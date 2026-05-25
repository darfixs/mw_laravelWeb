<?php
$js = file_get_contents(__DIR__ . '/js/admin.js');
$tieneFuncionEmail = str_contains($js, 'enviarFacturaEmail');
$tieneBotonEmail   = str_contains($js, 'Enviar por email');
$tamanio           = strlen($js);

echo "Tamaño admin.js: <b>$tamanio</b> bytes<br>";
echo "Contiene función enviarFacturaEmail: " . ($tieneFuncionEmail ? '✅' : '❌') . "<br>";
echo "Contiene botón 'Enviar por email': " . ($tieneBotonEmail ? '✅' : '❌') . "<br>";
echo "<br>Modificado por última vez: " . date('Y-m-d H:i:s', filemtime(__DIR__.'/js/admin.js'));