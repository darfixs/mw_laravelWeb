<?php
$blade = file_get_contents(__DIR__ . '/../resources/views/cliente/solicitar-factura.blade.php');

$tieneFuncionEmail = str_contains($blade, 'enviarPorEmail');
$tieneTypeButton   = str_contains($blade, 'id="email-send-btn"') && str_contains($blade, 'type="button" onclick="enviarPorEmail()"');
$tieneFetchAPI     = str_contains($blade, 'window.MW_API_BASE');
$tieneFacturaId    = str_contains($blade, 'window.MW_FACTURA_ID');
$tamanio           = strlen($blade);

echo "Tamaño solicitar-factura.blade.php: <b>$tamanio</b> bytes<br>";
echo "Función enviarPorEmail: " . ($tieneFuncionEmail ? '✅' : '❌') . "<br>";
echo "Botón tiene type='button': " . ($tieneTypeButton ? '✅' : '❌') . "<br>";
echo "Usa window.MW_API_BASE: " . ($tieneFetchAPI ? '✅' : '❌') . "<br>";
echo "Usa window.MW_FACTURA_ID: " . ($tieneFacturaId ? '✅' : '❌') . "<br>";
echo "Modificado: " . date('Y-m-d H:i:s', filemtime(__DIR__.'/../resources/views/cliente/solicitar-factura.blade.php'));

echo "<br><br><h3>Verificar SolicitudController:</h3>";
$ctrl = file_get_contents(__DIR__ . '/../app/Http/Controllers/SolicitudController.php');
$tieneFacturaIdResp = str_contains($ctrl, "'factura_id'") || str_contains($ctrl, '"factura_id"');
echo "Devuelve factura_id en JSON: " . ($tieneFacturaIdResp ? '✅' : '❌') . "<br>";

echo "<br><h3>Verificar FacturaController:</h3>";
$fc = file_get_contents(__DIR__ . '/../app/Http/Controllers/FacturaController.php');
$tieneEnviar = str_contains($fc, 'enviarPorEmail');
echo "Tiene método enviarPorEmail: " . ($tieneEnviar ? '✅' : '❌') . "<br>";

echo "<br><h3>Verificar routes/web.php:</h3>";
$rt = file_get_contents(__DIR__ . '/../routes/web.php');
$tieneRuta = str_contains($rt, "'/{id}/email'");
echo "Tiene ruta POST /{id}/email: " . ($tieneRuta ? '✅' : '❌') . "<br>";

echo "<br><h3>Vista cacheada (debe estar vacía):</h3>";
$cache = glob(__DIR__ . '/../storage/framework/views/*.php');
echo count($cache) . " archivos en views cache<br>";