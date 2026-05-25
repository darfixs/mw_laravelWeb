<?php
chdir(__DIR__ . '/..');
$output = shell_exec('composer dump-autoload 2>&1');
echo "<pre>" . htmlspecialchars($output ?: 'shell_exec deshabilitado') . "</pre>";