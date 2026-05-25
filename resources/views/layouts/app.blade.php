<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title','Miss Whitney · Bar &amp; Cocina')</title>
  <meta name="description" content="@yield('description','Miss Whitney — Sabores auténticos, ambiente familiar.')" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="{{ asset('css/shared.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  @stack('styles')
</head>

<body @class(['is-embedded' => request()->boolean('embedded')])>
  <div id="loader" aria-hidden="true">
  <div class="loader-logo">
    <img src="{{ asset('images/logo.png') }}" alt="Miss Whitney" />
  </div>
  <div class="loader-name">Miss Whitney</div>
  <div class="loader-bar-wrap"><div class="loader-bar"></div></div>
</div>
  <div class="cursor-dot" id="cursor"></div>
  <div class="blob-tl" aria-hidden="true"></div>
  <div class="blob-br" aria-hidden="true"></div>

  <nav class="topbar" aria-label="Navegación principal">
    <div class="navgrid">
      <div class="nav-left">
        <a class="navlink @if(request()->routeIs('galeria')) navlink--active @endif" href="{{ route('galeria') }}"
           data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Ver nuestra galería de platos">Galería</a>
        <span class="nav-sep" aria-hidden="true"></span>
        <a class="navlink @if(request()->routeIs('reservar')) navlink--active @endif" href="{{ route('reservar') }}"
           data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Reserva tu mesa online">Reservar</a>
        <span class="nav-sep" aria-hidden="true"></span>
        <a class="navlink @if(request()->routeIs('contacto')) navlink--active @endif" href="{{ route('contacto') }}"
           data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Escríbenos o llámanos">Contacto</a>
      </div>
      <a class="logo-btn" href="{{ route('home') }}" aria-label="Inicio"
         data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Volver al inicio">
        <img src="{{ asset('images/logo.png') }}" alt="Miss Whitney" />
      </a>
      <div class="nav-right">
        <a class="navlink" href="{{ route('contacto') }}#mapa"
           data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Cómo llegar a Miss Whitney">Dónde estamos</a>
        <span class="nav-sep" aria-hidden="true"></span>
        <a class="navlink @if(request()->routeIs('solicitar.factura')) navlink--active @endif navlink--factura" href="{{ route('solicitar.factura') }}"
           data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Solicita tu factura del ticket">Solicitar factura</a>
      </div>
    </div>
  </nav>
  {{-- ═══ HEADER MÓVIL ═══ --}}
  <header class="mob-header" aria-label="Miss Whitney">
    <div class="mob-header__inner">
      <a href="{{ route('home') }}" class="mob-header__logo" aria-label="Inicio">
        <img src="{{ asset('images/logo.png') }}" alt="MW" />
      </a>
      <div class="mob-header__texts">
        <span class="mob-header__name">Miss Whitney</span>
        <span class="mob-header__sub">Bar &amp; Cocina · Huelva</span>
      </div>
    </div>
  </header>

  {{-- ═══ DOCK INFERIOR ═══ --}}
  <div class="dock-wrap">
    <nav class="dock" aria-label="Navegación rápida">

      {{-- 1 · Galería --}}
      <a href="{{ route('galeria') }}" @class(['dock__item', 'dock__item--active' => request()->routeIs('galeria')])>
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
          <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
        </svg>
        Galería
      </a>

      {{-- 2 · Reservar --}}
      <a href="{{ route('reservar') }}" @class(['dock__item', 'dock__item--active' => request()->routeIs('reservar')])>
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
        </svg>
        Reservar
      </a>

      {{-- 3 · Casita (placeholder en grid + botón absoluto encima) --}}
      <div class="dock__home-slot" aria-hidden="true"></div>
      <a href="{{ route('home') }}" class="dock__home" aria-label="Inicio">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 10.5L12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M9.5 21V14.5h5V21"/>
        </svg>
      </a>

      {{-- 4 · Factura --}}
      <a href="{{ route('solicitar.factura') }}" @class(['dock__item', 'dock__item--active' => request()->routeIs('solicitar.factura')])>
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
        </svg>
        Factura
      </a>

      {{-- 5 · Contacto --}}
      <a href="{{ route('contacto') }}" @class(['dock__item', 'dock__item--active' => request()->routeIs('contacto')])>
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        Contacto
      </a>

    </nav>
  </div>

  @yield('content')

  <footer class="mw-footer">
    <div class="mw-footer__inner">

      {{-- Bloque marca --}}
      <div class="mw-footer__brand">
        <div class="mw-footer__logo">
          <img src="{{ asset('images/logo.png') }}" alt="Miss Whitney"/>
        </div>
        <div>
          <h3 class="mw-footer__name">Miss Whitney</h3>
          <p class="mw-footer__tag">Bar &amp; Cocina · Huelva</p>
        </div>
      </div>

      {{-- Navegación --}}
      <div class="mw-footer__col">
        <h4>Web</h4>
        <ul>
          <li><a href="{{ route('home') }}">Inicio</a></li>
          <li><a href="{{ route('galeria') }}">Galería</a></li>
          <li><a href="{{ route('reservar') }}">Reservar</a></li>
          <li><a href="{{ route('contacto') }}">Contacto</a></li>
          <li><a href="{{ route('solicitar.factura') }}">Solicitar factura</a></li>
        </ul>
      </div>

      {{-- Contacto --}}
      <div class="mw-footer__col">
        <h4>Contacto</h4>
        <ul>
          <li><a href="tel:+34959254960">959 254 960</a></li>
          <li><a href="mailto:misswhitneyfacturas@gmail.com">misswhitneyfacturas@gmail.com</a></li>
          <li>Avenida Escultora Miss Whitney, 15</li>
          <li>21003 Huelva</li>
        </ul>
      </div>

      {{-- Horario --}}
      <div class="mw-footer__col">
        <h4>Horario</h4>
        <ul>
          <li>Lunes – Viernes · 08:00 – 17:00</li>
          <li>Sábado · 07:00 – 16:00</li>
          <li>Domingo · cerrado</li>
        </ul>
      </div>

      {{-- Redes --}}
      <div class="mw-footer__col">
        <h4>Síguenos</h4>
        <div class="mw-footer__social">
          <a href="https://www.instagram.com/restaurantemisswhitney/" target="_blank" rel="noopener" aria-label="Instagram">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
            </svg>
          </a>
          <a href="https://www.facebook.com/profile.php?id=61583563838133&locale=es_ES" target="_blank" rel="noopener" aria-label="Facebook">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.99 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.99 22 12z"/></svg>
          </a>
          <a href="https://www.google.com/maps/place/Nuevo+Restaurante+Bar+Whitney/@37.2538009,-6.9445445,17z" target="_blank" rel="noopener" aria-label="Google Maps">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
            </svg>
          </a>
        </div>
      </div>

    </div>

    {{-- Línea legal --}}
    <div class="mw-footer__legal">
      © <span id="yr"></span> Miss Whitney · Todos los derechos reservados
      <span class="dot">·</span>
      <a href="{{ route('politica.cookies') }}">Política de cookies</a>
      <span class="dot">·</span>
      <button type="button" onclick="reabrirCookies()" class="mw-footer__cookie-btn">Gestionar cookies</button>
    </div>
  </footer>

  {{-- ═══════════ MODAL OVERLAY GLOBAL (Reservar / Factura) ═══════════ --}}
  <div id="mw-modal" class="mw-modal" aria-hidden="true">
    <div class="mw-modal__backdrop" data-modal-close></div>
    <div class="mw-modal__panel" role="dialog" aria-modal="true" aria-labelledby="mw-modal-title">
      <div class="mw-modal__head">
        <h2 id="mw-modal-title" class="mw-modal__title">Cargando…</h2>
        <button class="mw-modal__close" data-modal-close aria-label="Cerrar">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>
      <div class="mw-modal__body">
        <iframe id="mw-modal-iframe" title="Contenido" src="about:blank"></iframe>
      </div>
    </div>
  </div>

  <style>
    .mw-modal {
      position: fixed; inset: 0; z-index: 9500;
      display: none;
      align-items: center; justify-content: center;
      padding: clamp(0px, 2vw, 24px);
    }
    .mw-modal.is-open { display: flex; animation: mwModalIn .25s ease }
    @keyframes mwModalIn { from { opacity: 0 } to { opacity: 1 } }

    .mw-modal__backdrop {
      position: absolute; inset: 0;
      background: rgba(20,10,10,.6);
      backdrop-filter: blur(6px);
    }
    .mw-modal__panel {
      position: relative;
      width: min(900px, 100%);
      height: min(86vh, 900px);
      background: var(--crema, #fbf6ef);
      border-radius: 22px;
      box-shadow: 0 30px 80px rgba(0,0,0,.5);
      overflow: hidden;
      display: flex; flex-direction: column;
      animation: mwModalPanelIn .35s cubic-bezier(.22,1,.36,1);
    }
    @keyframes mwModalPanelIn {
      from { transform: translateY(30px) scale(.97); opacity: 0 }
      to   { transform: translateY(0)    scale(1);   opacity: 1 }
    }
    .mw-modal__head {
      display: flex; align-items: center; justify-content: space-between;
      padding: .9rem 1.2rem;
      background: linear-gradient(180deg, rgba(155,26,46,.95), rgba(110,15,28,.95));
      color: #fff;
      border-bottom: 1.5px solid rgba(200,155,60,.3);
      flex-shrink: 0;
    }
    .mw-modal__title {
      font-family: var(--font-display, Georgia, serif);
      font-style: italic; font-weight: 600;
      font-size: 1.4rem;
      color: #fff;
      margin: 0;
    }
    .mw-modal__close {
      width: 36px; height: 36px;
      border-radius: 50%;
      background: rgba(255,255,255,.15);
      border: 1px solid rgba(255,255,255,.2);
      color: #fff;
      cursor: pointer;
      display: grid; place-items: center;
      transition: background .18s, transform .18s;
    }
    .mw-modal__close:hover { background: rgba(255,255,255,.25); transform: rotate(90deg) }

    .mw-modal__body {
      flex: 1; background: var(--crema, #fbf6ef);
      overflow: hidden;
    }
    .mw-modal__body iframe {
      width: 100%; height: 100%;
      border: 0; display: block;
      background: var(--crema, #fbf6ef);
    }

    @media (max-width: 600px) {
      .mw-modal { padding: 10px }
      .mw-modal__panel {
        height: calc(100vh - 20px);
        height: calc(100dvh - 20px);
        max-height: none;
        width: 100%;
        border-radius: 18px;
      }
      .mw-modal__head {
        padding: .7rem 1rem;
      }
      .mw-modal__title {
        font-size: 1.15rem;
      }
      .mw-modal__close {
        width: 32px; height: 32px;
      }
    }
    @media (max-width: 400px) {
      .mw-modal { padding: 6px }
      .mw-modal__panel {
        height: calc(100vh - 12px);
        height: calc(100dvh - 12px);
        border-radius: 14px;
      }
    }

    /* Cuando estamos en una página cargada dentro del modal (iframe), ocultamos el header móvil y el dock */
    body.is-embedded .mob-header,
    body.is-embedded .dock-wrap,
    body.is-embedded .topbar,
    body.is-embedded .site-footer,
    body.is-embedded #cookie-reopen-tab,
    body.is-embedded #cookie-modal { display: none !important }
    body.is-embedded .page-wrap {
      padding-top: 1.5rem !important;
      padding-bottom: 2rem !important;
    }
  </style>

  <script>
    (function(){
      const modal   = document.getElementById('mw-modal');
      const iframe  = document.getElementById('mw-modal-iframe');
      const titleEl = document.getElementById('mw-modal-title');

      window.mwOpenModal = function(url, title){
        titleEl.textContent = title || '';
        // Añadimos ?embedded=1 a la url para que la página sepa que se carga en modal
        const sep = url.indexOf('?') === -1 ? '?' : '&';
        iframe.src = url + sep + 'embedded=1';
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.documentElement.style.overflow = 'hidden';
      };

      window.mwCloseModal = function(){
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        iframe.src = 'about:blank';
        document.documentElement.style.overflow = '';
      };

      modal.querySelectorAll('[data-modal-close]').forEach(el => {
        el.addEventListener('click', window.mwCloseModal);
      });

      document.addEventListener('keydown', e => {
        if(e.key === 'Escape' && modal.classList.contains('is-open')) window.mwCloseModal();
      });

      // Interceptar todos los <a> que apunten a /reservar o /solicitar-factura
      document.addEventListener('click', function(e){
        const a = e.target.closest('a');
        if(!a) return;
        const href = a.getAttribute('href') || '';
        if(!href) return;
        if(/\/reservar(\/|$|\?)/.test(href) || a.dataset.modal === 'reservar'){
          e.preventDefault();
          window.mwOpenModal(href, 'Reservar mesa');
        } else if(/\/solicitar-factura(\/|$|\?)/.test(href) || a.dataset.modal === 'factura'){
          e.preventDefault();
          window.mwOpenModal(href, 'Solicitar factura');
        }
      });

      // Si la propia página está cargada dentro de iframe, marca embedded y avisa al padre cuando se completa
      if(window.self !== window.top){
        document.documentElement.classList.add('embedded');
        document.body && document.body.classList.add('is-embedded');
      }
    })();
  </script>

  {{-- ═══════════ BANNER COOKIES (RGPD) ═══════════ --}}
  <div id="cookie-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(20,15,10,.65);backdrop-filter:blur(5px);align-items:center;justify-content:center;padding:1rem;animation:cookieFadeIn .3s ease">
    <div style="background:#fbf6ef;border-radius:18px;max-width:540px;width:100%;padding:2rem 1.8rem 1.8rem;box-shadow:0 20px 60px rgba(0,0,0,.45);position:relative;animation:cookieSlideUp .35s cubic-bezier(.22,1,.36,1);max-height:90vh;overflow-y:auto">

      {{-- VISTA PRINCIPAL --}}
      <div id="cookie-view-main">
        <div style="font-size:2.5rem;text-align:center;margin-bottom:.5rem"><i class="bi bi-cookie" style="color:#8c1426"></i></div>
        <h3 style="margin:0 0 .8rem;text-align:center;font-family:Georgia,serif;font-size:1.5rem;color:#3a251a;font-weight:400">Tu privacidad importa</h3>
        <p style="margin:0 0 .6rem;font-size:.92rem;line-height:1.6;color:#5a5a5a;text-align:center">
          Usamos cookies propias y de Google (Maps, fuentes) para que la web funcione y mejore tu experiencia.
        </p>
        <p style="margin:0 0 1.6rem;font-size:.82rem;line-height:1.5;color:#888;text-align:center">
          Elige qué cookies aceptas. Puedes cambiar tu decisión en cualquier momento.
          <a href="{{ route('politica.cookies') }}" style="color:#8c1426;font-weight:500">Más información</a>
        </p>
        <div style="display:flex;gap:.7rem;flex-direction:column">
          <button onclick="aceptarCookies()" style="background:#8c1426;color:#fbf6ef;border:none;padding:.95rem 1.2rem;border-radius:12px;font-weight:600;font-size:.95rem;cursor:pointer;transition:all .2s;box-shadow:0 6px 18px rgba(140,20,38,.3)">
            ✓ Aceptar todas
          </button>
          <button onclick="rechazarCookies()" style="background:transparent;color:#5a5a5a;border:1.5px solid #d8c8c8;padding:.95rem 1.2rem;border-radius:12px;font-weight:600;font-size:.95rem;cursor:pointer;transition:all .2s">
            Rechazar todas
          </button>
          <button onclick="mostrarConfig()" style="background:transparent;color:#5b3a26;border:none;padding:.6rem;font-weight:500;font-size:.88rem;cursor:pointer;text-decoration:underline;text-underline-offset:3px">
            <i class="bi bi-gear-fill"></i> Configurar
          </button>
        </div>
      </div>

      {{-- VISTA CONFIGURACIÓN --}}
      <div id="cookie-view-config" style="display:none">
        <button onclick="volverPrincipal()" style="background:none;border:none;color:#5b3a26;cursor:pointer;font-size:.85rem;padding:0;margin-bottom:1rem;display:flex;align-items:center;gap:.3rem">
          ← Volver
        </button>
        <h3 style="margin:0 0 1.4rem;font-family:Georgia,serif;font-size:1.4rem;color:#3a251a;font-weight:400">Configurar cookies</h3>

        {{-- Necesarias (siempre activas) --}}
        <div style="background:#f0e7d8;border-radius:10px;padding:1rem 1.1rem;margin-bottom:.7rem">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.4rem">
            <strong style="font-size:.95rem;color:#3a251a">Necesarias</strong>
            <span style="background:rgba(46,160,67,.18);color:#2da64a;padding:.18rem .6rem;border-radius:999px;font-size:.72rem;font-weight:600">Siempre activas</span>
          </div>
          <p style="margin:0;font-size:.82rem;color:#5a5a5a;line-height:1.5">
            Imprescindibles para que la web funcione: sesión, seguridad CSRF, consentimiento de cookies.
          </p>
        </div>

        {{-- Funcionales: Google Maps --}}
        <div style="background:#fbf6ef;border:1px solid #efe6e6;border-radius:10px;padding:1rem 1.1rem;margin-bottom:.7rem">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.4rem">
            <strong style="font-size:.95rem;color:#3a251a">Funcionales · Google Maps</strong>
            <label class="cookie-switch">
              <input type="checkbox" id="conf-maps" checked/>
              <span class="cookie-slider"></span>
            </label>
          </div>
          <p style="margin:0;font-size:.82rem;color:#5a5a5a;line-height:1.5">
            Para mostrar el mapa interactivo en la sección "Cómo llegar".
          </p>
        </div>

        {{-- Tipografías: Google Fonts --}}
        <div style="background:#fbf6ef;border:1px solid #efe6e6;border-radius:10px;padding:1rem 1.1rem;margin-bottom:1.4rem">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.4rem">
            <strong style="font-size:.95rem;color:#3a251a">Tipografías · Google Fonts</strong>
            <label class="cookie-switch">
              <input type="checkbox" id="conf-fonts" checked/>
              <span class="cookie-slider"></span>
            </label>
          </div>
          <p style="margin:0;font-size:.82rem;color:#5a5a5a;line-height:1.5">
            Para cargar las fuentes elegantes de la web. Sin ellas se ven en tipografías del sistema.
          </p>
        </div>

        <div style="display:flex;gap:.7rem;flex-direction:column">
          <button onclick="guardarConfig()" style="background:#8c1426;color:#fbf6ef;border:none;padding:.85rem 1.2rem;border-radius:12px;font-weight:600;font-size:.92rem;cursor:pointer;box-shadow:0 6px 18px rgba(140,20,38,.3)">
            Guardar preferencias
          </button>
          <button onclick="aceptarCookies()" style="background:transparent;color:#5b3a26;border:1.5px solid #d8c8c8;padding:.75rem 1.2rem;border-radius:12px;font-weight:600;font-size:.88rem;cursor:pointer">
            Aceptar todas
          </button>
        </div>
      </div>

    </div>
  </div>

  {{-- Pestaña lateral oculta para reabrir cookies --}}
  <button id="cookie-reopen-tab" onclick="reabrirCookies()" aria-label="Configurar cookies" title="Configurar cookies">
    <i class="bi bi-cookie"></i>
  </button>

  <style>
    @keyframes cookieFadeIn { from { opacity: 0 } to { opacity: 1 } }
    @keyframes cookieSlideUp { from { transform: translateY(20px); opacity: 0 } to { transform: translateY(0); opacity: 1 } }
    #cookie-modal.show { display: flex !important }

    /* PESTAÑA LATERAL oculta */
    #cookie-reopen-tab {
      display: none;
      position: fixed;
      top: 50%;
      left: -42px;
      transform: translateY(-50%);
      width: 58px;
      height: 44px;
      padding: 0 8px 0 14px;
      background: #fbf6ef;
      border: 1px solid #d8c8c8;
      border-left: none;
      border-radius: 0 22px 22px 0;
      cursor: pointer;
      box-shadow: 4px 4px 14px rgba(0,0,0,.15);
      transition: left .35s cubic-bezier(.22,1,.36,1);
      z-index: 9998;
      align-items: center;
      justify-content: flex-end;
    }
    #cookie-reopen-tab:hover { left: 0 }
    #cookie-reopen-tab.visible { display: flex !important }

    /* En móvil ponerlo abajo a la izquierda (encima de la nav) */
    @media (max-width: 860px) {
      #cookie-reopen-tab {
        top: auto;
        bottom: 5.5rem;
        transform: none;
        left: -40px;
      }
      #cookie-reopen-tab:hover { left: 0 }
    }

    /* Switch estilo iOS */
    .cookie-switch {
      position: relative;
      display: inline-block;
      width: 42px;
      height: 24px;
      flex-shrink: 0;
    }
    .cookie-switch input { opacity: 0; width: 0; height: 0 }
    .cookie-slider {
      position: absolute; inset: 0;
      background: #d8c8c8;
      border-radius: 24px;
      cursor: pointer;
      transition: background .25s;
    }
    .cookie-slider::before {
      content: '';
      position: absolute;
      top: 2px; left: 2px;
      width: 20px; height: 20px;
      background: #fbf6ef;
      border-radius: 50%;
      transition: transform .25s;
      box-shadow: 0 1px 3px rgba(0,0,0,.2);
    }
    .cookie-switch input:checked + .cookie-slider { background: #8c1426 }
    .cookie-switch input:checked + .cookie-slider::before { transform: translateX(18px) }
  </style>

  <script>
    (function() {
      const KEY = 'mw_cookies_consent';
      const modal = document.getElementById('cookie-modal');
      const reopenTab = document.getElementById('cookie-reopen-tab');

      function leerConsent() {
        const raw = localStorage.getItem(KEY);
        if (!raw) return null;
        // Compatibilidad con versión antigua
        if (raw === 'accepted') return { maps: true, fonts: true };
        if (raw === 'rejected') return { maps: false, fonts: false };
        try { return JSON.parse(raw) } catch(e) { return null }
      }

      function guardarConsent(obj) {
        localStorage.setItem(KEY, JSON.stringify({ ...obj, ts: Date.now() }));
      }

      const consent = leerConsent();

      // Si no hay decisión previa, mostrar el modal
      if (!consent) {
        modal.classList.add('show');
      } else {
        // Si ya decidió, mostrar la pestaña lateral
        reopenTab.classList.add('visible');
        if (!consent.maps) aplicarRechazoMaps();
      }

      window.aceptarCookies = function() {
        guardarConsent({ maps: true, fonts: true });
        modal.classList.remove('show');
        reopenTab.classList.add('visible');
        // Si había rechazado el mapa y ahora acepta, recargar para que cargue
        if (document.querySelector('.mapa-placeholder')) window.location.reload();
      };

      window.rechazarCookies = function() {
        guardarConsent({ maps: false, fonts: false });
        modal.classList.remove('show');
        reopenTab.classList.add('visible');
        aplicarRechazoMaps();
      };

      window.mostrarConfig = function() {
        document.getElementById('cookie-view-main').style.display = 'none';
        document.getElementById('cookie-view-config').style.display = 'block';
        const c = leerConsent() || { maps: true, fonts: true };
        document.getElementById('conf-maps').checked  = c.maps;
        document.getElementById('conf-fonts').checked = c.fonts;
      };

      window.volverPrincipal = function() {
        document.getElementById('cookie-view-main').style.display = 'block';
        document.getElementById('cookie-view-config').style.display = 'none';
      };

      window.guardarConfig = function() {
        const obj = {
          maps:  document.getElementById('conf-maps').checked,
          fonts: document.getElementById('conf-fonts').checked,
        };
        guardarConsent(obj);
        modal.classList.remove('show');
        reopenTab.classList.add('visible');
        if (!obj.maps) {
          aplicarRechazoMaps();
        } else if (document.querySelector('.mapa-placeholder')) {
          // Si activó el mapa y antes estaba bloqueado, recargar
          window.location.reload();
        }
      };

      window.reabrirCookies = function() {
        volverPrincipal();
        modal.classList.add('show');
      };

      function aplicarRechazoMaps() {
        document.querySelectorAll('iframe[src*="google.com/maps"], iframe[src*="maps.google.com"]').forEach(iframe => {
          const reemplazo = document.createElement('div');
          reemplazo.className = 'mapa-placeholder';
          reemplazo.style.cssText = 'width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#f0e7d8;color:#5a5a5a;padding:1.5rem;text-align:center;font-size:.9rem;line-height:1.6';
          reemplazo.innerHTML = '<div style="font-size:2rem;margin-bottom:.5rem"><i class="bi bi-geo-alt-fill" style="color:#8c1426"></i></div><p style="margin:0 0 1rem;max-width:36ch">El mapa interactivo está desactivado porque rechazaste las cookies de Google.</p><a href="https://www.google.com/maps/place/Nuevo+Restaurante+Bar+Whitney/@37.2538009,-6.9445445,17z" target="_blank" rel="noopener" style="background:#8c1426;color:#fbf6ef;padding:.6rem 1.2rem;border-radius:8px;text-decoration:none;font-weight:600;font-size:.85rem">Abrir en Google Maps</a>';
          iframe.parentNode.replaceChild(reemplazo, iframe);
        });
      }
    })();
  </script>

  <script>
    document.getElementById('yr').textContent = new Date().getFullYear();
    window.addEventListener('load', () => {
      const l = document.getElementById('loader');
      if (l) {
        l.classList.add('fade');
        setTimeout(() => l.remove(), 600)
      }
    });
    if (!window.matchMedia('(prefers-reduced-motion:reduce)').matches && matchMedia('(pointer:fine)').matches) {
      const dot = document.querySelector('.cursor-dot');
      let x = innerWidth / 2,
        y = innerHeight / 2,
        tx = x,
        ty = y;
      document.addEventListener('mousemove', e => {
        tx = e.clientX;
        ty = e.clientY
      });
      (function r() {
        x += (tx - x) * .18;
        y += (ty - y) * .18;
        dot.style.left = x + 'px';
        dot.style.top = y + 'px';
        requestAnimationFrame(r)
      })();
      document.addEventListener('mousedown', () => {
        dot.style.width = '16px';
        dot.style.height = '16px'
      });
      document.addEventListener('mouseup', () => {
        dot.style.width = '9px';
        dot.style.height = '9px'
      });
    }
  </script>
  {{-- Dock scroll behavior --}}
  <script>
  (function(){
    var dock = document.querySelector('.dock-wrap');
    if(!dock) return;
    var lastY = 0, scrolling = false, timer = null;

    function onTop(){ return window.scrollY < 60; }

    function show(){
      dock.classList.add('dock--visible');
      dock.classList.remove('dock--top');
    }
    function hide(){
      if(onTop()){
        dock.classList.add('dock--top');
        dock.classList.remove('dock--visible');
      } else {
        dock.classList.remove('dock--visible');
        dock.classList.remove('dock--top');
      }
    }

    // Al inicio: visible si estamos arriba
    if(onTop()) dock.classList.add('dock--top');

    window.addEventListener('scroll', function(){
      var y = window.scrollY;
      clearTimeout(timer);

      if(y > lastY && y > 80){
        // Scrolleando hacia abajo — mostrar barra
        show();
      } else if(y < lastY){
        // Scrolleando hacia arriba — también mostrar
        show();
      }

      lastY = y;

      // Cuando para de scrollear: ocultar tras 1.4s
      timer = setTimeout(function(){
        hide();
      }, 1400);
    }, { passive: true });
  })();
  </script>

  {{-- Scroll reveal global (IntersectionObserver) --}}
  <script>
  (function(){
    if(!('IntersectionObserver' in window)) return;

    // 1) Marcar automáticamente elementos que deben revelarse
    //    Cards, info-card, contact-method, info-row, howto-step, plato-card, social-placeholder, hours-table, mapa, etc.
    function tagAuto(){
      const selectors = [
        '.contact-card', '.msg-card', '.info-card', '.factura-card',
        '.reserva-card', '.res-card', '.glass-card',
        '.map-wrap', '.map-section',
        '.section-title', '.section-sub', '.section-label',
        '.platos-section', '.plato-card',
        '.social-placeholder',
        '.hours-table', '.contact-method',
        '.info-row', '.howto-step', '.summary-row',
        '.feature-card', '.testimonial-card', '.testimonial',
        '.steps-grid > *',
      ];
      document.querySelectorAll(selectors.join(',')).forEach(el => {
        if(!el.classList.contains('reveal') &&
           !el.classList.contains('reveal--up') &&
           !el.classList.contains('reveal--left') &&
           !el.classList.contains('reveal--right') &&
           !el.classList.contains('reveal--scale') &&
           !el.classList.contains('reveal--fade')){
          el.classList.add('reveal--up');
        }
      });

      // Grids con varios hijos → stagger
      const grids = document.querySelectorAll('.contact-method-list, .platos-grid, .gallery-grid, .cards-grid, .feature-grid');
      grids.forEach(g => g.classList.add('stagger'));
    }

    // 2) Observer
    const io = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if(e.isIntersecting){
          e.target.classList.add('is-in');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    function observeAll(){
      document.querySelectorAll(
        '.reveal, .reveal--up, .reveal--left, .reveal--right, .reveal--scale, .reveal--fade, .stagger'
      ).forEach(el => io.observe(el));
    }

    function init(){
      tagAuto();
      observeAll();
    }

    if(document.readyState === 'loading'){
      document.addEventListener('DOMContentLoaded', init);
    } else { init(); }
  })();
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el);
      });
    });
  </script>
  @stack('scripts')
</body>

</html>