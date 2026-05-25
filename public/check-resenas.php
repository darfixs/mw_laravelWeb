<?php
/**
 * Diagnóstico de reseñas — sube a public/check-resenas.php
 *
 *   https://tu-web/check-resenas.php?token=mw-clear-cache-7c4r1
 *
 * Te dice exactamente qué falla: variables .env, tabla BD, llamada a Google.
 */

$TOKEN = 'mw-clear-cache-7c4r1';

if (($_GET['token'] ?? '') !== $TOKEN) {
    http_response_code(404);
    echo "Not Found";
    exit;
}

header('Content-Type: text/plain; charset=utf-8');

echo "═══════════════════════════════════════════════\n";
echo "  Diagnóstico de reseñas · " . date('Y-m-d H:i:s') . "\n";
echo "═══════════════════════════════════════════════\n\n";

// Cargar Laravel para acceder a env(), DB, etc.
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

// ── 1. Variables de entorno ──
echo "── 1. .env ──\n";
$placeId = env('GOOGLE_PLACE_ID');
$apiKey  = env('GOOGLE_VISION_API_KEY');
echo "   GOOGLE_PLACE_ID:        " . ($placeId ? "✓ " . substr($placeId, 0, 20) . '…' : "✗ FALTA") . "\n";
echo "   GOOGLE_VISION_API_KEY:  " . ($apiKey  ? "✓ " . substr($apiKey, 0, 8)  . '… (len=' . strlen($apiKey) . ')' : "✗ FALTA") . "\n\n";

if (!$placeId || !$apiKey) {
    echo "❌ Faltan variables en .env. Solución: añadirlas y vaciar caché.\n";
    exit;
}

// ── 2. Tabla en BD ──
echo "── 2. Tabla resenas_cache ──\n";
try {
    $existe = \Illuminate\Support\Facades\Schema::hasTable('resenas_cache');
    if ($existe) {
        $total = \Illuminate\Support\Facades\DB::table('resenas_cache')->count();
        echo "   ✓ Tabla existe — {$total} reseñas almacenadas\n\n";
    } else {
        echo "   ✗ Tabla NO existe. Solución: php artisan migrate (o pásame por aquí)\n\n";
    }
} catch (\Throwable $e) {
    echo "   ✗ Error BD: " . $e->getMessage() . "\n\n";
}

// ── 3. Llamada directa a Google Places ──
echo "── 3. Llamada a Google Places API ──\n";
$url = 'https://maps.googleapis.com/maps/api/place/details/json?'
     . http_build_query([
        'place_id'                => $placeId,
        'fields'                  => 'name,rating,user_ratings_total,reviews,url',
        'language'                => 'es',
        'reviews_sort'            => 'most_relevant',
        'reviews_no_translations' => 'true',
        'key'                     => $apiKey,
       ]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$raw  = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

echo "   HTTP code: {$http}\n";
if ($err) echo "   cURL error: {$err}\n";

if ($raw) {
    $data = json_decode($raw, true);
    $status = $data['status'] ?? 'NO_STATUS';
    echo "   Status Google: {$status}\n";

    if ($status !== 'OK') {
        echo "   ⚠ error_message: " . ($data['error_message'] ?? '(ninguno)') . "\n";
        echo "\n   Posibles causas:\n";
        echo "   - REQUEST_DENIED: la API key no tiene Places API habilitada.\n";
        echo "     Solución: https://console.cloud.google.com/apis/library/places-backend.googleapis.com\n";
        echo "   - OVER_QUERY_LIMIT: agotada la cuota.\n";
        echo "   - INVALID_REQUEST: place_id incorrecto.\n";
    } else {
        $result = $data['result'] ?? [];
        echo "   ✓ Place: " . ($result['name'] ?? '?') . "\n";
        echo "   ✓ Rating: " . ($result['rating'] ?? '?') . " (" . ($result['user_ratings_total'] ?? 0) . " reseñas totales)\n";
        echo "   ✓ Reseñas devueltas en esta llamada: " . count($result['reviews'] ?? []) . "\n";

        if (!empty($result['reviews'])) {
            echo "\n   Primeras 2 reseñas:\n";
            foreach (array_slice($result['reviews'], 0, 2) as $i => $r) {
                echo "     #" . ($i+1) . " " . ($r['author_name'] ?? '?')
                   . " (" . ($r['rating'] ?? '?') . "★) — "
                   . mb_substr($r['text'] ?? '', 0, 60) . "…\n";
            }
        }
    }
} else {
    echo "   ✗ Respuesta vacía\n";
}

echo "\n── 4. Endpoint /api/resenas ──\n";
echo "   Probando llamada interna…\n";
try {
    $request = \Illuminate\Http\Request::create('/api/resenas', 'GET');
    $response = $app->handle($request);
    $body = $response->getContent();
    $json = json_decode($body, true);
    echo "   HTTP: " . $response->getStatusCode() . "\n";
    if (is_array($json)) {
        echo "   ok: " . ($json['ok'] ? 'true' : 'false') . "\n";
        echo "   rating_global: " . ($json['rating_global'] ?? 'null') . "\n";
        echo "   total_resenas: " . ($json['total_resenas'] ?? 'null') . "\n";
        echo "   reseñas en respuesta: " . count($json['resenas'] ?? []) . "\n";
    } else {
        echo "   Respuesta no es JSON: " . substr($body, 0, 200) . "\n";
    }
} catch (\Throwable $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    echo "   " . $e->getFile() . ':' . $e->getLine() . "\n";
}

echo "\n═══════════════════════════════════════════════\n";
echo "  Acuérdate de borrar este archivo después.\n";
echo "═══════════════════════════════════════════════\n";
