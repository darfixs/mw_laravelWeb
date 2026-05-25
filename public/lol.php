<?php
// Borrar manualmente el cache de config
$files = glob(__DIR__ . '/../bootstrap/cache/*.php');
foreach ($files as $f) { @unlink($f); }
echo "Cache borrada OK";