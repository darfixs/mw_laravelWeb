<?php
/**
 * Cache clear directo (sin exec)
 *
 * USO:
 *   https://tu-web/cc.php?token=mw-clear-cache-7c4r1
 *
 * Borra los archivos cacheados de Laravel directamente, sin necesidad
 * de ejecutar `php artisan`. Útil si exec() está deshabilitado en el hosting.
 *
 * IMPORTANTE: bórralo del servidor después de usarlo.
 */

$TOKEN = 'mw-clear-cache-7c4r1';   // ← cámbialo si quieres

if (($_GET['token'] ?? '') !== $TOKEN) {
    http_response_code(404);
    echo "Not Found";
    exit;
}

header('Content-Type: text/plain; charset=utf-8');

$root = dirname(__DIR__);

/** Borra recursivamente el contenido de una carpeta (no la carpeta en sí) */
function clearDir(string $dir, array &$log, array $keep = ['.gitignore']): void {
    if (!is_dir($dir)) { $log[] = "  (no existe: {$dir})"; return; }
    $items = scandir($dir);
    $count = 0;
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        if (in_array($item, $keep, true)) continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            clearDirRecursive($path);
            @rmdir($path);
            $count++;
        } else {
            @unlink($path);
            $count++;
        }
    }
    $log[] = "  ✓ {$count} elementos eliminados de " . str_replace($GLOBALS['root'] ?? '', '', $dir);
}

function clearDirRecursive(string $dir): void {
    if (!is_dir($dir)) return;
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? clearDirRecursive($path) : @unlink($path);
        if (is_dir($path)) @rmdir($path);
    }
}

$GLOBALS['root'] = $root;
$log = [];

echo "═══════════════════════════════════════════════\n";
echo "  Miss Whitney · Cache cleaner\n";
echo "  " . date('Y-m-d H:i:s') . "\n";
echo "═══════════════════════════════════════════════\n\n";

// ── 1. bootstrap/cache (archivos compilados) ──
echo "▸ Limpiando bootstrap/cache/ (config, routes, services, packages)\n";
$files = ['config.php', 'routes-v7.php', 'services.php', 'packages.php', 'events.php'];
$count = 0;
foreach ($files as $f) {
    $path = $root . '/bootstrap/cache/' . $f;
    if (file_exists($path)) { @unlink($path); $count++; }
}
echo "  ✓ {$count} archivos eliminados\n\n";

// ── 2. storage/framework/cache/data (cache de aplicación) ──
echo "▸ Limpiando storage/framework/cache/data/\n";
clearDir($root . '/storage/framework/cache/data', $log);
echo end($log) . "\n\n";

// ── 3. storage/framework/views (vistas blade compiladas) ──
echo "▸ Limpiando storage/framework/views/\n";
clearDir($root . '/storage/framework/views', $log);
echo end($log) . "\n\n";

// ── 4. storage/framework/sessions (sesiones — opcional, descomenta si quieres) ──
// echo "▸ Limpiando storage/framework/sessions/\n";
// clearDir($root . '/storage/framework/sessions', $log);
// echo end($log) . "\n\n";

echo "═══════════════════════════════════════════════\n";
echo "  Listo.\n";
echo "  Acuérdate de borrar este archivo:\n";
echo "  rm public/cc.php\n";
echo "═══════════════════════════════════════════════\n";
