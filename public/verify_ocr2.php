<?php
$logFile = __DIR__ . '/../storage/logs/laravel.log';
$content = file_get_contents($logFile);

// Buscar las últimas entradas de OCR
$entries = preg_split('/\[(\d{4}-\d{2}-\d{2}[^\]]+)\]/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
$lastOcr = '';
for ($i = count($entries) - 1; $i > 0; $i -= 2) {
    if (isset($entries[$i+1]) && (str_contains($entries[$i+1], 'OCR') || str_contains($entries[$i+1], 'ocr') || str_contains($entries[$i+1], 'Vision'))) {
        $lastOcr = '[' . $entries[$i] . ']' . $entries[$i+1];
        break;
    }
}

if ($lastOcr) {
    echo "<h3>Último error de OCR:</h3>";
    // Solo mostrar las primeras 50 líneas para ver el mensaje
    $lines = explode("\n", $lastOcr);
    $first50 = array_slice($lines, 0, 50);
    echo "<pre style='font-size:11px;background:#f5f5f5;padding:10px'>" . htmlspecialchars(implode("\n", $first50)) . "</pre>";
} else {
    echo "<h3>No hay errores de OCR específicos. Mostrando últimas 60 líneas del log:</h3>";
    $lines = explode("\n", $content);
    $last = array_slice($lines, -60);
    echo "<pre style='font-size:11px;background:#f5f5f5;padding:10px'>" . htmlspecialchars(implode("\n", $last)) . "</pre>";
}