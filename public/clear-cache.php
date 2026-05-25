<?php
/**
 * Limpia la caché de vistas compiladas de Laravel.
 * ÚSALO UNA VEZ y luego bórralo del servidor.
 */

$viewPath   = __DIR__ . '/../storage/framework/views/';
$cachePath  = __DIR__ . '/../bootstrap/cache/';

$deleted = 0;
$errors  = 0;

// 1. Vistas Blade compiladas
foreach (glob($viewPath . '*.php') as $file) {
    if (unlink($file)) {
        $deleted++;
    } else {
        $errors++;
    }
}

// 2. Config / routes cacheados
foreach (['config.php', 'routes-v7.php', 'packages.php'] as $f) {
    $fp = $cachePath . $f;
    if (file_exists($fp)) {
        unlink($fp) ? $deleted++ : $errors++;
    }
}

?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Limpiar caché · Miss Whitney</title>
  <style>
    body { font-family: sans-serif; background: #fbf6ef; padding: 2rem; color: #1a0f0a; }
    .ok  { background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .err { background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    a.btn { display: inline-block; background: #8c1426; color: #fbf6ef; padding: .7rem 1.4rem;
            border-radius: 999px; text-decoration: none; font-weight: 600; margin-top: 1rem; }
  </style>
</head>
<body>
  <h2>🗑 Limpieza de caché · Miss Whitney</h2>

  <?php if ($errors === 0): ?>
    <div class="ok">
      ✅ <strong><?= $deleted ?> archivo(s) eliminados</strong> correctamente.<br>
      La caché de vistas y configuración ha sido limpiada.<br>
      Laravel recompilará las vistas en la próxima petición.
    </div>
  <?php else: ?>
    <div class="err">
      ⚠️ <?= $deleted ?> eliminados, <?= $errors ?> con error (puede que ya estuvieran vacíos).
    </div>
  <?php endif; ?>

  <p>Ahora recarga la web principal para que Laravel recompile las vistas con los últimos cambios.</p>

  <a class="btn" href="/">← Ir a la web</a>
  &nbsp;
  <a class="btn" href="?del=1" style="background:#5b3a26">🗑 Eliminar este script</a>

  <?php
  if (isset($_GET['del'])) {
      unlink(__FILE__);
      echo '<p style="color:green;font-weight:600">✅ Script eliminado del servidor.</p>';
  }
  ?>
</body>
</html>
