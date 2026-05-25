<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 9pt;
      color: #2a2a2a;
    }

    .page {
      width: 85%;
      margin: 30px auto;
    }

    .header {
      background: #8f1d2c;
      color: #fff;
      padding: 14px 18px;
    }

    .header-inner {
      display: table;
      width: 100%;
    }

    .h-left {
      display: table-cell;
      vertical-align: middle;
      width: 55%;
    }

    .h-right {
      display: table-cell;
      text-align: right;
      width: 45%;
    }

    .brand {
      font-family: "Georgia", serif;
      font-size: 26pt;
      font-style: italic;
    }

    .brand::first-letter {
      font-size: 30pt;
      font-weight: bold;
    }

    .brand-info {
      font-size: 7pt;
      color: #f3c9ce;
      margin-top: 5px;
    }

    .inv-label {
      font-size: 7pt;
      letter-spacing: .2em;
      color: #f3c9ce;
      text-transform: uppercase;
    }

    .inv-num {
      font-size: 16pt;
      font-weight: bold;
    }

    .inv-dates {
      font-size: 9pt;
      color: #f3c9ce;
      margin-top: 6px;
      line-height: 1.6;
    }

    .stripe {
      background: #5e121c;
      height: 2px;
      margin-bottom: 12px;
    }

    .info-wrap {
      margin: 16px 0;
    }

    .info-left {
      width: 48%;
      float: left;
    }

    .info-right {
      width: 48%;
      float: right;
      text-align: right;
    }

    .info-label {
      font-size: 7pt;
      font-weight: bold;
      color: #8f1d2c;
      letter-spacing: .15em;
      text-transform: uppercase;
      border-bottom: 1px solid #8f1d2c;
      padding-bottom: 3px;
      margin-bottom: 6px;
    }

    .info-box {
      background: #fafafa;
      border: 1px solid #eee;
      border-radius: 4px;
      padding: 8px 10px;
    }

    .r-name {
      font-size: 10pt;
      font-weight: bold;
    }

    .r-line {
      font-size: 8pt;
      color: #5a4f4f;
      line-height: 1.5;
    }

    .clearfix {
      clear: both;
    }

    /* TABLA */
    .table-wrap {
      margin-top: 16px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    thead tr {
      background: #2b2b2b;
      color: #fff;
    }

    thead th {
      padding: 6px 8px;
      font-size: 7pt;
      text-transform: uppercase;
    }

    tbody tr:nth-child(even) {
      background: #fcfcfc;
    }

    tbody td {
      padding: 7px 8px;
      border-bottom: 1px solid #eee;
    }

    .r {
      text-align: right;
    }

    .c {
      text-align: center;
    }

    .totals-wrap {
      margin-top: 10px;
    }

    .totals-table {
      float: right;
      width: 200px;
    }

    .totals-table td {
      padding: 4px 6px;
    }

    .total-row td {
      background: #8f1d2c;
      color: #fff;
      font-weight: bold;
      padding: 6px;
    }

    .spacer {
      height: 120px;
    }

    .footer {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      font-size: 7pt;
      color: #777;
      text-align: center;
      border-top: 1px solid #eee;
      padding: 10px;
    }
  </style>
</head>

<body>

  <div class="page">

    <div class="header">
      <div class="header-inner">
        <div class="h-left">
          <div class="brand">Miss Whitney</div>
          <div class="brand-info">
            misswhitney.com · misswhitneyfacturas@gmail.com
          </div>
        </div>
        <div class="h-right">
          <div class="inv-label">Factura</div>
          <div class="inv-num">{{ $numero_factura ?? '-' }}</div>

          <div class="inv-dates">
            Emisión: {{ $fecha_emision ? \Carbon\Carbon::parse($fecha_emision)->format('d/m/Y') : date('d/m/Y') }}<br>
            Consumo: {{ $fecha_consumo ? \Carbon\Carbon::parse($fecha_consumo)->format('d/m/Y') : '-' }}
          </div>
        </div>
      </div>
    </div>

    <div class="stripe"></div>

    <div class="info-wrap">

      <div class="info-left">
        <div class="info-label">Restaurante</div>
        <div class="info-box">
          <div class="r-name">Miss Whitney</div>
          <div class="r-line">Maria Luisa Santos Nieves</div>
          <div class="r-line">NIF: 29051027A</div>
          <div class="r-line">Avda. Escultora Miss Whitney, 15</div>
          <div class="r-line">21003 Huelva</div>
          <div class="r-line"></div>
        </div>
      </div>

      <div class="info-right">
        <div class="info-label">Cliente</div>
        <div class="info-box">

          @php $nombre_display = !empty($receptor_empresa) ? $receptor_empresa : $receptor_nombre; @endphp

          <div class="r-name">{{ $nombre_display }}</div>

          @if(!empty($receptor_empresa))
          <div class="r-line">{{ $receptor_nombre }}</div>
          @endif

          <div class="r-line">NIF/CIF: {{ $receptor_nif }}</div>

          @if(!empty($receptor_direccion) && !in_array(trim($receptor_direccion), ['Alta manual admin','Alta admin','N/A','']))
          <div class="r-line">
            {{ $receptor_direccion }}
          </div>
          @endif

          @if(!empty($receptor_cp) && $receptor_cp !== '00000')
          <div class="r-line">
            {{ $receptor_cp }} {{ $receptor_ciudad ?? '' }}
          </div>
          @endif

          <div class="r-line">{{ $receptor_email }}</div>

        </div>
      </div>

      <div class="clearfix"></div>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Descripción</th>
            <th class="c">Uds.</th>
            <th class="r">Importe</th>
          </tr>
        </thead>

        <tbody>
          @if(!empty($lineas_ticket) && is_array($lineas_ticket))
            @foreach($lineas_ticket as $l)
            <tr>
              <td>{{ $l['concepto'] ?? '-' }}</td>
              <td class="c">{{ $l['cantidad'] ?? 1 }}</td>
              <td class="r">{{ number_format($l['importe'] ?? 0, 2, ',', '.') }} €</td>
            </tr>
            @endforeach
          @else
            <tr>
              <td>{{ $concepto ?? 'Consumición' }}</td>
              <td class="c">1</td>
              <td class="r">{{ number_format($total ?? 0, 2, ',', '.') }} €</td>
            </tr>
          @endif
        </tbody>
      </table>
    </div>

    <div class="totals-wrap">
      <table class="totals-table">
        <tr>
          <td>Base:</td>
          <td class="r">{{ number_format($base ?? 0, 2, ',', '.') }} €</td>
        </tr>
        <tr>
          <td>IVA:</td>
          <td class="r">{{ number_format($civa ?? 0, 2, ',', '.') }} €</td>
        </tr>
        <tr class="total-row">
          <td>Total:</td>
          <td class="r">{{ number_format($total ?? 0, 2, ',', '.') }} €</td>
        </tr>
      </table>
      <div class="clearfix"></div>
    </div>

    <div class="spacer"></div>

  </div>

  <div class="footer">
    Factura emitida electrónicamente · RD 1619/2012 · IVA 10%<br>
    @misswhitneyrestaurante · misswhitney.com · misswhitneyfacturas@gmail.com
  </div>

</body>

</html>