<?php
namespace App\Http\Controllers;

use App\Http\Requests\ContactoRequest;                 // Importa la clase que valida el formulario de contacto
use Illuminate\Support\Facades\{Mail, Log};            // Importa el sistema de correo y el registro de errores

class ContactoController extends Controller
{
    /**
     * POST /api/contacto
     *
     * Procesa el formulario de contacto de la web pública y reenvía el mensaje
     * al email del restaurante mediante Gmail SMTP.
     *
     * @param  ContactoRequest $request  Petición validada con los campos del formulario.
     * @return \Illuminate\Http\JsonResponse  { ok: bool, mensaje: string }
     */
    public function enviar(ContactoRequest $request)
    {
        try {
            $data = $request->validated();                                              // Obtiene solo los campos que han pasado la validación

            $asunto  = $data['asunto'] ?? 'Consulta general';                          // Usa el asunto del formulario o un valor por defecto si no viene
            $destino = env('MAIL_FROM_ADDRESS', 'misswhitneyfacturas@gmail.com');      // Lee el email de destino del archivo .env

            Mail::send([], [], function ($m) use ($data, $asunto, $destino) {          // Construye y envía el email con una función anónima
                $m->to($destino)                                                        // Establece el destinatario: el email del restaurante
                  ->replyTo($data['email'], $data['nombre'])                            // Configura Reply-To con el email del cliente para poder responderle directamente
                  ->subject('[Web MW] ' . $asunto . ' · ' . $data['nombre'])           // Construye el asunto con prefijo, asunto seleccionado y nombre del remitente
                  ->html(
                      '<div style="font-family:Arial,sans-serif;max-width:560px;margin:0 auto">'        // Contenedor principal con fuente y ancho máximo
                    . '<div style="background:#7b1c2b;padding:18px 24px">'                              // Cabecera burdeos con el nombre del restaurante
                    . '<h2 style="margin:0;color:#fff;font-style:italic">Miss Whitney</h2>'             // Título en blanco con cursiva
                    . '</div>'
                    . '<div style="padding:24px;background:#fff;border:1px solid #f0e7d8">'             // Cuerpo blanco con borde crema
                    . '<p style="margin:0 0 6px"><strong>Nombre:</strong> ' . htmlspecialchars($data['nombre'])   . '</p>'  // Nombre del remitente (escapado para evitar XSS)
                    . '<p style="margin:0 0 6px"><strong>Email:</strong> '  . htmlspecialchars($data['email'])    . '</p>'  // Email del remitente (escapado)
                    . '<p style="margin:0 0 6px"><strong>Asunto:</strong> ' . htmlspecialchars($asunto)           . '</p>'  // Asunto del mensaje (escapado)
                    . '<hr style="border:none;border-top:1px solid #f0e7d8;margin:14px 0">'             // Línea separadora entre datos y cuerpo del mensaje
                    . '<p style="white-space:pre-wrap;margin:0;color:#333">' . htmlspecialchars($data['mensaje']) . '</p>'  // Cuerpo del mensaje preservando saltos de línea
                    . '</div>'
                    . '<p style="font-size:11px;color:#999;text-align:center;margin-top:8px">'          // Pie de página en gris pequeño
                    . 'Mensaje enviado desde el formulario de contacto de misswhitney.es</p>'            // Texto informativo del origen del mensaje
                    . '</div>'
                  );
            });

            return response()->json(['ok' => true, 'mensaje' => 'Mensaje enviado correctamente']); // Devuelve éxito al frontend con mensaje de confirmación

        } catch (\Exception $e) {
            Log::error('MW contacto: ' . $e->getMessage());                            // Registra el error en el log de Laravel para diagnóstico posterior
            return response()->json(['ok' => false, 'mensaje' => 'Error al enviar el mensaje. Inténtalo de nuevo.'], 500); // Devuelve error 500 al frontend
        }
    }
}
