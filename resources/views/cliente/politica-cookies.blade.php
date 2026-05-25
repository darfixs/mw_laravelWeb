@extends('layouts.app')
@section('title','Política de cookies · Miss Whitney')

@push('styles')
<style>
  .policy-section {
    max-width: 760px;
    margin: 0 auto;
    padding: 3rem 1.2rem 6rem;
    color: #1a1a1a;
    line-height: 1.7;
  }
  .policy-section h1 {
    font-family: Georgia, serif;
    font-size: clamp(2rem, 5vw, 2.6rem);
    color: #6e0f1c;
    margin: 0 0 .3rem;
    font-weight: 400;
  }
  .policy-section .last-update {
    font-size: .82rem;
    color: #888;
    margin-bottom: 2.5rem;
    border-bottom: 1px solid #efe6e6;
    padding-bottom: 1.5rem;
  }
  .policy-section h2 {
    font-family: Georgia, serif;
    font-size: 1.4rem;
    color: #3a251a;
    margin: 2.5rem 0 .8rem;
    font-weight: 500;
  }
  .policy-section h3 {
    font-size: 1.05rem;
    color: #5b3a26;
    margin: 1.6rem 0 .6rem;
    font-weight: 600;
  }
  .policy-section p {
    font-size: .95rem;
    margin: 0 0 1rem;
    color: #3a3a3a;
  }
  .policy-section ul {
    padding-left: 1.4rem;
    margin: 0 0 1.2rem;
  }
  .policy-section li {
    font-size: .92rem;
    margin-bottom: .4rem;
    color: #3a3a3a;
  }
  .policy-section a {
    color: #8c1426;
    text-decoration: none;
    border-bottom: 1px solid rgba(140,20,38,.3);
    transition: border-color .2s;
  }
  .policy-section a:hover { border-bottom-color: #8c1426 }

  .cookie-table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.2rem 0 1.8rem;
    background: #fbf6ef;
    border-radius: 10px;
    overflow: hidden;
    font-size: .88rem;
  }
  .cookie-table th, .cookie-table td {
    padding: .7rem .9rem;
    text-align: left;
    border-bottom: 1px solid #efe6e6;
  }
  .cookie-table th {
    background: #f0e7d8;
    font-weight: 600;
    color: #3a251a;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .05em;
  }
  .cookie-table tr:last-child td { border-bottom: none }
  .cookie-table tr:hover td { background: rgba(200,155,60,.05) }
  .cookie-table .badge {
    display: inline-block;
    padding: .15rem .55rem;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 600;
  }
  .badge-necesaria { background: rgba(46,160,67,.15); color: #2da64a }
  .badge-funcional { background: rgba(200,155,60,.18); color: #a07a25 }
  .badge-tercero   { background: rgba(140,20,38,.12); color: #8c1426 }

  .cta-config {
    background: #fbf6ef;
    border: 1px solid #efe6e6;
    border-radius: 14px;
    padding: 1.4rem 1.6rem;
    margin: 2.5rem 0;
    text-align: center;
  }
  .cta-config p { margin: 0 0 .9rem; font-size: .95rem }
  .btn-config {
    display: inline-flex; align-items: center; gap: .4rem;
    background: #8c1426; color: #fbf6ef;
    border: none; padding: .7rem 1.4rem;
    border-radius: 10px; font-weight: 600;
    font-size: .9rem; cursor: pointer;
    box-shadow: 0 6px 18px rgba(140,20,38,.25);
    transition: all .2s;
  }
  .btn-config:hover { background: #6e0f1c; transform: translateY(-2px) }

  @media (max-width:600px) {
    .cookie-table { font-size: .8rem }
    .cookie-table th, .cookie-table td { padding: .5rem .6rem }
  }
</style>
@endpush

@section('content')
<section class="policy-section">
  <h1>Política de cookies</h1>
  <p class="last-update">Última actualización: {{ date('d/m/Y') }}</p>

  <p>
    En <strong>Miss Whitney</strong> usamos cookies y tecnologías similares para que la web funcione correctamente,
    recordar tus preferencias y ofrecerte una mejor experiencia. Esta política te explica qué cookies utilizamos,
    para qué sirven y cómo puedes gestionarlas.
  </p>

  <h2>¿Qué son las cookies?</h2>
  <p>
    Las cookies son pequeños archivos de texto que se guardan en tu dispositivo cuando visitas una web.
    Permiten reconocerte en visitas posteriores y guardar información como tus preferencias de idioma,
    consentimiento de cookies o sesión iniciada.
  </p>

  <h2>Tipos de cookies que usamos</h2>

  <table class="cookie-table">
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Tipo</th>
        <th>Finalidad</th>
        <th>Duración</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><code>mw_cookies_consent</code></td>
        <td><span class="badge badge-necesaria">Necesaria</span></td>
        <td>Recordar tu decisión sobre las cookies</td>
        <td>1 año</td>
      </tr>
      <tr>
        <td><code>XSRF-TOKEN</code></td>
        <td><span class="badge badge-necesaria">Necesaria</span></td>
        <td>Protección contra ataques CSRF</td>
        <td>Sesión</td>
      </tr>
      <tr>
        <td><code>miss-whitney-session</code></td>
        <td><span class="badge badge-necesaria">Necesaria</span></td>
        <td>Sesión del usuario en la web</td>
        <td>2 horas</td>
      </tr>
      <tr>
        <td>Google Maps</td>
        <td><span class="badge badge-tercero">Tercero</span></td>
        <td>Mostrar el mapa interactivo de ubicación</td>
        <td>Variable</td>
      </tr>
      <tr>
        <td>Google Fonts</td>
        <td><span class="badge badge-tercero">Tercero</span></td>
        <td>Cargar las tipografías de la web</td>
        <td>Variable</td>
      </tr>
    </tbody>
  </table>

  <h3>Cookies necesarias</h3>
  <p>
    Imprescindibles para el funcionamiento básico de la web. Sin ellas no podrías navegar, enviar formularios
    o solicitar facturas. <strong>No requieren consentimiento</strong> según la normativa.
  </p>

  <h3>Cookies de terceros</h3>
  <p>
    Algunos servicios externos que utilizamos (Google Maps, Google Fonts) instalan sus propias cookies cuando
    cargas la página. Estos terceros tienen sus propias políticas:
  </p>
  <ul>
    <li><a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Política de privacidad de Google</a></li>
    <li><a href="https://policies.google.com/technologies/cookies" target="_blank" rel="noopener">Cookies de Google</a></li>
  </ul>

  <h2>Gestionar tu consentimiento</h2>
  <p>
    Cuando entras en la web por primera vez te mostramos un banner para que decidas si aceptas o rechazas las cookies
    de terceros. Puedes cambiar tu decisión en cualquier momento haciendo clic en el botón flotante <i class="bi bi-cookie"></i> que aparece
    abajo a la izquierda, o desde aquí mismo:
  </p>

  <div class="cta-config">
    <p>Tu decisión actual: <strong id="estado-cookies">cargando…</strong></p>
    <button class="btn-config" onclick="reabrirCookies()">
      Cambiar mis preferencias
    </button>
  </div>

  <h2>Tus derechos</h2>
  <p>
    Conforme al RGPD y la LSSI, tienes derecho a:
  </p>
  <ul>
    <li>Aceptar o rechazar las cookies no esenciales</li>
    <li>Cambiar tu decisión en cualquier momento</li>
    <li>Solicitar información sobre los datos que recogemos</li>
    <li>Configurar tu navegador para bloquear todas las cookies</li>
  </ul>

  <h2>Configurar el navegador</h2>
  <p>
    Además del consentimiento en la web, puedes gestionar las cookies directamente desde tu navegador:
  </p>
  <ul>
    <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener">Google Chrome</a></li>
    <li><a href="https://support.mozilla.org/es/kb/habilitar-y-deshabilitar-cookies-sitios-web-rastrear-preferencias" target="_blank" rel="noopener">Firefox</a></li>
    <li><a href="https://support.apple.com/es-es/guide/safari/sfri11471/mac" target="_blank" rel="noopener">Safari</a></li>
    <li><a href="https://support.microsoft.com/es-es/microsoft-edge/eliminar-las-cookies-en-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener">Microsoft Edge</a></li>
  </ul>

  <h2>Contacto</h2>
  <p>
    Si tienes cualquier duda sobre esta política o sobre el tratamiento de tus datos, puedes escribirnos a
    <a href="mailto:misswhitneyfacturas@gmail.com">misswhitneyfacturas@gmail.com</a>.
  </p>

  <p style="margin-top:2.5rem;font-size:.82rem;color:#888">
    Esta política puede actualizarse para reflejar cambios en la web o en la normativa. La fecha de la última
    actualización aparece arriba.
  </p>
</section>

<script>
  // Mostrar el estado actual de consentimiento
  document.addEventListener('DOMContentLoaded', () => {
    const consent = localStorage.getItem('mw_cookies_consent');
    const elem = document.getElementById('estado-cookies');
    if (consent === 'accepted') {
      elem.innerHTML = '<span style="color:#2da64a">Aceptadas ✓</span>';
    } else if (consent === 'rejected') {
      elem.innerHTML = '<span style="color:#c53030">Rechazadas ✗</span>';
    } else {
      elem.innerHTML = '<span style="color:#888">Sin decidir</span>';
    }
  });
</script>
@endsection
