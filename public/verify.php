<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "<h3>Lo que Laravel LEE del config:</h3>";
echo "DB_HOST: <b>" . config('database.connections.mysql.host') . "</b><br>";
echo "DB_DATABASE: <b>" . config('database.connections.mysql.database') . "</b><br>";
echo "DB_USERNAME: <b>" . config('database.connections.mysql.username') . "</b><br>";
echo "APP_URL: <b>" . config('app.url') . "</b><br>";

echo "<h3>Lo que hay en el .env directo:</h3>";
$envContent = file_get_contents(__DIR__.'/../.env');
echo "<pre>" . htmlspecialchars($envContent) . "</pre>";

echo "<h3>Archivos en bootstrap/cache:</h3>";
foreach (glob(__DIR__.'/../bootstrap/cache/*.php') as $file) {
    echo basename($file) . " (" . filesize($file) . " bytes)<br>";
}