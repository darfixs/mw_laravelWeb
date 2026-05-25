<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Http, Cache, Log};     // Importa el cliente HTTP, el sistema de caché y el registro de errores

class ResenasController extends Controller
{
    /**
     * GET /api/resenas
     *
     * Devuelve las reseñas públicas del restaurante desde Google Places API.
     * Hace 5 llamadas (es/en/fr/it/de), deduplica por autor+timestamp y
     * almacena el resultado 12 horas en caché.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $placeId = env('GOOGLE_PLACE_ID');              // Lee el ID del establecimiento en Google Maps desde el .env
        $apiKey  = env('GOOGLE_VISION_API_KEY');        // Lee la clave de API de Google desde el .env

        if (!$placeId || !$apiKey) {                    // Comprueba que ambas variables estén configuradas
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Reseñas no configuradas',
            ], 500);                                    // Devuelve error 500 si falta alguna variable de entorno
        }

        $resultado = Cache::remember('resenas_google_v3', 60 * 60 * 12, function () use ($placeId, $apiKey) { // Devuelve resultado cacheado o ejecuta el bloque si han pasado 12 horas

            $idiomas = ['es', 'en', 'fr', 'it', 'de']; // Lista de idiomas para hacer 5 llamadas distintas a Google Places
            $rating  = null;                            // Valoración global del restaurante (se rellena en la primera llamada exitosa)
            $total   = 0;                               // Número total de reseñas del establecimiento
            $url     = null;                            // URL de la ficha del restaurante en Google Maps
            $nombre  = 'Miss Whitney';                  // Nombre del establecimiento (valor por defecto)
            $resenas = [];                              // Array de reseñas indexado por fingerprint para evitar duplicados

            foreach ($idiomas as $lang) {               // Itera sobre cada idioma para obtener reseñas distintas
                try {
                    $r = Http::timeout(15)->get(        // Hace una petición GET a la API de Google Places con timeout de 15 segundos
                        'https://maps.googleapis.com/maps/api/place/details/json',
                        [
                            'place_id'                => $placeId,         // ID del establecimiento en Google Maps
                            'fields'                  => 'name,rating,user_ratings_total,reviews,url', // Campos que queremos que devuelva la API
                            'language'                => $lang,            // Idioma de las reseñas a solicitar
                            'reviews_sort'            => $lang === 'es' ? 'most_relevant' : 'newest',  // En español pide las más relevantes; en otros idiomas las más recientes
                            'reviews_no_translations' => 'true',           // Pide a Google que no traduzca automáticamente las reseñas
                            'key'                     => $apiKey,          // Clave de autenticación de la API
                        ]
                    );

                    if (!$r->ok()) continue;            // Si la respuesta HTTP no es 2xx, salta al siguiente idioma
                    $data = $r->json();                 // Decodifica el JSON de respuesta de Google
                    if (($data['status'] ?? '') !== 'OK') continue; // Si Google devuelve un estado distinto de OK, salta al siguiente idioma

                    $result = $data['result'] ?? [];    // Extrae el objeto 'result' de la respuesta de Google Places

                    if ($rating === null) {             // Solo extrae los metadatos globales de la primera llamada exitosa
                        $rating = $result['rating']             ?? null; // Valoración media del restaurante (ej: 4.7)
                        $total  = $result['user_ratings_total'] ?? 0;    // Número total de reseñas publicadas
                        $url    = $result['url']                ?? null; // URL directa a la ficha en Google Maps
                        $nombre = $result['name']               ?? $nombre; // Nombre oficial del establecimiento según Google
                    }

                    foreach ($result['reviews'] ?? [] as $rev) {    // Itera cada reseña individual devuelta por Google
                        $texto = trim($rev['text'] ?? '');           // Extrae y limpia el texto de la reseña
                        if ($texto === '') continue;                  // Descarta reseñas sin texto

                        $autor = $rev['author_name'] ?? 'Anónimo';  // Nombre del autor de la reseña
                        $time  = (int) ($rev['time'] ?? 0);          // Timestamp Unix de cuándo se publicó la reseña
                        $rRat  = (int) ($rev['rating'] ?? 5);        // Puntuación dada por este usuario (1-5 estrellas)

                        if ($rRat < 4) continue;                     // Descarta reseñas negativas (menos de 4 estrellas)

                        $fp = hash('sha256', $autor . '|' . $time); // Genera un fingerprint único combinando autor y timestamp para identificar la reseña

                        if (isset($resenas[$fp]) && mb_strlen($texto) <= mb_strlen($resenas[$fp]['texto'])) {
                            continue;                                // Si ya existe esta reseña y la nueva versión no es más larga, la ignora
                        }

                        $resenas[$fp] = [                            // Guarda o reemplaza la reseña con el fingerprint como clave
                            'autor'           => $autor,             // Nombre del autor
                            'autor_foto'      => $rev['profile_photo_url'] ?? null, // URL de la foto de perfil del autor
                            'rating'          => $rRat,              // Puntuación de esta reseña
                            'texto'           => $texto,             // Contenido de la reseña
                            'tiempo_relativo' => $rev['relative_time_description'] ?? null, // Texto relativo del tiempo, ej: "hace 2 meses"
                            'timestamp'       => $time,              // Timestamp Unix para ordenar cronológicamente
                        ];
                    }
                } catch (\Throwable $e) {
                    Log::debug("Reseñas {$lang}: " . $e->getMessage()); // Registra el fallo del idioma concreto sin abortar el resto de llamadas
                    continue;                                        // Continúa con el siguiente idioma aunque este falle
                }
            }

            $urlEscribir = 'https://www.google.com/maps/place/Nuevo+Restaurante+Bar+Whitney/@37.2538009,-6.9445445,17z/data=!4m8!3m7!1s0xd11d03d2c971959:0xc7a2d59e76abffa!8m2!3d37.2538009!4d-6.9445445!9m1!1b1!16s%2Fg%2F11btmcb_25'; // URL directa al formulario de escritura de reseñas en Google Maps

            return [
                'ok'             => true,                            // Indica que la operación fue correcta
                'rating_global'  => $rating,                         // Valoración media global del restaurante
                'total_resenas'  => $total,                          // Número total de reseñas publicadas
                'url_google'     => $url,                            // URL de la ficha del restaurante en Google Maps
                'url_escribir'   => $urlEscribir,                    // URL para abrir directamente el formulario de nueva reseña
                'resenas'        => array_values($resenas),          // Array de reseñas deduplicadas (array_values resetea las claves numéricas)
                'actualizado'    => now()->toIso8601String(),         // Fecha y hora de la última actualización del caché en formato ISO 8601
            ];
        });

        return response()->json($resultado);                         // Devuelve el resultado (del caché o recién obtenido) como JSON
    }
}
