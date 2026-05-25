<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Http, Log};            // Importa el cliente HTTP para llamar a Google Vision y el registro de errores

class OcrController extends Controller
{
    /**
     * POST /api/ocr/ticket
     *
     * Recibe la imagen del ticket, la envía a Google Cloud Vision y devuelve
     * los datos estructurados extraídos (fecha, total, líneas, empleado).
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function procesarTicket(Request $request)
    {
        $request->validate([
            'ticket' => 'required|file|mimes:jpg,jpeg,png,webp|max:10240', // Valida que sea imagen (jpg/png/webp) y no supere 10 MB
        ]);

        try {
            $apiKey = env('GOOGLE_VISION_API_KEY');     // Lee la clave de la API de Google Cloud Vision desde el .env
            if (!$apiKey) {
                return response()->json(['ok' => false, 'mensaje' => 'OCR no configurado'], 500); // Devuelve error si la clave no está configurada
            }

            $imageBase64 = base64_encode(file_get_contents($request->file('ticket')->getRealPath())); // Lee el archivo de imagen y lo codifica en base64 para enviarlo a la API

            $response = Http::timeout(30)->post(        // Hace una petición POST a la API de Google Cloud Vision con timeout de 30 segundos
                'https://vision.googleapis.com/v1/images:annotate?key=' . $apiKey,
                [
                    'requests' => [[
                        'image'    => ['content' => $imageBase64],  // Envía la imagen en base64
                        'features' => [['type' => 'DOCUMENT_TEXT_DETECTION', 'maxResults' => 1]], // Solicita detección de texto en documento (mejor para tickets)
                        'imageContext' => ['languageHints' => ['es']], // Indica que el texto es en español para mejorar la precisión
                    ]],
                ]
            );

            if (!$response->ok()) {                     // Comprueba si la respuesta HTTP de Google fue exitosa
                Log::error('OCR Vision: ' . $response->body()); // Registra el cuerpo del error de la API en el log
                return response()->json(['ok' => false, 'mensaje' => 'Vision API error'], 500); // Devuelve error 500 al frontend
            }

            $texto = $response->json()['responses'][0]['fullTextAnnotation']['text'] ?? ''; // Extrae el texto completo detectado por Vision API
            if (!$texto) {
                return response()->json(['ok' => false, 'mensaje' => 'No se detectó texto'], 422); // Devuelve error 422 si Vision no encontró ningún texto
            }

            $extracted = $this->parsearTicket($texto);  // Parsea el texto plano para extraer datos estructurados del ticket
            $extracted['ok']        = true;             // Añade indicador de éxito al resultado
            $extracted['texto_raw'] = mb_convert_encoding($texto, 'UTF-8', 'UTF-8'); // Normaliza el texto a UTF-8 válido

            array_walk_recursive($extracted, function (&$v) { // Recorre recursivamente todos los valores del array extraído
                if (is_string($v)) {
                    $v = mb_convert_encoding($v, 'UTF-8', 'UTF-8'); // Fuerza UTF-8 válido en cada string (Vision a veces devuelve bytes inválidos)
                }
            });

            return response()->json($extracted);        // Devuelve los datos extraídos del ticket como JSON al frontend

        } catch (\Exception $e) {
            Log::error('OCR ticket: ' . $e->getMessage()); // Registra cualquier error inesperado en el log de Laravel
            return response()->json(['ok' => false, 'mensaje' => $e->getMessage()], 500); // Devuelve error 500 al frontend
        }
    }

    /**
     * Parsea el texto plano de Vision API y devuelve los campos estructurados.
     *
     * @param  string $texto
     * @return array  { fecha, hora, lineas[], total, atendido_por }
     */
    private function parsearTicket(string $texto): array
    {
        $resultado = [
            'fecha'         => null,    // Fecha de la consumición (formato YYYY-MM-DD)
            'hora'          => null,    // Hora de la consumición (formato HH:MM)
            'lineas'        => [],      // Array de líneas de productos detectadas
            'total'         => null,    // Importe total del ticket
            'atendido_por'  => null,    // Nombre del empleado que atendió
        ];

        $lineas = array_values(array_filter(
            array_map('trim', preg_split("/\r\n|\n|\r/", $texto)), // Divide el texto por saltos de línea y elimina espacios en cada línea
            fn($l) => $l !== ''         // Filtra las líneas vacías
        ));

        $count = count($lineas);        // Total de líneas a procesar
        for ($idx = 0; $idx < $count; $idx++) { // Itera línea a línea sobre el texto del ticket
            $linea = $lineas[$idx];     // Línea actual en proceso

            if (preg_match('/(\d{2}\/\d{2}\/\d{4})\s+(\d{1,2}:\d{2})/', $linea, $m)) { // Detecta fecha y hora juntas en formato "DD/MM/YYYY HH:MM"
                $resultado['fecha'] = $this->normalizarFecha($m[1]); // Convierte la fecha a formato ISO YYYY-MM-DD
                $resultado['hora']  = $m[2];                          // Guarda la hora tal como aparece
            } elseif (!$resultado['fecha'] && preg_match('/(\d{2}\/\d{2}\/\d{4})/', $linea, $m)) { // Detecta solo la fecha si aún no se encontró
                $resultado['fecha'] = $this->normalizarFecha($m[1]); // Convierte la fecha a formato ISO
            }

            if (preg_match('/le\s+atendi[óo]\s*:?\s*(.+)/i', $linea, $m)) { // Detecta "Le atendió: NombreEmpleado" con o sin tilde y con o sin ":"
                $resultado['atendido_por'] = trim($m[1]);             // Guarda el nombre del empleado eliminando espacios
            }

            if (preg_match('/^total\s*:?\s*([\d.,]+)\s*€?/i', $linea, $m)) { // Detecta la línea de total del ticket, ej: "Total: 2,50 €"
                $valor = $this->parseNumero($m[1]);                   // Convierte el importe de texto a float
                if ($valor !== null && $valor > 0) {
                    $resultado['total'] = $valor;                     // Guarda el total solo si es un número positivo válido
                }
            }

            if (preg_match('/^(\d{1,3})\s+([A-ZÁÉÍÓÚÑa-záéíóúñ\/\.\-\s]{2,}?)$/u', $linea, $m)
                && !preg_match('/€|total|base|iva|imponible|factura|concepto/i', $linea)) { // Detecta línea de producto: número seguido de descripción, sin € ni palabras reservadas

                $cantidad = (int)$m[1];                               // Extrae la cantidad del producto como entero
                $concepto = trim(preg_replace('/\s+/', ' ', $m[2])); // Limpia espacios múltiples en el nombre del producto

                $precio  = null;                                      // Precio unitario (se leerá de la siguiente línea)
                $importe = null;                                      // Importe de la línea (se leerá de la línea subsiguiente)
                if (isset($lineas[$idx + 1])
                    && preg_match('/^([\d.,]+)\s*€?\s*$/', $lineas[$idx + 1], $p1)) { // Comprueba si la línea siguiente es el precio unitario
                    $precio = $this->parseNumero($p1[1]);             // Convierte el precio unitario a float
                }
                if (isset($lineas[$idx + 2])
                    && preg_match('/^([\d.,]+)\s*€?\s*$/', $lineas[$idx + 2], $p2)) { // Comprueba si la línea subsiguiente es el importe total de la línea
                    $importe = $this->parseNumero($p2[1]);            // Convierte el importe de la línea a float
                }

                if ($cantidad > 0 && $cantidad < 100
                    && strlen($concepto) >= 2
                    && $importe !== null && $importe > 0
                    && !preg_match('/total|base|iva|i\.v\.a|imponible/i', $concepto)) { // Filtra líneas válidas: cantidad razonable, concepto mínimo y sin palabras de resumen
                    $resultado['lineas'][] = [
                        'cantidad' => $cantidad,                      // Cantidad del producto
                        'concepto' => strtoupper($concepto),          // Nombre del producto en mayúsculas
                        'importe'  => $importe,                       // Importe total de esa línea
                    ];
                    $idx += 2;                                        // Salta las dos líneas de precios ya procesadas para no reinterpretarlas como productos
                }
            }
        }

        return $resultado;              // Devuelve el array con todos los datos estructurados extraídos del ticket
    }

