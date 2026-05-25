<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = config('mail.mailers.smtp.username');
$pwd  = config('mail.mailers.smtp.password');
$host = config('mail.mailers.smtp.host');
$port = config('mail.mailers.smtp.port');

echo "Host: <b>" . $host . "</b><br>";
echo "Port: <b>" . $port . "</b><br>";
echo "Username: <b>" . $user . "</b><br>";
echo "Password longitud: <b>" . strlen($pwd) . "</b> caracteres<br>";
echo "Hex: <code>" . bin2hex($pwd) . "</code><br>";
echo "Mailer: <b>" . config('mail.default') . "</b><br>";