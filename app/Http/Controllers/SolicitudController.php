<?php
namespace App\Http\Controllers;

use App\Models\{Factura, SolicitudFactura};             // Importa los modelos Factura y SolicitudFactura para interactuar con las tablas de BD
use App\Http\Requests\SolicitudFacturaRequest;          // Importa la clase que valida el formulario de solicitud de factura
use Illuminate\Support\Facades\{DB, Storage, Log};     // Importa la BD, el sistema de almacenamiento de ficheros y el registro de errores

class SolicitudController extends Controller
{
    /**
     * GET /solicitar-factura
     *
     * Renderiza la vista pública del formulario de solicitud de factura.
     *
     * @return \Illuminate\View\View  Vista 'cliente.solicitar-factura'.
     */
    public function index()
    {
        return view('cliente.solicitar-factura');       // Devuelve la vista Blade del formulario público de solicitud de factura
    }

    /**
     * POST /api/solicitudes
     *
     * Procesa el formulario de solicitud de factura enviado por el cliente.
     *
     * @param  SolicitudFacturaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(SolicitudFacturaRequest $request)
    {
        try {
            $data = $request->validated();              // Obtiene solo los campos que han pasado la validación del FormRequest

            $ticketFilename = $ticketPath = $ticketMime = null; // Inicializa a null las variables del fichero de ticket (por si no se adjunta)
            if ($request->hasFile('ticket') && $request->file('ticket')->isValid()) { // Comprueba que se adjuntó un ticket y que el archivo es válido
                $file = $request->file('ticket');       // Obtiene el objeto archivo del ticket subido
                $ticketFilename = uniqid('ticket_', true) . '.' . $file->getClientOriginalExtension(); // Genera un nombre único para evitar colisiones en el servidor
                $ticketPath     = $file->storeAs('tickets', $ticketFilename, 'public'); // Guarda el archivo en storage/app/public/tickets/ con el nombre único
                $ticketMime     = $file->getMimeType(); // Obtiene el tipo MIME del archivo (ej: image/jpeg) para guardarlo en BD
            }

            $total = (float)$data['importe_ticket'];    // Convierte el importe total del ticket a número decimal
            $pct   = 10.0;                              // Define el porcentaje de IVA aplicable (10% para hostelería)
            $base  = round($total / (1 + $pct / 100), 2); // Calcula la base imponible: total dividido entre 1.10, redondeado a 2 decimales
            $civa  = round($total - $base, 2);          // Calcula la cuota de IVA: total menos base imponible

            DB::beginTransaction();                     // Abre una transacción para que solicitud y factura se creen juntas o ninguna

            $serie      = 'MW-' . date('Y');            // Construye el prefijo de serie con el año actual, ej: "MW-2026"
            $referencia = $this->generarReferencia($serie); // Genera el siguiente número correlativo de forma atómica, ej: "MW-2026-0005"

            $solicitud = SolicitudFactura::create([     // Crea el registro de la solicitud del cliente en la tabla solicitudes_factura
                'referencia'     => $referencia,        // Número de referencia único generado
                'tipo_receptor'  => $data['tipo_receptor'],  // Tipo de receptor: 'particular' o 'empresa'
                'nombre_cliente' => $data['nombre_cliente'], // Nombre completo del solicitante
                'nombre_empresa' => $data['nombre_empresa'] ?? null, // Razón social (solo si es empresa)
                'nif_cif'        => strtoupper($data['nif_cif']),    // NIF/CIF en mayúsculas para normalizar formato
                'email'          => strtolower($data['email']),      // Email en minúsculas para normalizar formato
                'direccion'      => $data['direccion'],              // Dirección fiscal del receptor
                'codigo_postal'  => $data['codigo_postal'],          // Código postal del receptor
                'ciudad'         => $data['ciudad'],                 // Ciudad del receptor
                'fecha_consumo'  => $data['fecha_consumo'],          // Fecha en que se realizó la consumición en el restaurante
                'importe_ticket' => $total,                          // Importe total del ticket original
                'ticket_filename'=> $ticketFilename,                 // Nombre del archivo de imagen del ticket (o null si no se adjuntó)
                'ticket_path'    => $ticketPath,                     // Ruta relativa en storage donde se guardó el ticket
                'ticket_mime'    => $ticketMime,                     // Tipo MIME del archivo de ticket
                'observaciones'  => $data['observaciones'] ?? null,  // Notas adicionales del cliente (opcional)
                'atendido_por'   => $data['atendido_por']  ?? null,  // Nombre del empleado que atendió, extraído por OCR (opcional)
                'acepta_lopd'    => true,                            // El cliente aceptó la política de privacidad (siempre true si llegó aquí)
                'ip_solicitante' => $request->ip(),                  // IP del cliente para auditoría y trazabilidad
                'user_agent'     => substr($request->userAgent() ?? '', 0, 500), // User-agent del navegador, truncado a 500 caracteres
            ]);

            $factura = Factura::create([                // Crea el registro de la factura vinculada a la solicitud anterior
                'id_solicitud'       => $solicitud->id,         // Clave foránea que vincula esta factura con su solicitud
                'numero_factura'     => $referencia,            // Mismo número de referencia que la solicitud
                'receptor_nombre'    => $data['nombre_cliente'], // Nombre del receptor en la factura
                'receptor_empresa'   => $data['nombre_empresa'] ?? null, // Empresa receptora (opcional)
                'receptor_nif'       => strtoupper($data['nif_cif']),    // NIF/CIF del receptor en mayúsculas
                'receptor_email'     => strtolower($data['email']),      // Email del receptor en minúsculas
                'receptor_direccion' => $data['direccion'],              // Dirección fiscal del receptor
                'receptor_cp'        => $data['codigo_postal'],          // Código postal del receptor
                'receptor_ciudad'    => $data['ciudad'],                 // Ciudad del receptor
                'base_imponible'     => $base,                           // Base imponible calculada (sin IVA)
                'porcentaje_iva'     => $pct,                            // Porcentaje de IVA aplicado (10%)
                'cuota_iva'          => $civa,                           // Importe del IVA en euros
                'total_factura'      => $total,                          // Importe total de la factura con IVA incluido
                'concepto'           => 'Consumicion en Miss Whitney',   // Descripción del concepto facturado
                'lineas_ticket'      => !empty($data['lineas_ticket']) ? json_decode($data['lineas_ticket'], true) : null, // Líneas del ticket decodificadas de JSON (si las detectó el OCR)
                'fecha_consumo'      => $data['fecha_consumo'],          // Fecha de la consumición
                'estado'             => 'pendiente',                     // Estado inicial: pendiente de revisión por el admin
            ]);

            DB::commit();                               // Confirma la transacción: ambos registros quedan guardados permanentemente

        } catch (\Exception $e) {
            DB::rollBack();                             // Revierte la transacción si algo falló: no queda ningún registro a medias
            Log::error('MW solicitud store: ' . $e->getMessage()); // Registra el error en el log de Laravel
            return response()->json(['ok' => false, 'mensaje' => 'Error al guardar: ' . $e->getMessage()], 500); // Devuelve error 500 al cliente
        }

        $pdfUrl = null;                                 // Inicializa la URL del PDF a null (por si la generación falla)
        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.factura', [ // Carga la vista Blade pdf.factura con los datos de la factura para generar el PDF
                'numero_factura'     => $referencia,
                'receptor_nombre'    => $data['nombre_cliente'],
                'receptor_empresa'   => $data['nombre_empresa'] ?? null,
                'receptor_nif'       => strtoupper($data['nif_cif']),
                'receptor_email'     => strtolower($data['email']),
                'receptor_direccion' => $data['direccion'],
                'receptor_cp'        => $data['codigo_postal'],
                'receptor_ciudad'    => $data['ciudad'],
                'base'               => $base,          // Base imponible para mostrar en el PDF
                'civa'               => $civa,          // Cuota de IVA para mostrar en el PDF
                'total'              => $total,         // Total con IVA para mostrar en el PDF
                'pct_iva'            => $pct,           // Porcentaje de IVA para mostrar en el PDF
                'concepto'           => 'Consumicion en Miss Whitney',
                'lineas_ticket'      => !empty($data['lineas_ticket']) ? json_decode($data['lineas_ticket'], true) : null, // Líneas del ticket para el detalle del PDF
                'fecha_consumo'      => $data['fecha_consumo'],
                'fecha_emision'      => now(),          // Fecha y hora actuales como fecha de emisión
                'fecha_solicitud'    => now(),          // Fecha y hora actuales como fecha de solicitud
                'obs_cliente'        => $data['observaciones'] ?? null,
            ])->setPaper('a4');                         // Configura el PDF en tamaño A4

            $nombre = 'Factura_' . $referencia . '.pdf';                 // Construye el nombre del archivo PDF con la referencia
            Storage::disk('public')->put('facturas/' . $nombre, $pdf->output()); // Guarda el PDF en storage/app/public/facturas/
            $factura->update(['pdf_path' => 'facturas/' . $nombre]);     // Actualiza la factura en BD con la ruta del PDF generado
            $pdfUrl = url('api/facturas/' . $factura->id . '/pdf?modo=descargar'); // Construye la URL pública de descarga del PDF

        } catch (\Throwable $e) {
            Log::error('MW PDF cliente: ' . $e->getMessage()); // Registra el fallo de generación del PDF (no es bloqueante)
            // El PDF falló pero la solicitud ya se guardó: se responde OK igualmente
        }

        return response()->json([
            'ok'         => true,                       // Indica que la solicitud se guardó correctamente
            'referencia' => $referencia,                // Número de referencia asignado, ej: "MW-2026-0005"
            'email'      => strtolower($data['email']), // Email del cliente para mostrar confirmación en pantalla
            'pdf_ok'     => !is_null($pdfUrl),          // True si el PDF se generó correctamente, false si falló
            'pdf_url'    => $pdfUrl,                    // URL de descarga del PDF (null si no se generó)
            'factura_id' => $factura->id,               // ID de la factura creada en base de datos
            'mensaje'    => 'Solicitud registrada correctamente', // Mensaje de confirmación para el usuario
        ]);
    }

    /**
     * Genera el siguiente número de referencia correlativo de forma atómica.
     * Debe llamarse dentro de una transacción DB activa.
     *
     * @param  string $serie  Prefijo de la serie, p.ej. "MW-2026".
     * @return string         Referencia completa, p.ej. "MW-2026-0042".
     */
    public function generarReferencia(string $serie): string
    {
        DB::table('contador_referencias')
            ->insertOrIgnore(['serie' => $serie, 'ultimo_numero' => 0]); // Inserta la serie con contador a 0 si todavía no existe (idempotente)
        $n = DB::table('contador_referencias')
            ->where('serie', $serie)
            ->lockForUpdate()                           // Bloquea la fila exclusivamente para evitar que dos peticiones lean el mismo número a la vez
            ->value('ultimo_numero') + 1;              // Lee el contador actual e incrementa en 1
        DB::table('contador_referencias')
            ->where('serie', $serie)
            ->update(['ultimo_numero' => $n]);          // Persiste el nuevo valor del contador en la base de datos
        return $serie . '-' . str_pad($n, 4, '0', STR_PAD_LEFT); // Devuelve la referencia formateada con 4 dígitos, ej: "MW-2026-0042"
    }
}
