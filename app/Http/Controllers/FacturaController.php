<?php
namespace App\Http\Controllers;

use App\Models\{Factura, SolicitudFactura};             // Importa los modelos Eloquent para interactuar con las tablas de facturas y solicitudes
use App\Http\Requests\{CrearFacturaRequest, ActualizarFacturaRequest, CambiarEstadoRequest, EnviarEmailFacturaRequest}; // Importa los FormRequests que validan cada operación
use Illuminate\Http\Request;                           // Importa la clase base de petición HTTP de Laravel
use Illuminate\Support\Facades\{DB, Storage, Log};    // Importa la BD, el sistema de ficheros y el registro de errores

class FacturaController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    //  HELPERS PRIVADOS
    // ══════════════════════════════════════════════════════════════

    /**
     * Genera el siguiente número de referencia correlativo de forma atómica.
     * Debe invocarse dentro de una transacción DB activa.
     *
     * @param  string $serie  Prefijo de la serie, p.ej. "MW-2026".
     * @return string         Referencia completa, p.ej. "MW-2026-0007".
     */
    private function generarReferencia(string $serie): string
    {
        DB::table('contador_referencias')
            ->insertOrIgnore(['serie' => $serie, 'ultimo_numero' => 0]); // Inserta la serie con contador a 0 si aún no existe (idempotente)
        $n = DB::table('contador_referencias')
            ->where('serie', $serie)
            ->lockForUpdate()           // Bloquea la fila exclusivamente para que dos peticiones simultáneas no lean el mismo número
            ->value('ultimo_numero') + 1; // Lee el contador actual y lo incrementa en 1
        DB::table('contador_referencias')
            ->where('serie', $serie)
            ->update(['ultimo_numero' => $n]); // Persiste el nuevo valor del contador en la base de datos
        return $serie . '-' . str_pad($n, 4, '0', STR_PAD_LEFT); // Devuelve la referencia formateada con 4 dígitos, ej: "MW-2026-0007"
    }

    /**
     * Calcula la base imponible y la cuota de IVA desde el importe total.
     *
     * @param  float $total  Importe total con IVA incluido.
     * @param  float $pct    Porcentaje de IVA (por defecto 10.0).
     * @return array         ['base' => float, 'civa' => float]
     */
    private function calcularIVA(float $total, float $pct = 10.0): array
    {
        $base = round($total / (1 + $pct / 100), 2); // Calcula la base imponible: total dividido entre 1.10, redondeado a 2 decimales
        $civa = round($total - $base, 2);             // Calcula la cuota de IVA: total menos base imponible
        return ['base' => $base, 'civa' => $civa];   // Devuelve ambos valores en un array asociativo
    }

    // ══════════════════════════════════════════════════════════════
    //  RUTAS PÚBLICAS / VISTAS
    // ══════════════════════════════════════════════════════════════

    /**
     * GET /admin
     *
     * Renderiza el panel de administración.
     * Los datos se cargan de forma asíncrona desde el frontend.
     *
     * @return \Illuminate\View\View  Vista 'admin.index'.
     */
    public function index()
    {
        return view('admin.index');     // Devuelve la vista Blade del panel de administración
    }

    // ══════════════════════════════════════════════════════════════
    //  API REST — FACTURAS
    // ══════════════════════════════════════════════════════════════

    /**
     * GET /api/facturas
     *
     * Devuelve la lista completa de facturas para el panel de administración,
     * combinando solicitudes y facturas con un LEFT JOIN.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listar()
    {
        try {
            $rows = DB::table('solicitudes_factura as s')
                ->leftJoin('facturas as f', 'f.id_solicitud', '=', 's.id') // JOIN: une facturas con sus solicitudes; LEFT para incluir facturas sin solicitud
                ->select(
                    'f.id',                                                 // ID único de la factura
                    DB::raw('COALESCE(f.numero_factura, s.referencia) as numero_factura'), // Usa el número de factura o la referencia de solicitud si no hay factura
                    's.referencia as referencia_solicitud',                 // Referencia original de la solicitud
                    DB::raw('COALESCE(s.created_at, f.created_at, NOW()) as fecha_solicitud'), // Fecha de creación de la solicitud (o de la factura si no hay solicitud)
                    DB::raw('COALESCE(f.fecha_consumo, s.fecha_consumo) as fecha_consumo'),    // Fecha de consumición de la factura o la solicitud
                    'f.fecha_emision',                                      // Fecha en que se emitió la factura
                    DB::raw('COALESCE(f.receptor_nombre,  s.nombre_cliente)  as nombre_cliente'),  // Nombre del cliente priorizando datos de la factura
                    DB::raw('COALESCE(f.receptor_empresa, s.nombre_empresa)  as empresa'),         // Empresa priorizando datos de la factura
                    DB::raw("CASE
                        WHEN COALESCE(f.receptor_empresa, s.nombre_empresa) IS NOT NULL
                          AND TRIM(COALESCE(f.receptor_empresa, s.nombre_empresa)) != ''
                        THEN COALESCE(f.receptor_empresa, s.nombre_empresa)
                        ELSE COALESCE(f.receptor_nombre, s.nombre_cliente)
                    END as nombre_display"),                                // Muestra empresa si existe, nombre personal si no (para la columna principal del listado)
                    DB::raw('COALESCE(f.receptor_nif,   s.nif_cif)         as nif_cif'),           // NIF/CIF priorizando datos de la factura
                    DB::raw('COALESCE(f.receptor_email, s.email)           as email'),             // Email priorizando datos de la factura
                    DB::raw('COALESCE(f.total_factura,  s.importe_ticket)  as importe'),           // Importe total priorizando el de la factura sobre el del ticket
                    DB::raw("COALESCE(f.estado, 'pendiente')               as estado"),            // Estado de la factura; si no existe factura, se asume 'pendiente'
                    's.observaciones as obs_cliente',                       // Notas adicionales escritas por el cliente en la solicitud
                    's.atendido_por',                                       // Empleado que atendió según el OCR
                    's.ticket_filename',                                    // Nombre del archivo de imagen del ticket adjunto
                    'f.pdf_path',                                           // Ruta del PDF generado en storage
                    'f.lineas_ticket'                                       // Líneas de productos del ticket en formato JSON
                )
                ->orderByRaw('COALESCE(s.created_at, f.created_at) DESC')  // Ordena por fecha de creación más reciente primero
                ->get()
                ->map(function ($row) {
                    $row->importe = (float)($row->importe ?? 0);           // Convierte el importe a float (evita que llegue como string al JavaScript)
                    $row->lineas_ticket = $row->lineas_ticket ? json_decode($row->lineas_ticket, true) : null; // Decodifica el JSON de líneas de ticket a array PHP
                    return $row;
                });

            return response()->json($rows);     // Devuelve el listado completo como JSON al panel de administración

        } catch (\Exception $e) {
            Log::error('MW listar facturas: ' . $e->getMessage()); // Registra el error en el log de Laravel
            return response()->json(['error' => $e->getMessage()], 500); // Devuelve error 500 con el mensaje del error
        }
    }

    /**
     * POST /api/facturas
     *
     * Crea una nueva factura desde el panel de administración (alta manual).
     *
     * @param  CrearFacturaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function crear(CrearFacturaRequest $request)
    {
        try {
            $data = $request->validated();      // Obtiene solo los campos que han pasado la validación del FormRequest

            $total = (float)$data['importe'];   // Convierte el importe a float
            $iva   = $this->calcularIVA($total); // Calcula base imponible y cuota de IVA al 10%
            $serie = 'MW-' . date('Y');          // Construye el prefijo de serie con el año actual, ej: "MW-2026"

            $idSolicitud = null;                 // ID de la solicitud vinculada (null si es alta manual pura)
            $referencia  = '';                   // Número de referencia que se rellenará a continuación

            if (!empty($data['referencia_solicitud'])) { // Si se proporcionó una referencia de solicitud existente, intenta vincular
                $sol = SolicitudFactura::where('referencia', $data['referencia_solicitud'])->first(); // Busca la solicitud en BD por su referencia
                if ($sol) {
                    $idSolicitud = $sol->id;     // Guarda el ID de la solicitud encontrada para vincularlo a la factura
                    $referencia  = $sol->referencia; // Usa la misma referencia que ya tiene la solicitud
                }
            }

            if (!$idSolicitud) {                 // Si no se vinculó a ninguna solicitud existente, crea una solicitud "fantasma" de admin
                DB::beginTransaction();          // Abre transacción para crear solicitud y obtener referencia de forma atómica
                $referencia  = $this->generarReferencia($serie); // Genera el siguiente número correlativo de forma atómica
                $sol = SolicitudFactura::create([
                    'referencia'     => $referencia,             // Número de referencia asignado
                    'tipo_receptor'  => 'particular',            // Tipo por defecto para altas manuales de admin
                    'nombre_cliente' => $data['nombre_cliente'], // Nombre del cliente introducido por el admin
                    'nombre_empresa' => $data['empresa'] ?? null, // Empresa (opcional)
                    'nif_cif'        => strtoupper($data['nif_cif']),  // NIF/CIF en mayúsculas
                    'email'          => strtolower($data['email']),    // Email en minúsculas
                    'direccion'      => 'Alta manual admin',     // Dirección genérica para altas manuales
                    'codigo_postal'  => '00000',                 // Código postal genérico para altas manuales
                    'ciudad'         => 'N/A',                   // Ciudad genérica para altas manuales
                    'fecha_consumo'  => $data['fecha_consumo'],  // Fecha de consumición introducida por el admin
                    'importe_ticket' => $total,                  // Importe total de la factura
                    'acepta_lopd'    => true,                    // Se asume aceptación al ser creada por el admin
                    'ip_solicitante' => '127.0.0.1',             // IP local para indicar que es una creación interna
                ]);
                $idSolicitud = $sol->id;         // Guarda el ID de la solicitud recién creada
                DB::commit();                    // Confirma la transacción: la solicitud queda guardada
            }

            $f = Factura::create([               // Crea el registro de la factura en la tabla facturas
                'id_solicitud'       => $idSolicitud,            // Clave foránea que vincula con la solicitud
                'numero_factura'     => $referencia,             // Número de factura (mismo que la referencia)
                'receptor_nombre'    => $data['nombre_cliente'], // Nombre del receptor en la factura
                'receptor_empresa'   => $data['empresa'] ?? null, // Empresa del receptor (opcional)
                'receptor_nif'       => strtoupper($data['nif_cif']),   // NIF/CIF en mayúsculas
                'receptor_email'     => strtolower($data['email']),     // Email en minúsculas
                'receptor_direccion' => 'Alta manual admin',     // Dirección para altas manuales
                'receptor_cp'        => '00000',                 // Código postal para altas manuales
                'receptor_ciudad'    => 'N/A',                   // Ciudad para altas manuales
                'base_imponible'     => $iva['base'],            // Base imponible calculada sin IVA
                'porcentaje_iva'     => 10,                      // Porcentaje de IVA aplicado
                'cuota_iva'          => $iva['civa'],            // Importe del IVA en euros
                'total_factura'      => $total,                  // Importe total con IVA incluido
                'concepto'           => 'Consumicion en Miss Whitney', // Concepto fijo de la factura
                'lineas_ticket'      => $data['lineas_ticket'] ?? null, // Líneas del ticket (opcional en alta manual)
                'fecha_consumo'      => $data['fecha_consumo'],  // Fecha de la consumición
                'estado'             => $data['estado'],         // Estado inicial elegido por el admin
                'fecha_emision'      => $data['estado'] === 'emitida' ? now() : null, // Registra fecha de emisión solo si se crea directamente como emitida
                'admin_usuario'      => 'admin',                 // Registra que fue creada por el panel de administración
            ]);

            return response()->json([            // Devuelve los datos de la factura recién creada al frontend
                'id'                   => $f->id,
                'numero_factura'       => $f->numero_factura,
                'referencia_solicitud' => $referencia,
                'fecha_solicitud'      => $f->created_at,
                'fecha_consumo'        => $f->fecha_consumo,
                'nombre_cliente'       => $f->receptor_nombre,
                'empresa'              => $f->receptor_empresa,
                'nombre_display'       => $f->receptor_empresa ?: $f->receptor_nombre, // Muestra empresa si existe, nombre personal si no
                'nif_cif'              => $f->receptor_nif,
                'email'                => $f->receptor_email,
                'importe'              => $f->total_factura,
                'estado'               => $f->estado,
                'obs_cliente'          => $data['obs_cliente'] ?? null,
                'lineas_ticket'        => $f->lineas_ticket,
                'pdf_path'             => null,              // El PDF no se genera automáticamente en alta manual
            ]);

        } catch (\Exception $e) {
            Log::error('MW crear factura admin: ' . $e->getMessage()); // Registra el error en el log
            return response()->json(['ok' => false, 'mensaje' => $e->getMessage()], 500); // Devuelve error 500 al frontend
        }
    }

    /**
     * POST /api/facturas/{id}
     *
     * Actualiza los datos fiscales y el estado de una factura existente.
     *
     * @param  ActualizarFacturaRequest $request
     * @param  int                      $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function actualizar(ActualizarFacturaRequest $request, int $id)
    {
        try {
            $f = Factura::findOrFail($id);       // Busca la factura por ID o lanza excepción 404 si no existe

            $data = $request->validated();       // Obtiene los campos validados del FormRequest

            $total = (float)$data['importe'];    // Convierte el nuevo importe a float
            $iva   = $this->calcularIVA($total); // Recalcula base imponible y cuota de IVA con el nuevo importe

            $f->update([                         // Actualiza todos los campos editables de la factura
                'receptor_nombre'  => $data['nombre_cliente'],           // Nuevo nombre del receptor
                'receptor_empresa' => $data['empresa'] ?? null,          // Nueva empresa del receptor (opcional)
                'receptor_nif'     => strtoupper($data['nif_cif']),      // NIF/CIF actualizado en mayúsculas
                'receptor_email'   => strtolower($data['email']),        // Email actualizado en minúsculas
                'fecha_consumo'    => $data['fecha_consumo'],            // Nueva fecha de consumición
                'base_imponible'   => $iva['base'],                      // Base imponible recalculada
                'cuota_iva'        => $iva['civa'],                      // Cuota de IVA recalculada
                'total_factura'    => $total,                            // Nuevo importe total
                'lineas_ticket'    => $data['lineas_ticket'] ?? null,    // Líneas del ticket actualizadas
                'estado'           => $data['estado'],                   // Nuevo estado de la factura
                'fecha_emision'    => $data['estado'] === 'emitida' && !$f->fecha_emision ? now() : $f->fecha_emision, // Registra la fecha de emisión solo la primera vez que pasa a 'emitida'
                'admin_usuario'    => 'admin',                           // Registra que la actualización fue hecha desde el panel
            ]);

            return response()->json([            // Devuelve los datos actualizados de la factura al frontend
                'id'             => $f->id,
                'numero_factura' => $f->numero_factura,
                'nombre_cliente' => $f->receptor_nombre,
                'empresa'        => $f->receptor_empresa,
                'nombre_display' => $f->receptor_empresa ?: $f->receptor_nombre, // Muestra empresa si existe, nombre si no
                'nif_cif'        => $f->receptor_nif,
                'email'          => $f->receptor_email,
                'importe'        => $f->total_factura,
                'estado'         => $f->estado,
                'fecha_consumo'  => $f->fecha_consumo,
                'obs_cliente'    => $data['obs_cliente'] ?? null,
                'lineas_ticket'  => $f->lineas_ticket,
            ]);

        } catch (\Exception $e) {
            Log::error('MW actualizar factura: ' . $e->getMessage()); // Registra el error en el log
            return response()->json(['ok' => false, 'mensaje' => $e->getMessage()], 500); // Devuelve error 500 al frontend
        }
    }

    /**
     * POST /api/facturas/{id}/estado
     *
     * Cambia únicamente el estado de una factura (pendiente → procesando → emitida → cancelada).
     *
     * @param  CambiarEstadoRequest $request
     * @param  int                  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cambiarEstado(CambiarEstadoRequest $request, int $id)
    {
        try {
            $f      = Factura::findOrFail($id);  // Busca la factura por ID o lanza excepción 404 si no existe
            $estado = $request->validated()['estado']; // Extrae el nuevo estado del FormRequest validado

            $f->update([
                'estado'        => $estado,      // Actualiza el estado de la factura
                'fecha_emision' => $estado === 'emitida' && !$f->fecha_emision ? now() : $f->fecha_emision, // Registra la fecha de emisión la primera vez que pasa a 'emitida'
            ]);

            return response()->json(['ok' => true, 'id' => $id, 'estado' => $estado]); // Devuelve confirmación con el nuevo estado al frontend

        } catch (\Exception $e) {
            Log::error('MW cambiar estado: ' . $e->getMessage()); // Registra el error en el log
            return response()->json(['ok' => false, 'mensaje' => $e->getMessage()], 500); // Devuelve error 500 al frontend
        }
    }

    /**
     * POST /api/facturas/{id}/delete  |  DELETE /api/facturas/{id}
     *
     * Elimina permanentemente una factura y su PDF del disco.
     * La solicitud vinculada NO se elimina (se preserva el historial).
     *
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function eliminar(int $id)
    {
        try {
            $f = Factura::findOrFail($id);       // Busca la factura por ID o lanza excepción 404 si no existe

            if ($f->pdf_path && Storage::disk('public')->exists($f->pdf_path)) { // Comprueba si tiene PDF asociado y si el archivo existe en disco
                Storage::disk('public')->delete($f->pdf_path); // Elimina el PDF del disco antes de borrar el registro de BD
            }

            $ref = $f->numero_factura;           // Guarda el número de factura antes de eliminarla para incluirlo en la respuesta
            $f->delete();                        // Elimina el registro de la factura de la base de datos

            return response()->json(['ok' => true, 'id' => $id, 'numero_factura' => $ref]); // Devuelve confirmación con el ID y número de la factura eliminada

        } catch (\Exception $e) {
            Log::error('MW eliminar factura: ' . $e->getMessage()); // Registra el error en el log
            return response()->json(['ok' => false, 'mensaje' => $e->getMessage()], 500); // Devuelve error 500 al frontend
        }
    }

    /**
     * GET /api/facturas/{id}/pdf
     *
     * Sirve el PDF de una factura (patrón cache-first: sirve desde disco o regenera con DomPDF).
     *
     * @param  int     $id
     * @param  Request $request  Parámetro GET 'modo': 'ver' (inline) o 'descargar' (attachment).
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function descargarPdf(int $id, Request $request)
    {
        try {
            $f    = Factura::with('solicitud')->findOrFail($id); // Carga la factura con su solicitud asociada o lanza 404
            $modo = $request->get('modo', 'ver'); // Lee el parámetro 'modo' de la URL; por defecto 'ver' (abrir en navegador)

            if ($f->pdf_path && Storage::disk('public')->exists($f->pdf_path)) { // Si el PDF ya existe en disco, lo sirve directamente sin regenerar
                $nombre = 'Factura_' . $f->numero_factura . '.pdf'; // Nombre del archivo para la cabecera Content-Disposition
                return $modo === 'descargar'
                    ? Storage::disk('public')->download($f->pdf_path, $nombre)  // Descarga el PDF como adjunto
                    : response(Storage::disk('public')->get($f->pdf_path), 200, ['Content-Type' => 'application/pdf']); // Muestra el PDF inline en el navegador
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.factura', [ // Regenera el PDF cargando la vista Blade con todos los datos de la factura
                'numero_factura'     => $f->numero_factura,
                'receptor_nombre'    => $f->receptor_nombre,
                'receptor_empresa'   => $f->receptor_empresa,
                'receptor_nif'       => $f->receptor_nif,
                'receptor_email'     => $f->receptor_email,
                'receptor_direccion' => $f->receptor_direccion,
                'receptor_cp'        => $f->receptor_cp,
                'receptor_ciudad'    => $f->receptor_ciudad,
                'base'               => $f->base_imponible,    // Base imponible de la factura
                'civa'               => $f->cuota_iva,         // Cuota de IVA de la factura
                'total'              => $f->total_factura,     // Importe total de la factura
                'pct_iva'            => $f->porcentaje_iva,    // Porcentaje de IVA aplicado
                'concepto'           => $f->concepto,          // Concepto de la factura
                'lineas_ticket'      => $f->lineas_ticket,     // Líneas de productos del ticket
                'fecha_consumo'      => $f->fecha_consumo,     // Fecha de consumición
                'fecha_emision'      => $f->fecha_emision,     // Fecha de emisión de la factura
                'fecha_solicitud'    => $f->created_at,        // Fecha de creación del registro
                'obs_cliente'        => $f->solicitud->observaciones ?? null, // Notas del cliente de la solicitud asociada
            ])->setPaper('a4');                                 // Configura el PDF en tamaño A4

            $nombre = 'Factura_' . $f->numero_factura . '.pdf';           // Construye el nombre del archivo PDF
            Storage::disk('public')->put('facturas/' . $nombre, $pdf->output()); // Guarda el PDF en disco para no regenerarlo la próxima vez
            $f->update(['pdf_path' => 'facturas/' . $nombre]);            // Actualiza la ruta del PDF en la base de datos

            return $modo === 'descargar' ? $pdf->download($nombre) : $pdf->stream($nombre); // Descarga o muestra el PDF según el modo solicitado

        } catch (\Exception $e) {
            Log::error('MW PDF: ' . $e->getMessage()); // Registra el error en el log
            return response()->json(['ok' => false, 'mensaje' => $e->getMessage()], 500); // Devuelve error 500 al frontend
        }
    }

    /**
     * POST /api/facturas/{id}/email
     *
     * Envía la factura en PDF como adjunto al email del receptor.
     *
     * @param  int                       $id
     * @param  EnviarEmailFacturaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function enviarPorEmail(int $id, EnviarEmailFacturaRequest $request)
    {
        try {
            $data = $request->validated();       // Obtiene los campos validados (campo 'email' opcional)

            $f = Factura::with('solicitud')->findOrFail($id); // Carga la factura con su solicitud o lanza 404
            $destino = !empty($data['email']) ? $data['email'] : $f->receptor_email; // Usa el email del formulario si se proporcionó, si no el de la factura

            if (!$destino) {
                return response()->json(['ok' => false, 'mensaje' => 'No hay email destino'], 422); // Devuelve error 422 si no hay ningún email disponible
            }

            $pdfPath = $f->pdf_path;             // Ruta del PDF almacenada en la BD
            if (!$pdfPath || !Storage::disk('public')->exists($pdfPath)) { // Si no existe el PDF en disco, lo regenera
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.factura', [ // Regenera el PDF con los datos de la factura
                    'numero_factura'     => $f->numero_factura,
                    'receptor_nombre'    => $f->receptor_nombre,
                    'receptor_empresa'   => $f->receptor_empresa,
                    'receptor_nif'       => $f->receptor_nif,
                    'receptor_email'     => $f->receptor_email,
                    'receptor_direccion' => $f->receptor_direccion,
                    'receptor_cp'        => $f->receptor_cp,
                    'receptor_ciudad'    => $f->receptor_ciudad,
                    'base'               => $f->base_imponible,
                    'civa'               => $f->cuota_iva,
                    'total'              => $f->total_factura,
                    'pct_iva'            => $f->porcentaje_iva,
                    'concepto'           => $f->concepto,
                    'lineas_ticket'      => $f->lineas_ticket,
                    'fecha_consumo'      => $f->fecha_consumo,
                    'fecha_emision'      => $f->fecha_emision,
                    'fecha_solicitud'    => $f->created_at,
                    'obs_cliente'        => $f->solicitud->observaciones ?? null,
                ])->setPaper('a4');              // Configura el PDF en tamaño A4

                $nombre  = 'Factura_' . $f->numero_factura . '.pdf'; // Nombre del archivo PDF
                $pdfPath = 'facturas/' . $nombre;                    // Ruta relativa en storage
                Storage::disk('public')->put($pdfPath, $pdf->output()); // Guarda el PDF en disco
                $f->update(['pdf_path' => $pdfPath]);                // Actualiza la ruta en la BD para futuras peticiones
            }

            $absPath        = Storage::disk('public')->path($pdfPath); // Obtiene la ruta absoluta del PDF en el servidor de ficheros
            $pdfNombre      = 'Factura_' . $f->numero_factura . '.pdf'; // Nombre del archivo que verá el destinatario del email
            $nombreReceptor = !empty($f->receptor_empresa) ? $f->receptor_empresa : $f->receptor_nombre; // Nombre a mostrar en el email (empresa si existe, nombre personal si no)

            \Mail::to($destino)->send(new \App\Mail\FacturaMail( // Envía el email usando la clase Mailable FacturaMail
                $f->numero_factura,  // Número de factura para el asunto y cuerpo del email
                $nombreReceptor,     // Nombre del receptor para personalizar el saludo
                $absPath,            // Ruta absoluta del PDF para adjuntarlo al email
                $pdfNombre           // Nombre que tendrá el archivo adjunto
            ));

            return response()->json([
                'ok'      => true,
                'mensaje' => 'Factura enviada a ' . $destino, // Mensaje de confirmación con el email usado
                'email'   => $destino,                        // Email al que se envió para mostrarlo en el panel
            ]);

        } catch (\Exception $e) {
            Log::error('MW enviar email factura: ' . $e->getMessage()); // Registra el error en el log
            return response()->json(['ok' => false, 'mensaje' => $e->getMessage()], 500); // Devuelve error 500 al frontend
        }
    }

    /**
     * GET /api/facturas/zip?year=YYYY&month=MM
     *
     * Genera y descarga un ZIP con los PDFs de todas las facturas del mes indicado,
     * filtradas por fecha_consumo.
     *
     * @param  Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function downloadZip(Request $request)
    {
        try {
            $year  = (int) $request->get('year',  date('Y')); // Lee el año del parámetro GET; por defecto el año actual
            $month = (int) $request->get('month', date('n')); // Lee el mes del parámetro GET; por defecto el mes actual

            $facturas = Factura::with('solicitud')
                ->whereYear('fecha_consumo',  $year)   // Filtra por el año de la fecha de consumición
                ->whereMonth('fecha_consumo', $month)  // Filtra por el mes de la fecha de consumición
                ->orderBy('id')                        // Ordena por ID para que el ZIP tenga las facturas en orden cronológico
                ->get();                               // Ejecuta la consulta y obtiene todas las facturas del mes

            if ($facturas->isEmpty()) {
                return response()->json(['ok' => false, 'mensaje' => 'No hay facturas para ese mes'], 404); // Devuelve 404 si no hay facturas en ese mes
            }

            $tmpPath = tempnam(sys_get_temp_dir(), 'mw_zip_'); // Crea un archivo temporal vacío en el directorio temporal del servidor
            $zipName = sprintf('facturas-%04d-%02d.zip', $year, $month); // Construye el nombre del ZIP con año y mes, ej: "facturas-2026-05.zip"
            $zip     = new \ZipArchive();              // Crea una nueva instancia de ZipArchive para construir el archivo ZIP

            if ($zip->open($tmpPath, \ZipArchive::OVERWRITE) !== true) { // Abre el archivo temporal como ZIP (modo OVERWRITE para sobrescribir si existía)
                throw new \RuntimeException('No se pudo crear el archivo ZIP'); // Lanza excepción si no se puede crear el ZIP
            }

            foreach ($facturas as $f) {                // Itera cada factura del mes para añadir su PDF al ZIP
                $fileName = 'Factura_' . $f->numero_factura . '.pdf'; // Nombre del archivo PDF dentro del ZIP

                if ($f->pdf_path && Storage::disk('public')->exists($f->pdf_path)) {
                    $pdfContent = Storage::disk('public')->get($f->pdf_path); // Lee el contenido del PDF desde disco si ya existe
                } else {
                    $pdfObj = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.factura', [ // Regenera el PDF con DomPDF si no existe en disco
                        'numero_factura'     => $f->numero_factura,
                        'receptor_nombre'    => $f->receptor_nombre,
                        'receptor_empresa'   => $f->receptor_empresa,
                        'receptor_nif'       => $f->receptor_nif,
                        'receptor_email'     => $f->receptor_email,
                        'receptor_direccion' => $f->receptor_direccion,
                        'receptor_cp'        => $f->receptor_cp,
                        'receptor_ciudad'    => $f->receptor_ciudad,
                        'base'               => $f->base_imponible,
                        'civa'               => $f->cuota_iva,
                        'total'              => $f->total_factura,
                        'pct_iva'            => $f->porcentaje_iva,
                        'concepto'           => $f->concepto,
                        'lineas_ticket'      => $f->lineas_ticket,
                        'fecha_consumo'      => $f->fecha_consumo,
                        'fecha_emision'      => $f->fecha_emision,
                        'fecha_solicitud'    => $f->created_at,
                        'obs_cliente'        => $f->solicitud->observaciones ?? null,
                    ])->setPaper('a4');                // Configura el PDF en tamaño A4

                    $pdfContent = $pdfObj->output();   // Genera el PDF en memoria como string binario
                    Storage::disk('public')->put('facturas/' . $fileName, $pdfContent); // Guarda el PDF en disco para futuras peticiones
                    $f->update(['pdf_path' => 'facturas/' . $fileName]); // Actualiza la ruta del PDF en la BD
                }

                $zip->addFromString($fileName, $pdfContent); // Añade el PDF al ZIP leyendo el contenido inmediatamente (no lazy, fiable en todo hosting)
            }

            $zip->close();                             // Cierra y finaliza el archivo ZIP (escribe el directorio central del ZIP en el archivo temporal)

            return response()->download($tmpPath, $zipName, [
                'Content-Type' => 'application/zip',  // Cabecera MIME para indicar que es un archivo ZIP
            ])->deleteFileAfterSend(true);             // Elimina el archivo temporal del servidor automáticamente tras enviarlo al cliente

        } catch (\Exception $e) {
            Log::error('MW ZIP: ' . $e->getMessage()); // Registra el error en el log
            return response()->json(['ok' => false, 'mensaje' => $e->getMessage()], 500); // Devuelve error 500 al frontend
        }
    }
}
