<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('config:clear');
$kernel->call('route:clear');
$kernel->call('view:clear');
$kernel->call('cache:clear');
echo 'OK - todo limpiado';