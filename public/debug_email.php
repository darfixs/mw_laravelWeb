<?php
$blade = file_get_contents(__DIR__ . '/../resources/views/cliente/solicitar-factura.blade.php');

// Localizar el botón email
$pos = strpos($blade, 'email-send-btn');
if ($pos !== false) {
    echo "<h3>Contexto del botón Enviar (cliente):</h3>";
    echo "<pre>" . htmlspecialchars(substr($blade, max(0, $pos - 200), 600)) . "</pre>";
}

// Localizar la función enviarPorEmail
$pos2 = strpos($blade, 'async function enviarPorEmail');
if ($pos2 !== false) {
    echo "<h3>Función enviarPorEmail:</h3>";
    echo "<pre>" . htmlspecialchars(substr($blade, $pos2, 1500)) . "</pre>";
}

// Verificar admin.js función
echo "<hr><h3>Función admin enviarFacturaEmail:</h3>";
$js = file_get_contents(__DIR__ . '/js/admin.js');
$pos3 = strpos($js, 'async function enviarFacturaEmail');
if ($pos3 !== false) {
    echo "<pre>" . htmlspecialchars(substr($js, $pos3, 1200)) . "</pre>";
}

// Verificar bloque admin del botón email
echo "<hr><h3>Botón email en admin.js (renderTable):</h3>";
$pos4 = strpos($js, 'enviarFacturaEmail');
if ($pos4 !== false) {
    echo "<pre>" . htmlspecialchars(substr($js, max(0, $pos4 - 100), 400)) . "</pre>";
}