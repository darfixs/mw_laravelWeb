<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pwd = config('database.connections.mysql.password');
echo "Longitud password leída: " . strlen($pwd) . " caracteres<br>";
echo "Primeros 3: <b>" . substr($pwd, 0, 3) . "</b><br>";
echo "Últimos 3: <b>" . substr($pwd, -3) . "</b><br>";
echo "Hex completo: <code>" . bin2hex($pwd) . "</code><br>";