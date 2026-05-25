<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "<h3>1. Configuración:</h3>";
$key = env('GOOGLE_VISION_API_KEY');
echo "GOOGLE_VISION_API_KEY: " . ($key ? '✅ presente (' . strlen($key) . ' chars)' : '❌ VACÍA') . "<br>";

echo "<h3>2. Archivo del controlador:</h3>";
$ctrl = __DIR__ . '/../app/Http/Controllers/OcrController.php';
if (file_exists($ctrl)) {
    echo "✅ existe (" . filesize($ctrl) . " bytes, " . date('Y-m-d H:i:s', filemtime($ctrl)) . ")<br>";
    echo "Contiene 'procesarTicket': " . (str_contains(file_get_contents($ctrl), 'procesarTicket') ? '✅' : '❌') . "<br>";
} else {
    echo "❌ NO existe<br>";
}

echo "<h3>3. Test directo a Google Vision:</h3>";
if (!$key) {
    echo "Sin API key — saltando<br>";
} else {
    // Imagen de prueba — un texto simple
    $testUrl = 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/41/Hello_World.svg/640px-Hello_World.svg.png';
    $img = @file_get_contents($testUrl);
    if (!$img) {
        echo "⚠️ No se pudo descargar imagen de prueba — el servidor no permite outbound HTTP<br>";
    } else {
        $payload = json_encode([
            'requests' => [[
                'image' => ['content' => base64_encode($img)],
                'features' => [['type' => 'TEXT_DETECTION']],
            ]],
        ]);
        $ch = curl_init('https://vision.googleapis.com/v1/images:annotate?key=' . $key);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        echo "HTTP code: <b>$code</b><br>";
        if ($err) echo "cURL error: <b>$err</b><br>";
        $data = json_decode($resp, true);
        if (isset($data['responses'][0]['textAnnotations'][0]['description'])) {
            echo "✅ <b style='color:green'>Vision API funciona</b><br>";
            echo "Texto detectado: " . htmlspecialchars($data['responses'][0]['textAnnotations'][0]['description']) . "<br>";
        } elseif (isset($data['error'])) {
            echo "❌ <b style='color:red'>Error de Vision:</b><br><pre>" . htmlspecialchars(json_encode($data['error'], JSON_PRETTY_PRINT)) . "</pre>";
        } else {
            echo "⚠️ Respuesta inesperada:<br><pre>" . htmlspecialchars(substr($resp, 0, 500)) . "</pre>";
        }
    }
}

echo "<h3>4. Últimas 5 líneas del log:</h3>";
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -10);
    echo "<pre style='font-size:11px;background:#f5f5f5;padding:10px'>" . htmlspecialchars(implode('', $lastLines)) . "</pre>";
}