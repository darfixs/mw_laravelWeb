<?php
// Subir a public/debug.php, abrir en navegador, luego BORRAR
require '../vendor/autoload.php';
$app = require '../bootstrap/app.php';

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=darioalfaro;charset=utf8mb4',
        'darioalfaro',
        'ntB#3ROhxgs7$2mf'
    );
    echo '✅ Conexión BD OK<br>';
    
    // Test tablas
    $tablas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo 'Tablas: ' . implode(', ', $tablas) . '<br>';
    
} catch (Exception $e) {
    echo '❌ Error BD: ' . $e->getMessage() . '<br>';
}

// Test rutas
echo 'APP_URL: ' . env('APP_URL') . '<br>';
echo 'DB_HOST: ' . env('DB_HOST') . '<br>';