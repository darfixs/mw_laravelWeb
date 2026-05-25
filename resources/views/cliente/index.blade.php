@extends('layouts.app')
@section('title','Miss Whitney · Bar & Cocina')

@push('styles')
<style>
  :root {
    --roble-oscuro: #3a251a;
    --roble-medio:  #5b3a26;
    --roble-claro:  #8b6443;
    --crema:        #fbf6ef;
    --crema-osc:    #f0e7d8;
    --burdeos:      #8c1426;
    --burdeos-osc:  #6e0f1c;
    --dorado:       #c89b3c;
    --dorado-osc:   #a07a25;
    --negro-suave:  #1a1a1a;
    --gris-suave:   #5a5a5a;
  }

  body { background: var(--crema); color: var(--negro-suave); overflow-x: hidden }

  /* TEXTURA MADERA */
  .wood-texture {
    position: relative;
    background-color: var(--roble-oscuro);
    background-image:
      repeating-linear-gradient(180deg, rgba(60,30,15,.5) 0 2px, transparent 2px 28px, rgba(40,20,10,.4) 28px 30px, transparent 30px 60px),
      repeating-linear-gradient(180deg, rgba(150,100,50,.13) 0 1px, transparent 1px 11px),
      radial-gradient(ellipse 120px 14px at 25% 35%, rgba(255,200,150,.07), transparent),
      radial-gradient(ellipse 90px 10px at 70% 60%, rgba(255,200,150,.06), transparent);
  }
  .wood-texture::before {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,.25), transparent 30%, transparent 70%, rgba(0,0,0,.35));
    pointer-events: none;
  }

  /* HERO */
  .home-hero {
    position: relative; min-height: 88vh;
    padding: 5rem 1.2rem 4rem;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; isolation: isolate;
  }
  .home-hero::after {
    content: ''; position: absolute; inset: 0;
    background-image: url('https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=1600&q=70');
    background-size: cover; background-position: center;
    opacity: .18; z-index: -1;
  }
  .hero-inner { text-align: center; max-width: 720px; z-index: 1; color: var(--crema) }
  .hero-logo-wrap {
    width: 110px; height: 110px; margin: 0 auto 1.5rem;
    border-radius: 50%; background: var(--crema);
    display: grid; place-items: center;
    box-shadow: 0 12px 40px rgba(0,0,0,.45), inset 0 0 0 4px rgba(200,155,60,.4);
    animation: float 4s ease-in-out infinite;
  }
  .hero-logo-wrap img { width: 70%; height: 70%; object-fit: contain }
  @keyframes float { 0%,100% { transform: translateY(0) } 50% { transform: translateY(-8px) } }
  .hero-eyebrow {
    display: inline-flex; align-items: center; gap: .5rem;
    font-family: Georgia, serif; font-style: italic;
    font-size: .9rem; color: var(--dorado);
    letter-spacing: .2em; text-transform: uppercase; margin-bottom: .8rem;
  }
  .hero-eyebrow::before, .hero-eyebrow::after {
    content: ''; display: inline-block; width: 28px; height: 1px;
    background: var(--dorado); opacity: .6;
  }
  .home-hero h1 {
    font-family: Georgia, 'Times New Roman', serif;
    font-size: clamp(2.4rem, 9vw, 4.6rem); line-height: 1.05;
    margin: 0 0 1.2rem; color: var(--crema); font-weight: 400;
  }
  .home-hero h1 em { color: var(--dorado); font-style: italic }
  .home-hero .sub {
    font-size: clamp(.95rem, 2.5vw, 1.1rem);
    color: rgba(251,246,239,.85);
    max-width: 38ch; margin: 0 auto 2rem; line-height: 1.7;
  }
  .hero-cta { display: flex; gap: .8rem; justify-content: center; flex-wrap: wrap }
  .btn-wood {
    display: inline-flex; align-items: center; gap: .5rem;
    background: var(--burdeos); color: var(--crema);
    text-decoration: none; padding: .95rem 1.8rem;
    border-radius: 999px; font-weight: 600; font-size: .95rem;
    transition: all .25s; box-shadow: 0 8px 24px rgba(140,20,38,.4);
    border: 1px solid rgba(200,155,60,.3);
  }
  .btn-wood:hover { background: var(--burdeos-osc); transform: translateY(-2px); box-shadow: 0 12px 32px rgba(140,20,38,.5) }
  .btn-wood-ghost {
    display: inline-flex; align-items: center; gap: .5rem;
    background: transparent; color: var(--crema);
    text-decoration: none; padding: .95rem 1.8rem;
    border-radius: 999px; font-weight: 600; font-size: .95rem;
    border: 1.5px solid rgba(251,246,239,.4); transition: all .25s;
  }
  .btn-wood-ghost:hover { background: rgba(251,246,239,.1); border-color: var(--dorado); color: var(--dorado) }
  .scroll-hint {
    position: absolute; bottom: 1.5rem; left: 50%; transform: translateX(-50%);
    color: rgba(251,246,239,.4); font-size: .75rem;
    letter-spacing: .15em; text-transform: uppercase;
    animation: bounce 2s ease-in-out infinite;
  }
  @keyframes bounce { 0%,100% { transform: translate(-50%, 0) } 50% { transform: translate(-50%, 6px) } }

  /* ═════ CARRUSEL DE PLATOS (justo tras DESLIZA) ═════ */
  .carrusel-platos {
    padding: 3rem 0 2.5rem;
    background: var(--crema);
    overflow: hidden;
  }
  .carrusel-platos .eyebrow {
    display: flex; align-items: center; justify-content: center; gap: .8rem;
    font-family: Georgia, serif; font-style: italic;
    font-size: .85rem; letter-spacing: .22em;
    color: var(--burdeos); text-transform: uppercase; margin-bottom: .6rem;
  }
  .carrusel-platos .eyebrow::before, .carrusel-platos .eyebrow::after {
    content: ''; width: 32px; height: 1px; background: var(--burdeos); opacity: .5;
  }
  .carrusel-platos h2 {
    text-align: center; font-family: Georgia, serif; font-weight: 400;
    font-size: clamp(1.8rem, 4vw, 2.5rem);
    color: var(--burdeos-osc); margin: 0 0 1.8rem;
  }

  .cp-wrap {
    position: relative; overflow: hidden;
    -webkit-mask-image: linear-gradient(90deg, transparent 0, #000 6%, #000 94%, transparent 100%);
            mask-image: linear-gradient(90deg, transparent 0, #000 6%, #000 94%, transparent 100%);
  }
  .cp-track {
    display: flex; gap: 1.2rem;
    width: max-content;
    will-change: transform;
  }
  .cp-card {
    flex: 0 0 auto;
    width: 260px; height: 340px;
    border-radius: 18px; overflow: hidden;
    position: relative;
    box-shadow: 0 10px 28px rgba(30,15,10,.15);
    background: var(--crema-osc);
    transition: transform .35s ease;
  }
  .cp-card:hover { transform: translateY(-6px) }
  .cp-card img { width: 100%; height: 100%; object-fit: cover; display: block }
  .cp-card .caption {
    position: absolute; inset: auto 0 0 0;
    padding: 1rem .9rem .85rem;
    background: linear-gradient(to top, rgba(30,15,10,.88), rgba(30,15,10,.5) 60%, transparent);
    color: #fff;
  }
  .cp-card .cap-cat {
    font-size: .68rem; letter-spacing: .14em; text-transform: uppercase;
    color: rgba(255,215,120,.95); font-weight: 700; margin-bottom: .2rem;
  }
  .cp-card .cap-name {
    font-family: Georgia, serif; font-style: italic;
    font-size: 1.15rem; font-weight: 600; line-height: 1.15;
    margin: 0 0 .15rem; color: #fff;
  }
  .cp-card .cap-desc { font-size: .76rem; opacity: .9; margin: 0; line-height: 1.35 }
  @media (max-width: 600px) {
    .cp-card { width: 210px; height: 290px }
    .cp-card .cap-name { font-size: 1rem }
  }

  /* ═════ RESEÑAS — Carrusel de 2 filas en sentidos opuestos ═════ */
  .resenas-section {
    padding: 4rem 0 4rem;
    position: relative;
    overflow: hidden;
  }
  .resenas-section .eyebrow {
    display: flex; align-items: center; justify-content: center; gap: .8rem;
    font-family: Georgia, serif; font-style: italic;
    font-size: .85rem; letter-spacing: .22em;
    color: var(--dorado); text-transform: uppercase; margin-bottom: .6rem;
  }
  .resenas-section .eyebrow::before, .resenas-section .eyebrow::after {
    content: ''; width: 32px; height: 1px; background: var(--dorado); opacity: .5;
  }
  .resenas-section h2 {
    text-align: center; font-family: Georgia, serif; font-weight: 400;
    font-size: clamp(2rem, 5vw, 2.8rem); color: var(--crema); margin: 0 0 .4rem;
  }
  .resenas-summary {
    text-align: center; color: rgba(251,246,239,.75); font-size: .92rem;
    margin: 0 0 2.5rem;
  }
  .resenas-summary .big-rating { color: var(--dorado); font-weight: 700; font-size: 1.15rem }

  .res-row-wrap {
    overflow: hidden; padding: .6rem 0;
    -webkit-mask-image: linear-gradient(90deg, transparent 0, #000 5%, #000 95%, transparent 100%);
            mask-image: linear-gradient(90deg, transparent 0, #000 5%, #000 95%, transparent 100%);
  }
  .res-row {
    display: flex; gap: 1rem;
    width: max-content;
    min-width: 200vw;          /* nunca menor que 2x viewport → sin huecos */
    will-change: transform;
  }

  .res-card {
    flex: 0 0 auto;
    width: 320px;
    background: var(--crema);
    border-radius: 14px;
    padding: 1.1rem 1.2rem 1rem;
    box-shadow: 0 6px 20px rgba(0,0,0,.18);
    border: 1px solid rgba(200,155,60,.15);
  }
  .res-card .stars {
    color: var(--dorado); font-size: 1rem; letter-spacing: 2px; margin-bottom: .55rem;
    filter: drop-shadow(0 1px 1px rgba(0,0,0,.08));
  }
  .res-card .text {
    font-family: Georgia, serif; font-size: .9rem; line-height: 1.55;
    color: var(--negro-suave); margin: 0 0 .85rem;
    display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical;
    overflow: hidden;
  }
  .res-card .who {
    display: flex; align-items: center; gap: .55rem;
    padding-top: .6rem; border-top: 1px solid var(--crema-osc);
  }
  .res-card .ava {
    width: 32px; height: 32px; border-radius: 50%;
    background: var(--burdeos); color: var(--crema);
    display: grid; place-items: center;
    font-weight: 700; font-size: .82rem; flex-shrink: 0;
  }
  .res-card .name { font-weight: 600; font-size: .85rem; color: var(--negro-suave); margin: 0 }
  .res-card .ago { font-size: .72rem; color: var(--gris-suave); margin: 0 }
  .res-card .g-badge {
    margin-left: auto; font-size: .68rem; color: var(--gris-suave);
    background: var(--crema-osc); padding: .15rem .45rem; border-radius: 4px;
  }
  @media (max-width: 600px) {
    .res-card { width: 270px; padding: .95rem 1rem }
  }

  .valorar-wrap { text-align: center; margin-top: 2.2rem }
  .btn-valorar {
    display: inline-flex; align-items: center; gap: .5rem;
    background: var(--dorado); color: var(--roble-oscuro);
    padding: .85rem 1.6rem;
    border-radius: 999px;
    font-weight: 700; font-size: .9rem;
    text-decoration: none;
    box-shadow: 0 8px 24px rgba(200,155,60,.3);
    border: 1px solid rgba(200,155,60,.5);
    transition: all .25s;
  }
  .btn-valorar:hover {
    background: var(--dorado-osc); transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(200,155,60,.45);
  }

  /* ═════ CÓMO LLEGAR (al final) ═════ */
  .como-llegar-section {
    background: var(--crema);
    padding: 4rem 1.2rem;
  }
  .como-llegar-inner {
    max-width: 1100px; margin: 0 auto;
    display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;
    align-items: center;
  }
  @media (max-width:860px) {
    .como-llegar-inner { grid-template-columns: 1fr; gap: 1.8rem }
  }
  .como-llegar-text h2 {
    font-family: Georgia, serif; font-weight: 400;
    font-size: clamp(2rem, 4.5vw, 2.8rem);
    color: var(--burdeos-osc); margin: 0 0 1rem;
  }
  .como-llegar-text .direccion {
    color: var(--gris-suave); font-size: 1rem; line-height: 1.6; margin: 0 0 1.6rem;
  }
  .como-llegar-text .direccion strong { color: var(--negro-suave); font-weight: 700 }
  .como-llegar-actions { display: flex; gap: .8rem; flex-wrap: wrap }
  .btn-direcciones, .btn-direcciones-ghost {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .85rem 1.4rem; border-radius: 999px;
    font-weight: 600; font-size: .9rem; text-decoration: none;
    transition: all .25s;
  }
  .btn-direcciones {
    background: var(--burdeos); color: var(--crema);
    box-shadow: 0 8px 24px rgba(140,20,38,.3);
  }
  .btn-direcciones:hover { background: var(--burdeos-osc); transform: translateY(-2px) }
  .btn-direcciones-ghost {
    background: transparent; color: var(--burdeos);
    border: 1.5px solid var(--burdeos);
  }
  .btn-direcciones-ghost:hover { background: var(--burdeos-pale) }

  .mapa-frame {
    border-radius: 18px; overflow: hidden;
    box-shadow: 0 16px 40px rgba(0,0,0,.15);
    aspect-ratio: 4/3;
  }
  .mapa-frame iframe { width: 100%; height: 100%; border: 0; display: block }

  /* ═════ CANVAS HERO ═════ */
  #hero-canvas {
    position: absolute; inset: 0;
    width: 100%; height: 100%;
    pointer-events: none; z-index: 0;
  }

  /* ═════ SECCIÓN VÍDEO ═════ */
  .video-section {
    padding: 4rem 1.2rem;
    background: var(--crema-osc);
    text-align: center;
  }
  .video-section .ev {
    display: inline-flex; align-items: center; gap: .8rem;
    font-family: Georgia, serif; font-style: italic;
    font-size: .85rem; letter-spacing: .22em;
    color: var(--burdeos); text-transform: uppercase; margin-bottom: .5rem;
  }
  .video-section .ev::before, .video-section .ev::after {
    content: ''; width: 32px; height: 1px; background: var(--burdeos); opacity: .5;
  }
  .video-section h2 {
    font-family: Georgia, serif; font-weight: 400;
    font-size: clamp(1.8rem, 4vw, 2.5rem);
    color: var(--burdeos-osc); margin: 0 0 .4rem;
  }
  .video-section .vsub {
    color: var(--gris-suave); font-size: .92rem; margin: 0 0 1.5rem;
  }
  .video-wrap {
    max-width: 900px; margin: 0 auto;
    border-radius: 18px; overflow: hidden;
    box-shadow: 0 20px 50px rgba(30,15,10,.18);
    background: #1a0f0a; aspect-ratio: 16/9;
    position: relative;
  }
  .video-wrap video {
    width: 100%; height: 100%; display: block; object-fit: cover;
  }
  .video-placeholder {
    position: absolute; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    background: linear-gradient(135deg, #2a1a0e, #1a0f0a);
    color: rgba(251,246,239,.65); text-align: center; padding: 2rem;
  }
  .video-placeholder i { font-size: 3rem; margin-bottom: .8rem; color: var(--dorado); }
  .video-caption {
    color: var(--gris-suave); font-size: .85rem;
    margin: .8rem auto 0; max-width: 50ch; font-style: italic;
  }
</style>
@endpush

@section('content')

{{-- ════ HERO ════ --}}
<section class="home-hero wood-texture">
  <canvas id="hero-canvas" aria-hidden="true"></canvas>
  <div class="hero-inner">
    <div class="hero-logo-wrap">
      <img src="{{ asset('images/logo.png') }}" alt="Miss Whitney"/>
    </div>
    <div class="hero-eyebrow">Bar &amp; Cocina familiar</div>
    <h1>Sabores de <em>siempre</em>,<br>ambiente de casa</h1>
    <p class="sub">Un rincón con alma donde cada plato cuenta una historia. Ven a comer, a celebrar, a quedarte un rato más.</p>
    <div class="hero-cta">
      <a class="btn-wood" href="{{ route('reservar') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        Reservar mesa
      </a>
      <a class="btn-wood-ghost" href="{{ route('galeria') }}">Ver platos</a>
    </div>
  </div>
  <div class="scroll-hint">↓ Desliza</div>
</section>

{{-- ════ CARRUSEL DE PLATOS (justo tras DESLIZA) ════ --}}
<section class="carrusel-platos">
  <div class="eyebrow">Nuestra cocina</div>
  <h2>Platos del día</h2>

  <div class="cp-wrap">
    <div class="cp-track" id="cp-track">
      @php
        $platos = [
          ['cat'=>'Para compartir', 'name'=>'Tabla ibérica',    'desc'=>'Quesos curados, jamón y embutidos', 'img'=>'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&w=600&q=75'],
          ['cat'=>'Tapa estrella',  'name'=>'Croquetas caseras','desc'=>'De jamón, bechamel cremosa',       'img'=>'https://images.unsplash.com/photo-1599974579688-8dbdd335c77f?auto=format&fit=crop&w=600&q=75'],
          ['cat'=>'Frío',           'name'=>'Salmorejo',        'desc'=>'Receta tradicional, huevo y jamón','img'=>'https://images.unsplash.com/photo-1633436374961-09b92742047b?auto=format&fit=crop&w=600&q=75'],
          ['cat'=>'Marisco',        'name'=>'Gambas al ajillo', 'desc'=>'Aceite, ajo y guindilla',          'img'=>'https://images.unsplash.com/photo-1559847844-5315695dadae?auto=format&fit=crop&w=600&q=75'],
          ['cat'=>'Principal',      'name'=>'Arroz del día',    'desc'=>'Varía según mercado',              'img'=>'https://images.unsplash.com/photo-1551183053-bf91a1d81141?auto=format&fit=crop&w=600&q=75'],
          ['cat'=>'Postre',         'name'=>'Tarta de queso',   'desc'=>'Horneada al estilo de la casa',    'img'=>'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=600&q=75'],
        ];
      @endphp
      @for($l=0;$l<3;$l++)
        @foreach($platos as $p)
          <article class="cp-card">
            <img src="{{ $p['img'] }}" alt="{{ $p['name'] }}" loading="lazy"/>
            <div class="caption">
              <div class="cap-cat">{{ $p['cat'] }}</div>
              <h3 class="cap-name">{{ $p['name'] }}</h3>
              <p class="cap-desc">{{ $p['desc'] }}</p>
            </div>
          </article>
        @endforeach
      @endfor
    </div>
  </div>
</section>

{{-- ════ VÍDEO AMBIENTE ════ --}}
<section class="video-section">
  <div class="ev">Nuestro rincón</div>
  <h2>El ambiente de Miss Whitney</h2>
  <p class="vsub">Un vistazo a nuestra cocina y el espíritu del local.</p>
  <div class="video-wrap">
    <video
      id="mw-video"
      controls
      muted
      loop
      playsinline
      preload="metadata"
      poster="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=1200&q=70"
      aria-label="Vídeo del restaurante Miss Whitney — ambiente y cocina">
      <source src="{{ asset('video/restaurante.mp4') }}" type="video/mp4">
      <p style="color:rgba(251,246,239,.7);padding:2rem;margin:0">
        Tu navegador no soporta vídeo HTML5.
        <a href="{{ asset('video/restaurante.mp4') }}" style="color:var(--dorado)">Descargar vídeo</a>
      </p>
    </video>
    <div class="video-placeholder" id="video-placeholder">
      <i class="bi bi-play-circle" aria-hidden="true"></i>
      <p style="margin:0 0 .4rem;font-size:1rem">Vídeo de ambiente · Miss Whitney</p>
      <small style="opacity:.55">Sube <code style="background:rgba(255,255,255,.1);padding:.1rem .35rem;border-radius:4px">public/video/restaurante.mp4</code> al servidor</small>
    </div>
  </div>
  <p class="video-caption">Grabado en nuestro local de la Avenida Escultora Miss Whitney, Huelva.</p>
</section>

{{-- ════ RESEÑAS — Carrusel real con datos de Google ════ --}}
<section class="resenas-section wood-texture">
  <div style="position:relative;z-index:1">
    <div class="eyebrow">Voces de nuestros clientes</div>
    <h2>Lo que dicen de nosotros</h2>
    <p class="resenas-summary" id="resenas-summary">
      <span style="opacity:.6">Cargando reseñas&hellip;</span>
    </p>

    {{-- Loading inicial --}}
    <div id="resenas-loading" style="text-align:center;padding:1.5rem 0;color:rgba(251,246,239,.5)">
      <div style="display:inline-block;width:28px;height:28px;border:3px solid rgba(200,155,60,.2);border-top-color:var(--dorado);border-radius:50%;animation:spinR 1s linear infinite"></div>
    </div>

    {{-- Filas (se rellenan vía JS) --}}
    <div id="resenas-rows" style="display:none">
      <div class="res-row-wrap">
        <div class="res-row" id="res-row-a"></div>
      </div>
      <div class="res-row-wrap" style="margin-top:1rem">
        <div class="res-row" id="res-row-b"></div>
      </div>
    </div>

    {{-- Mensaje de error --}}
    <div id="resenas-error" style="display:none;text-align:center;color:rgba(251,246,239,.6);padding:2rem 1rem">
      <p style="margin:0 0 .5rem">No se pudieron cargar las reseñas en este momento.</p>
      <a id="link-google-fallback" class="btn-valorar" style="display:inline-flex;margin-top:.8rem" href="https://www.google.com/maps/place/Nuevo+Restaurante+Bar+Whitney/@37.2538009,-6.9445445,17z" target="_blank" rel="noopener">Ver en Google</a>
    </div>

    {{-- Botón Valorarnos (se muestra cuando carguen las reseñas) --}}
    <div class="valorar-wrap" id="valorar-wrap" style="display:none">
      <a class="btn-valorar" id="btn-valorar" href="#" target="_blank" rel="noopener">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        Valorarnos
      </a>
    </div>
  </div>
</section>

<style>@keyframes spinR{to{transform:rotate(360deg)}}</style>

{{-- ════ CÓMO LLEGAR (al final, antes del footer) ════ --}}
<section class="como-llegar-section">
  <div class="como-llegar-inner">
    <div class="como-llegar-text">
      <div class="eyebrow" style="display:inline-flex;align-items:center;gap:.5rem;font-family:Georgia,serif;font-style:italic;font-size:.85rem;color:var(--burdeos);letter-spacing:.22em;text-transform:uppercase;margin-bottom:.4rem">Visítanos</div>
      <h2>Cómo llegar</h2>
      <p class="direccion">
        <strong>Café Bar Whitney</strong><br>
        Avenida Escultora Miss Whitney, 15<br>
        21003 Huelva
      </p>
      <div class="como-llegar-actions">
        <a class="btn-direcciones" href="https://www.google.com/maps/dir/?api=1&destination=37.2538009,-6.9445445&destination_place_id=ChIJWRmXLD3QEQ0R-r9q51ktegw" target="_blank" rel="noopener">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="3 11 22 2 13 21 11 13 3 11"/>
          </svg>
          Cómo llegar
        </a>
        <a class="btn-direcciones-ghost" href="https://www.google.com/maps/place/Nuevo+Restaurante+Bar+Whitney/@37.2538009,-6.9445445,17z" target="_blank" rel="noopener">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
            <circle cx="12" cy="10" r="3"/>
          </svg>
          Ver en Google Maps
        </a>
      </div>
    </div>
    <div class="mapa-frame">
      <iframe loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"
        src="https://maps.google.com/maps?q=37.2538009,-6.9445445&hl=es&z=16&output=embed"
        title="Ubicación de Miss Whitney"></iframe>
    </div>
  </div>
</section>

@push('scripts')
<script>
(function(){
  // ───── CARRUSEL GENÉRICO ─────
  // direction: -1 = hacia la izquierda, +1 = hacia la derecha
  function rotateTrack(trackId, direction){
    const track = document.getElementById(trackId);
    if(!track) return null;
    let x = 0, setWidth = 0, paused = false;
    const SPEED = 0.5;
    let lastT = performance.now();

    function measure(){
      // Suponemos que el contenido está duplicado x3 → un set = total / 3
      setWidth = track.scrollWidth / 3;
      // Para fila B (sentido derecha) empezamos en -setWidth
      if(direction > 0 && x === 0) x = -setWidth;
    }
    setTimeout(measure, 200);
    setTimeout(measure, 1500);
    window.addEventListener('load', measure);
    window.addEventListener('resize', measure);

    track.addEventListener('mouseenter', () => paused = true);
    track.addEventListener('mouseleave', () => paused = false);

    function tick(now){
      const dt = Math.min(now - lastT, 50);
      lastT = now;
      if(!paused && setWidth > 0){
        x += direction * SPEED * (dt / 16.67);
        if(direction < 0 && x <= -setWidth)  x += setWidth;
        if(direction > 0 && x >=  0)         x -= setWidth;
        track.style.transform = 'translate3d(' + x.toFixed(2) + 'px, 0, 0)';
      }
      requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
    return { remeasure: measure };
  }

  // Arrancar carrusel de platos
  rotateTrack('cp-track', -1);

  // ───── CARGA DE RESEÑAS REALES ─────
  function escapeHtml(s){
    return (s||'').toString()
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;').replace(/\'/g,'&#39;');
  }

  function renderCard(r){
    const stars = '★'.repeat(r.rating || 5) + '☆'.repeat(Math.max(0,5-(r.rating||5)));
    const inicial = (r.autor || 'A').trim().charAt(0).toUpperCase();
    const avatar = r.autor_foto
      ? `<img src="${r.autor_foto}" alt="" loading="lazy" referrerpolicy="no-referrer" onerror="this.replaceWith(Object.assign(document.createElement('span'),{textContent:'${inicial}'}))" style="width:100%;height:100%;object-fit:cover;border-radius:50%">`
      : inicial;
    return `
      <article class="res-card">
        <div class="stars">${stars}</div>
        <p class="text">${escapeHtml(r.texto)}</p>
        <div class="who">
          <div class="ava">${avatar}</div>
          <div>
            <p class="name">${escapeHtml(r.autor)}</p>
            <p class="ago">${escapeHtml(r.tiempo_relativo || '')}</p>
          </div>
          <span class="g-badge">Google</span>
        </div>
      </article>`;
  }

  async function cargarResenas(){
    try {
      const res  = await fetch('{{ route("api.resenas") }}', { headers: { 'Accept': 'application/json' } });
      const data = await res.json();
      if(!data.ok || !Array.isArray(data.resenas) || data.resenas.length === 0){
        throw new Error('sin reseñas');
      }

      // Resumen real
      const summary = document.getElementById('resenas-summary');
      if(data.rating_global && data.total_resenas){
        summary.innerHTML = `<span class="big-rating">★ ${Number(data.rating_global).toFixed(1)}</span> sobre ${data.total_resenas} reseñas verificadas en Google`;
      } else {
        summary.innerHTML = `<span class="big-rating">★ ${Number(data.rating_global||5).toFixed(1)}</span> en Google`;
      }

      // Botón valorarnos → URL directa de "escribir reseña"
      const btn = document.getElementById('btn-valorar');
      btn.href = data.url_escribir || data.url_google || '#';
      document.getElementById('valorar-wrap').style.display = 'block';

      // ─── DISTRIBUCIÓN A/B SIN REPETICIÓN VISUAL ───
      // Con pocas reseñas (5-10) hay que ser inteligente: barajamos y rotamos
      // B con un offset diferente, así A[0] != B[0] en ningún momento.
      const todas = data.resenas.slice();
      if(todas.length === 0){
        document.getElementById('resenas-loading').style.display = 'none';
        document.getElementById('resenas-error').style.display   = 'block';
        return;
      }
      function shuffle(arr){
        const a = arr.slice();
        for(let i=a.length-1;i>0;i--){ const j=Math.floor(Math.random()*(i+1)); [a[i],a[j]]=[a[j],a[i]]; }
        return a;
      }
      // dos shuffles independientes + rotación
      const baseA = shuffle(todas);
      const baseB = shuffle(todas);
      const off   = Math.max(1, Math.floor(baseB.length / 2));
      const setA  = baseA;
      const setB  = baseB.slice(off).concat(baseB.slice(0, off));

      // Calcular cuántas copias necesitamos para llenar el viewport.
      // tarjeta ≈ 320px + gap 16px = 336px.
      // Necesitamos que el track mida AL MENOS 2x el viewport para que el loop
      // funcione sin huecos visibles.
      const TARJETA_PX = 336;
      const viewport   = Math.max(window.innerWidth || 1200, 1200);
      const tarjetasPorSet = todas.length;
      const anchoSet   = tarjetasPorSet * TARJETA_PX;
      // Queremos anchoTotal >= 2 * viewport, y mínimo 3 copias
      const repetir    = Math.max(3, Math.ceil((2 * viewport) / anchoSet));

      const rowA = document.getElementById('res-row-a');
      const rowB = document.getElementById('res-row-b');
      let htmlA = '', htmlB = '';
      for(let i=0; i<repetir; i++){
        setA.forEach(r => htmlA += renderCard(r));
        setB.forEach(r => htmlB += renderCard(r));
      }
      rowA.innerHTML = htmlA;
      rowB.innerHTML = htmlB;

      // Mostrar y arrancar carruseles
      document.getElementById('resenas-loading').style.display = 'none';
      document.getElementById('resenas-rows').style.display    = 'block';
      // El track tiene `repetir` copias del set → setWidth = scrollWidth / repetir
      // Pero rotateTrack divide por 3. Para que funcione bien, hacemos custom:
      setTimeout(() => {
        startCarousel('res-row-a', -1, repetir);
        startCarousel('res-row-b', +1, repetir);
      }, 100);

    } catch (e){
      document.getElementById('resenas-loading').style.display = 'none';
      document.getElementById('resenas-error').style.display   = 'block';
      console.warn('Reseñas:', e);
    }
  }

  // Carrusel con re-medición cuando carguen las imágenes (fix del hueco)
  function startCarousel(trackId, direction, copies){
    const track = document.getElementById(trackId);
    if(!track) return;
    let x = 0, setWidth = 0, paused = false;
    const SPEED = 0.45;
    let lastT = performance.now();
    let firstMeasure = true;

    function measure(){
      const w = track.scrollWidth / copies;
      if(w > 0 && w !== setWidth){
        // Conservar posición proporcional al cambiar el ancho
        if(setWidth > 0 && x !== 0){
          x = (x / setWidth) * w;
        }
        setWidth = w;
        if(direction > 0 && firstMeasure){
          x = -setWidth;
          firstMeasure = false;
        }
      }
    }

    measure();
    setTimeout(measure, 300);
    setTimeout(measure, 1200);
    setTimeout(measure, 2500);
    window.addEventListener('resize', measure);

    // Re-medir cuando termine de cargar cualquier imagen del track
    track.querySelectorAll('img').forEach(img => {
      if(img.complete) return;
      img.addEventListener('load',  measure, { once: true });
      img.addEventListener('error', measure, { once: true });
    });

    track.addEventListener('mouseenter', () => paused = true);
    track.addEventListener('mouseleave', () => paused = false);

    function tick(now){
      const dt = Math.min(now - lastT, 50);
      lastT = now;
      if(!paused && setWidth > 0){
        x += direction * SPEED * (dt / 16.67);
        if(direction < 0 && x <= -setWidth) x += setWidth;
        if(direction > 0 && x >=  0)         x -= setWidth;
        track.style.transform = 'translate3d(' + x.toFixed(2) + 'px, 0, 0)';
      }
      requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }

  cargarResenas();
})();
</script>
@endpush

@endsection

@push('scripts')
<script>
/* ═══ CANVAS HERO — partículas flotantes ═══ */
(function () {
  console.log('[MW Canvas] v4 start');
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    console.log('[MW Canvas] exit: prefers-reduced-motion activo');
    return;
  }
  var canvas = document.getElementById('hero-canvas');
  if (!canvas) { console.log('[MW Canvas] exit: no existe #hero-canvas'); return; }
  var ctx = canvas.getContext('2d');
  var hero = canvas.parentElement;

  console.log('[MW Canvas] canvas encontrado, hero.offsetHeight =', hero ? hero.offsetHeight : 'sin padre');

  function setSize() {
    var w = (hero && hero.offsetWidth  > 0) ? hero.offsetWidth  : window.innerWidth;
    var h = (hero && hero.offsetHeight > 0) ? hero.offsetHeight : Math.round(window.innerHeight * 0.88);
    if (canvas.width !== w || canvas.height !== h) {
      canvas.width  = w;
      canvas.height = h;
      console.log('[MW Canvas] size =', w, h);
    }
  }

  setSize();
  window.addEventListener('load',   setSize);
  window.addEventListener('resize', setSize);
  if (typeof ResizeObserver !== 'undefined') {
    new ResizeObserver(setSize).observe(hero || canvas);
  }

  var COLORS = [
    [200, 155,  60],
    [200, 155,  60],
    [251, 246, 239],
  ];

  var particles = [];
  for (var i = 0; i < 28; i++) {
    var c = COLORS[Math.floor(Math.random() * COLORS.length)];
    particles.push({
      x:     Math.random(),
      y:     Math.random(),
      r:     Math.random() * 2.5 + 1,      /* 1 – 3.5 px */
      speed: Math.random() * 0.18 + 0.06,
      drift: (Math.random() - 0.5) * 0.1,
      phase: Math.random() * Math.PI * 2,
      c:     c,
    });
  }

  var running = true;
  var lastLog = 0;

  function draw(ts) {
    if (!running) return;
    var W = canvas.width, H = canvas.height;
    if (W < 10 || H < 10) { setSize(); requestAnimationFrame(draw); return; }

    if (ts - lastLog > 5000) { console.log('[MW Canvas] frame', W, H); lastLog = ts; }

    ctx.clearRect(0, 0, W, H);
    particles.forEach(function (p) {
      var opacity = 0.6 + 0.35 * (0.5 + 0.5 * Math.sin((ts || 0) * 0.001 + p.phase));
      ctx.beginPath();
      ctx.arc(p.x * W, p.y * H, p.r, 0, Math.PI * 2);
      ctx.fillStyle = 'rgba(' + p.c[0] + ',' + p.c[1] + ',' + p.c[2] + ',' + opacity.toFixed(2) + ')';
      ctx.fill();

      p.y -= p.speed / H * 60;
      p.x += p.drift  / W * 10;

      if (p.y < -0.02) { p.y = 1.02; p.x = Math.random(); }
      if (p.x < 0) p.x = 1;
      if (p.x > 1) p.x = 0;
    });
    requestAnimationFrame(draw);
  }

  requestAnimationFrame(draw);
  document.addEventListener('visibilitychange', function () {
    running = !document.hidden;
    if (running) requestAnimationFrame(draw);
  });
})();

/* ═══ VÍDEO — ocultar placeholder cuando carga ═══ */
(function () {
  var video = document.getElementById('mw-video');
  var placeholder = document.getElementById('video-placeholder');
  if (!video || !placeholder) return;

  function hidePlaceholder() {
    if (video.readyState >= 1) placeholder.style.display = 'none';
  }
  video.addEventListener('loadedmetadata', hidePlaceholder);
  video.addEventListener('canplay', hidePlaceholder);
  hidePlaceholder();
})();
</script>
@endpush
