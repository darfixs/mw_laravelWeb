<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Simulate the API call
$request = Illuminate\Http\Request::create('/api/facturas', 'GET');

try {
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "<br><br>";
    echo "<pre>" . htmlspecialchars($response->getContent()) . "</pre>";
} catch (Throwable $e) {
    echo "<h3>EXCEPCION:</h3>";
    echo "<b>Mensaje:</b> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<b>Archivo:</b> " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "<b>Trace:</b><br><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}