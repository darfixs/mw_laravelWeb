<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Test Bootstrap · Miss Whitney</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body { background: #fbf6ef; font-family: sans-serif; padding: 2rem; }
    .check { font-size: 1.1rem; padding: .6rem 1rem; border-radius: 8px; margin-bottom: .5rem; }
    .ok  { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .err { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .info{ background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    pre  { background: #2d2d2d; color: #f8f8f2; padding: 1rem; border-radius: 8px; font-size: .85rem; }
  </style>
</head>
<body>
  <h2 style="color:#8c1426">🔧 Diagnóstico Bootstrap · Miss Whitney</h2>
  <hr>

  <h4>PHP (servidor)</h4>

  <?php
  // PHP version
  echo '<div class="check info">PHP ' . PHP_VERSION . ' · ' . php_uname('s') . '</div>';

  // Test CDN reachable from server
  $cdnJs  = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js';
  $cdnCss = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css';

  function testUrl($url) {
    $headers = @get_headers($url);
    if ($headers && strpos($headers[0], '200') !== false) return true;
    // Fallback con curl
    if (function_exists('curl_init')) {
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_NOBODY, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 5);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_exec($ch);
      $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
      return $code === 200;
    }
    return false;
  }

  $cssOk = testUrl($cdnCss);
  $jsOk  = testUrl($cdnJs);

  echo '<div class="check ' . ($cssOk ? 'ok' : 'err') . '">';
  echo ($cssOk ? '✅' : '❌') . ' Bootstrap CSS CDN → ' . ($cssOk ? 'accesible desde el servidor' : 'NO accesible desde el servidor');
  echo '</div>';

  echo '<div class="check ' . ($jsOk ? 'ok' : 'err') . '">';
  echo ($jsOk ? '✅' : '❌') . ' Bootstrap JS CDN → ' . ($jsOk ? 'accesible desde el servidor' : 'NO accesible desde el servidor');
  echo '</div>';
  ?>

  <h4 class="mt-4">JavaScript (navegador)</h4>
  <div id="js-results"></div>

  <h4 class="mt-4">Tooltips en vivo</h4>
  <p>Pasa el ratón por encima del botón:</p>
  <button class="btn btn-danger"
          data-bs-toggle="tooltip"
          data-bs-placement="right"
          data-bs-title="¡Bootstrap Tooltip funcionando! ✅">
    Hover aquí para probar tooltip
  </button>

  <h4 class="mt-4">Nav Pills (componente Bootstrap)</h4>
  <ul class="nav nav-pills gap-2 mt-2">
    <li class="nav-item"><button class="nav-link active">Entrantes</button></li>
    <li class="nav-item"><button class="nav-link">Principales</button></li>
    <li class="nav-item"><button class="nav-link">Postres</button></li>
    <li class="nav-item"><button class="nav-link">Bebidas</button></li>
  </ul>
  <small class="text-muted">Si los pills se ven en rojo/burdeos al hacer clic → Bootstrap + estilos MW funcionan.</small>

  <hr class="mt-4">
  <a href="/" class="btn btn-outline-secondary btn-sm">← Volver a la web</a>
  <a href="?del=1" class="btn btn-outline-danger btn-sm ms-2">🗑 Eliminar este archivo de diagnóstico</a>

  <?php
  // Auto-delete option
  if (isset($_GET['del'])) {
    unlink(__FILE__);
    echo '<div class="check ok mt-3">✅ Archivo eliminado correctamente.</div>';
  }
  ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    var out = document.getElementById('js-results');

    // Test 1: Bootstrap definido
    var bsDefined = typeof bootstrap !== 'undefined';
    out.innerHTML += '<div class="check ' + (bsDefined ? 'ok' : 'err') + '">' +
      (bsDefined ? '✅' : '❌') +
      ' <code>typeof bootstrap</code> → <strong>' + typeof bootstrap + '</strong>' +
      (bsDefined ? '' : ' — Bootstrap JS NO se cargó') +
      '</div>';

    // Test 2: Tooltip disponible
    if (bsDefined) {
      var tooltipOk = typeof bootstrap.Tooltip !== 'undefined';
      out.innerHTML += '<div class="check ' + (tooltipOk ? 'ok' : 'err') + '">' +
        (tooltipOk ? '✅' : '❌') + ' <code>bootstrap.Tooltip</code> disponible' +
        '</div>';

      // Inicializar tooltips
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
        new bootstrap.Tooltip(el);
      });
    }

    // Test 3: Versión
    if (bsDefined && bootstrap.Tooltip) {
      out.innerHTML += '<div class="check info">ℹ️ Bootstrap versión cargada correctamente desde jsDelivr CDN</div>';
    }
  </script>
</body>
</html>
