<?php
namespace App\Http\Controllers;

use App\Http\Requests\ReservaRequest;                  // Importa la clase que valida los datos del formulario de reserva
use Illuminate\Support\Facades\{Mail, Log};            // Importa el sistema de correo y el registro de errores

class ReservaController extends Controller
{
    /**
     * POST /api/reserva
     *
     * Recibe los datos del formulario de reserva de mesa, construye un email HTML
     * con el resumen de la solicitud y lo envía al buzón del restaurante.
     *
     * @param  ReservaRequest $request  Petición validada con nombre, telefono,
     *                                  email (opcional), fecha, hora, personas y notas.
     * @return \Illuminate\Http\JsonResponse  { ok: bool }
     */
    public function enviar(ReservaRequest $request)
    {
        try {
            $data = $request->validated();                                                          // Obtiene solo los campos que han pasado la validación

            $destino = env('MAIL_FROM_ADDRESS', 'misswhitneyfacturas@gmail.com');                  // Lee el email de destino del .env (el restaurante)

            $fecha_fmt = \Carbon\Carbon::parse($data['fecha'])->translatedFormat('l, j \d\e F \d\e Y'); // Convierte la fecha a formato legible en español, ej: "martes, 14 de mayo de 2026"

            $html =
                '<div style="font-family:Arial,sans-serif;max-width:580px;margin:0 auto">'         // Contenedor principal del email con fuente y ancho máximo
              . '<div style="background:#7b1c2b;padding:18px 24px;border-radius:8px 8px 0 0">'     // Cabecera burdeos redondeada arriba con nombre del restaurante
              . '<h2 style="margin:0;color:#fff;font-style:italic;font-size:22px">Miss Whitney</h2>' // Título en blanco con cursiva
              . '<p style="margin:4px 0 0;color:rgba(255,255,255,.7);font-size:13px">Nueva solicitud de reserva</p>' // Subtítulo informativo en blanco semitransparente
              . '</div>'
              . '<div style="padding:24px 28px;background:#fff;border:1px solid #f0e7d8;border-top:none">' // Cuerpo blanco con borde crema (sin borde arriba para unirse a la cabecera)
              . '<table style="width:100%;border-collapse:collapse;font-size:15px">'               // Tabla de datos de la reserva a ancho completo
              . '<tr><td style="padding:8px 0;border-bottom:1px solid #f5ede8;color:#888;width:140px">Nombre</td>'
              . '<td style="padding:8px 0;border-bottom:1px solid #f5ede8;font-weight:600;color:#1a1a1a">' . htmlspecialchars($data['nombre'])            . '</td></tr>' // Fila con nombre del cliente (escapado)
              . '<tr><td style="padding:8px 0;border-bottom:1px solid #f5ede8;color:#888">Teléfono</td>'
              . '<td style="padding:8px 0;border-bottom:1px solid #f5ede8;font-weight:600;color:#1a1a1a">' . htmlspecialchars($data['telefono'])          . '</td></tr>' // Fila con teléfono del cliente (escapado)
              . '<tr><td style="padding:8px 0;border-bottom:1px solid #f5ede8;color:#888">Email</td>'
              . '<td style="padding:8px 0;border-bottom:1px solid #f5ede8;color:#1a1a1a">'         . htmlspecialchars($data['email'] ?? '—')              . '</td></tr>' // Fila con email, muestra "—" si no se proporcionó
              . '<tr><td style="padding:8px 0;border-bottom:1px solid #f5ede8;color:#888">Fecha</td>'
              . '<td style="padding:8px 0;border-bottom:1px solid #f5ede8;font-weight:600;color:#7b1c2b">' . $fecha_fmt                                   . '</td></tr>' // Fila con la fecha formateada en español y color burdeos
              . '<tr><td style="padding:8px 0;border-bottom:1px solid #f5ede8;color:#888">Hora</td>'
              . '<td style="padding:8px 0;border-bottom:1px solid #f5ede8;font-weight:600;color:#7b1c2b">' . htmlspecialchars($data['hora'])              . '</td></tr>' // Fila con la hora de la reserva en burdeos
              . '<tr><td style="padding:8px 0;border-bottom:1px solid #f5ede8;color:#888">Personas</td>'
              . '<td style="padding:8px 0;border-bottom:1px solid #f5ede8;font-weight:600;color:#1a1a1a">' . $data['personas']                            . '</td></tr>' // Fila con el número de comensales
              . '</table>';

            if (!empty($data['notas'])) {                                                           // Solo añade el bloque de notas si el cliente escribió algo
                $html .= '<div style="margin-top:16px;padding:14px;background:#fdf5f0;border-radius:8px;border-left:3px solid #7b1c2b">' // Caja destacada con borde izquierdo burdeos
                       . '<p style="margin:0 0 4px;font-size:12px;color:#888;text-transform:uppercase;letter-spacing:.08em">Notas</p>'    // Etiqueta "NOTAS" en gris pequeño
                       . '<p style="margin:0;color:#333;white-space:pre-wrap">' . htmlspecialchars($data['notas']) . '</p>'               // Texto de las notas preservando saltos de línea
                       . '</div>';
            }

            $html .= '</div>'
                   . '<p style="font-size:11px;color:#aaa;text-align:center;padding:10px">Reserva recibida desde misswhitney.es</p>' // Pie de email con origen de la solicitud
                   . '</div>';

            Mail::send([], [], function ($m) use ($data, $destino, $html) {            // Construye y envía el email con una función anónima
                $m->to($destino)                                                        // Envía al email del restaurante
                  ->subject('Nueva reserva — ' . $data['nombre'] . ' · ' . $data['fecha'] . ' ' . $data['hora']) // Asunto con nombre, fecha y hora para identificar rápido
                  ->html($html);                                                         // Adjunta el HTML construido arriba

                if (!empty($data['email'])) {                                           // Solo configura Reply-To si el cliente dejó su email
                    $m->replyTo($data['email'], $data['nombre']);                       // Permite al restaurante responder directamente al cliente
                }
            });

            return response()->json(['ok' => true]);                                    // Devuelve éxito al frontend

        } catch (\Exception $e) {
            Log::error('MW reserva: ' . $e->getMessage());                             // Registra el error en el log de Laravel
            return response()->json(['ok' => false, 'mensaje' => 'Error al enviar. Inténtalo de nuevo.'], 500); // Devuelve error 500 al frontend
        }
    }
}
