<?php
$contrasenas_a_probar = [
    'ntB#3ROhxgs7$2mf',
    'ntB#3ROhxgs7\\$2mf',
];

echo "<h3>Test directo PDO con cada contraseña posible:</h3>";

foreach ($contrasenas_a_probar as $i => $pwd) {
    echo "<hr><b>Test " . ($i + 1) . ":</b> longitud " . strlen($pwd) . "<br>";
    echo "Hex: <code>" . bin2hex($pwd) . "</code><br>";
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=darioalfaro', 'darioalfaro', $pwd);
        echo "✅ <b style='color:green'>FUNCIONA</b><br>";
    } catch (Exception $e) {
        echo "❌ " . $e->getMessage() . "<br>";
    }
}

echo "<hr><h3>Lo que tiene Laravel ahora mismo:</h3>";
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$pwd_laravel = config('database.connections.mysql.password');
echo "Longitud: " . strlen($pwd_laravel) . "<br>";
echo "Hex: <code>" . bin2hex($pwd_laravel) . "</code><br>";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=darioalfaro', 'darioalfaro', $pwd_laravel);
    echo "✅ Laravel SÍ podría conectar con esta password<br>";
} catch (Exception $e) {
    echo "❌ Laravel NO puede conectar: " . $e->getMessage() . "<br>";
}