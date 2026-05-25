<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Tu factura · Miss Whitney</title>
<style>
  body { margin:0; padding:0; background:#f7f3f3; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color:#1b1b1b; }
  .wrap { max-width: 560px; margin: 0 auto; background:#fff; }
  .header { background:#a52337; color:#fff; padding:24px 28px; }
  .brand { font-size:24px; font-style:italic; font-weight:bold; margin:0; }
  .body { padding:28px; }
  .body h2 { font-size:18px; margin:0 0 12px; color:#1b1b1b; font-weight:600; }
  .body p { font-size:14px; line-height:1.6; color:#4a4040; margin:0 0 14px; }
  .ref-box { background:#fdf2f4; border:1px solid #f3d4d9; border-radius:8px;
             padding:12px 16px; margin:18px 0; text-align:center; }
  .ref-box .lbl { font-size:11px; color:#a52337; text-transform:uppercase; letter-spacing:.08em; font-weight:600; }
  .ref-box .num { font-size:18px; font-weight:bold; color:#1b1b1b; margin-top:4px; }
  .footer { background:#1b1b1b; color:#999; padding:14px 28px; font-size:11px; text-align:center; }
  .footer a { color:#bbb; text-decoration:none; }
</style>
</head>
<body>
  <div class="wrap">
    <div class="header">
      <div class="brand">Miss Whitney</div>
    </div>
    <div class="body">
      <h2>Hola {{ $nombreReceptor }},</h2>
      <p>Aquí tienes tu factura en formato PDF, lista para descargar y guardar.</p>
      <div class="ref-box">
        <div class="lbl">Número de factura</div>
        <div class="num">{{ $numeroFactura }}</div>
      </div>
      <p>Gracias por confiar en nosotros. Si tienes cualquier duda sobre la factura, escríbenos a <a href="mailto:misswhitneycontacto@gmail.com" style="color:#a52337">misswhitneycontacto@gmail.com</a>.</p>
      <p style="margin-top:20px;color:#888;font-size:12px">Un saludo,<br><b>Miss Whitney</b></p>
    </div>
    <div class="footer">
      @misswhitneyrestaurante &nbsp;·&nbsp; MissWhitney Huelva &nbsp;·&nbsp; misswhitney.com
    </div>
  </div>
</body>
</html>
