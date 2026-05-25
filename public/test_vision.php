<?php
$apiKey = 'AIzaSyB...PEGA_AQUI_TU_CLAVE_REAL';

// Imagen de prueba (un texto simple en base64)
$testImageBase64 = base64_encode(file_get_contents(
    'https://upload.wikimedia.org/wikipedia/commons/thumb/4/41/Hello_World.svg/640px-Hello_World.svg.png'
));

$payload = json_encode([
    'requests' => [[
        'image'    => ['content' => $testImageBase64],
        'features' => [['type' => 'TEXT_DETECTION', 'maxResults' => 1]],
    ]],
]);

$ch = curl_init('https://vision.googleapis.com/v1/images:annotate?key=' . $apiKey);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h3>Status HTTP: $status</h3>";
$data = json_decode($response, true);

if (isset($data['responses'][0]['textAnnotations'][0]['description'])) {
    echo "<h2 style='color:green'>✅ FUNCIONA</h2>";
    echo "<p>Texto detectado: <b>" . htmlspecialchars($data['responses'][0]['textAnnotations'][0]['description']) . "</b></p>";
} elseif (isset($data['error'])) {
    echo "<h2 style='color:red'>❌ Error</h2>";
    echo "<pre>" . htmlspecialchars(json_encode($data['error'], JSON_PRETTY_PRINT)) . "</pre>";
} else {
    echo "<h2 style='color:orange'>⚠️ Respuesta inesperada</h2>";
    echo "<pre>" . htmlspecialchars(substr($response, 0, 1000)) . "</pre>";
}