<?php
$dir = __DIR__ . '/../storage/framework/views/';
$count = 0;
foreach (glob($dir . '*.php') as $file) {
    if (unlink($file)) $count++;
}
echo "✅ $count vistas cacheadas eliminadas";