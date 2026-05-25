<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Buscar la última solicitud guardada y mostrar lo que el OCR habría visto
$logFile = __DIR__ . '/../storage/logs/laravel.log';
$content = file_get_contents($logFile);

// Buscar el último OCR exitoso en el log (debería estar guardado el texto raw)
echo "<h3>Para diagnosticar, sube de nuevo un ticket en la web cliente,</h3>";
echo "<p>luego refresca esta página. Aquí veremos qué leyó realmente Google Vision.</p>";

// Buscar entradas con "OCR debug texto"
preg_match_all('/OCR debug texto: (.+?)(?=\n\[|\Z)/s', $content, $matches);
if (!empty($matches[1])) {
    $last = end($matches[1]);
    echo "<h3>Texto que leyó Google Vision (último ticket):</h3>";
    echo "<pre style='background:#f5f5f5;padding:15px;font-family:monospace;font-size:12px'>";
    echo htmlspecialchars($last);
    echo "</pre>";
} else {
    echo "<p>⚠️ No hay log de OCR todavía. Necesitamos añadir un log temporal al controlador.</p>";
}