    /**
     * Convierte fecha DD/MM/YYYY a formato ISO YYYY-MM-DD.
     *
     * @param  string $fecha
     * @return string
     */
    private function normalizarFecha(string $fecha): string
    {
        $partes = explode('/', $fecha); // Separa la fecha en sus tres partes: día, mes, año
        if (count($partes) === 3) {
            return sprintf('%04d-%02d-%02d', $partes[2], $partes[1], $partes[0]); // Reordena a formato ISO: año-mes-día con ceros iniciales
        }
        return $fecha;                  // Si no tiene el formato esperado, devuelve la cadena original sin cambios
    }

    /**
     * Convierte un string numérico (formato español o anglosajón) a float.
     *
     * @param  string $valor
     * @return float|null
     */
    private function parseNumero(string $valor): ?float
    {
        $limpio = str_replace(['€', ' '], '', $valor); // Elimina el símbolo de euro y espacios del valor
        if (substr_count($limpio, ',') === 1 && substr_count($limpio, '.') === 0) {
            $limpio = str_replace(',', '.', $limpio);  // Formato español sin miles "2,50" → convierte coma a punto decimal
        } elseif (substr_count($limpio, ',') === 1 && substr_count($limpio, '.') >= 1) {
            $limpio = str_replace('.', '', $limpio);   // Formato español con miles "1.234,50" → elimina el punto de miles
            $limpio = str_replace(',', '.', $limpio);  // Convierte la coma decimal a punto para que PHP lo entienda
        }
        return is_numeric($limpio) ? (float)$limpio : null; // Devuelve el float si es un número válido, o null si no se pudo convertir
    }
}
