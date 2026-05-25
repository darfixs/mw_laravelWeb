@extends('layouts.app')
@section('title','Galería · Miss Whitney')

@push('styles')
<style>
  :root {
    --roble-oscuro: #3a251a;
    --roble-medio:  #5b3a26;
    --crema-x:      #fbf6ef;
    --dorado-x:     #c89b3c;
  }

  /* ─── HERO galería ─── */
  .gal-hero {
    text-align: center;
    padding: 1.5rem 1.2rem 1rem;
  }
  .gal-hero .eyebrow {
    display: inline-flex; align-items: center; gap: .8rem;
    font-family: var(--font-display); font-style: italic;
    font-size: .85rem; letter-spacing: .22em;
    color: var(--burdeos);
    text-transform: uppercase; margin-bottom: .6rem;
  }
  .gal-hero .eyebrow::before, .gal-hero .eyebrow::after {
    content: ''; width: 32px; height: 1px;
    background: var(--burdeos); opacity: .5;
  }
  .gal-hero h1 {
    font-family: var(--font-display); font-style: italic; font-weight: 400;
    font-size: clamp(2rem, 5vw, 3rem); color: var(--burdeos-osc);
    margin: 0 0 .3rem;
  }
  .gal-hero p {
    color: var(--gris); font-size: .95rem; margin: 0;
    max-width: 50ch; margin-inline: auto;
  }

  /* ─── PLATOS DEL DÍA ─── */
  .platos-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.2rem;
    margin: 2rem auto 2.5rem;
    max-width: 1000px;
  }
  @media (max-width: 800px) { .platos-grid { grid-template-columns: 1fr 1fr; gap: .9rem } }
  @media (max-width: 500px) { .platos-grid { grid-template-columns: 1fr; gap: .9rem } }

  .plato-card {
    position: relative;
    aspect-ratio: 3/4;
    border-radius: 18px; overflow: hidden;
    box-shadow: 0 10px 30px rgba(30,15,10,.14);
    transition: transform .4s cubic-bezier(.22,1,.36,1), box-shadow .4s ease;
    cursor: pointer;
    background: #f0e7d8;
  }
  .plato-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 22px 48px rgba(30,15,10,.22);
  }
  .plato-card img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .65s ease;
    display: block;
  }
  .plato-card:hover img { transform: scale(1.08) }

  .plato-card .caption {
    position: absolute; inset: auto 0 0 0;
    padding: 1.1rem 1rem .95rem;
    background: linear-gradient(to top,
      rgba(30,15,10,.92) 0%,
      rgba(30,15,10,.55) 55%,
      transparent 100%);
    color: #fff;
  }
  .plato-cat {
    font-size: .68rem; letter-spacing: .14em; text-transform: uppercase;
    color: rgba(255,215,120,.95); font-weight: 700; margin-bottom: .25rem;
  }
  .plato-name {
    font-family: var(--font-display); font-style: italic;
    font-size: 1.25rem; font-weight: 600; line-height: 1.15;
    margin: 0 0 .2rem; color: #fff;
  }
  .plato-desc { font-size: .8rem; opacity: .9; margin: 0; line-height: 1.4 }

  .plato-featured {
    grid-column: span 2;
    aspect-ratio: 16/10;
  }
  @media (max-width: 500px) {
    .plato-featured { grid-column: span 1; aspect-ratio: 3/4 }
  }
  .plato-featured .plato-name { font-size: 1.6rem }

  /* ─── PLACEHOLDER REDES ─── */
  .social-placeholder {
    margin: 1rem auto 2rem;
    max-width: 700px;
    padding: 2.2rem 1.4rem;
    background: linear-gradient(135deg, var(--burdeos-pale) 0%, #fdf4f6 100%);
    border: 1.5px dashed rgba(155,26,46,.25);
    border-radius: 22px;
    text-align: center;
    position: relative; overflow: hidden;
  }
  .social-icons {
    display: flex; justify-content: center; gap: .8rem; margin-bottom: 1rem;
  }
  .social-icons span {
    width: 46px; height: 46px; border-radius: 14px;
    display: grid; place-items: center;
    background: #fff; box-shadow: 0 6px 18px rgba(155,26,46,.15);
    overflow: hidden;
    transition: transform .35s ease;
  }
  .social-icons span svg { width: 28px; height: 28px; display: block }
  .social-icons span:hover { transform: translateY(-3px) rotate(-4deg) }
  .social-placeholder h2 {
    font-family: var(--font-display); font-style: italic;
    font-size: 1.5rem; color: var(--burdeos-osc); margin: 0 0 .5rem;
  }
  .social-placeholder p {
    color: var(--marron); font-size: .9rem; line-height: 1.55;
    margin: 0 auto .6rem; max-width: 50ch;
  }
  .social-eta {
    display: inline-flex; align-items: center; gap: .5rem;
    background: #fff; border: 1px solid rgba(155,26,46,.18);
    border-radius: 999px; padding: .38rem .95rem;
    font-size: .78rem; font-weight: 600; color: var(--burdeos);
  }
  .social-eta::before {
    content: ''; width: 8px; height: 8px; border-radius: 50%;
    background: var(--burdeos);
    animation: socialDot 1.4s ease-in-out infinite;
  }
  @keyframes socialDot {
    0%,100% { opacity: 1; transform: scale(1) }
    50%     { opacity: .4; transform: scale(.85) }
  }

  /* ─── Bootstrap nav-pills · filtro galería ─── */
  .nav-pills .nav-link {
    color: var(--roble-medio);
    font-weight: 600;
    font-size: .85rem;
    border: 1.5px solid rgba(155,26,46,.2);
    background: rgba(255,255,255,.8);
    border-radius: 999px;
    white-space: nowrap;
    transition: all .22s ease;
  }
  .nav-pills .nav-link:hover {
    background: rgba(140,20,38,.08);
    color: var(--burdeos);
    border-color: var(--burdeos);
  }
  .nav-pills .nav-link.active {
    background: var(--burdeos) !important;
    color: #fff !important;
    border-color: var(--burdeos) !important;
    box-shadow: 0 4px 14px rgba(155,26,46,.25);
  }
  .plato-card.gallery-hidden {
    display: none;
  }
  @keyframes cardIn {
    from { opacity: 0; transform: translateY(10px) scale(.97) }
    to   { opacity: 1; transform: none }
  }
  .plato-card:not(.gallery-hidden) {
    animation: cardIn .3s ease both;
  }

  /* ─── CTA RESERVA — fondo madera ─── */
  .cta-section {
    position: relative;
    margin: 2rem -1rem 0;
    padding: 3.5rem 1.5rem;
    text-align: center;
    color: #fff;
    border-radius: 24px;
    overflow: hidden;
    background-color: var(--roble-oscuro);
    background-image:
      repeating-linear-gradient(180deg, rgba(60,30,15,.5) 0 2px, transparent 2px 28px, rgba(40,20,10,.4) 28px 30px, transparent 30px 60px),
      repeating-linear-gradient(180deg, rgba(150,100,50,.13) 0 1px, transparent 1px 11px),
      radial-gradient(ellipse 120px 14px at 25% 35%, rgba(255,200,150,.07), transparent),
      radial-gradient(ellipse 90px 10px at 70% 60%, rgba(255,200,150,.06), transparent);
  }
  .cta-section::before {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,.2), transparent 30%, transparent 70%, rgba(0,0,0,.3));
    pointer-events: none;
  }
  .cta-section > * { position: relative; z-index: 1 }
  .cta-section .eyebrow {
    display: inline-flex; align-items: center; gap: .8rem;
    font-family: var(--font-display); font-style: italic;
    font-size: .85rem; letter-spacing: .22em;
    color: var(--dorado-x); text-transform: uppercase; margin-bottom: .6rem;
  }
  .cta-section .eyebrow::before, .cta-section .eyebrow::after {
    content: ''; width: 32px; height: 1px; background: var(--dorado-x); opacity: .5;
  }
  .cta-section h2 {
    font-family: var(--font-display); font-style: italic; font-weight: 400;
    font-size: clamp(2rem, 5vw, 2.8rem);
    color: #fff; margin: 0 0 .6rem;
  }
  .cta-section h2 em { color: var(--dorado-x); font-style: italic }
  .cta-section p {
    color: rgba(251,246,239,.85); font-size: 1rem;
    margin: 0 auto 1.8rem; max-width: 40ch; line-height: 1.6;
  }
  .cta-section .btn-cta {
    display: inline-flex; align-items: center; gap: .6rem;
    background: var(--burdeos);
    color: #fff; text-decoration: none;
    padding: .95rem 1.8rem;
    border-radius: 999px;
    font-weight: 600; font-size: .95rem;
    box-shadow: 0 8px 24px rgba(155,26,46,.4);
    border: 1px solid rgba(200,155,60,.3);
    transition: all .25s;
  }
  .cta-section .btn-cta:hover {
    background: var(--burdeos-osc);
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(155,26,46,.5);
  }

  @media (max-width: 500px) {
    .cta-section { margin: 1.5rem -.7rem 0; padding: 2.5rem 1.2rem }
  }
</style>
@endpush

@section('content')
<main class="page-wrap">

  <div class="gal-hero">
    <div class="eyebrow">Nuestra cocina</div>
    <h1>Platos del día</h1>
    <p>Cada día algo diferente. Productos frescos del mercado, recetas de toda la vida con un toque personal.</p>
  </div>

  {{-- ─── Bootstrap Nav Pills · filtro de galería ─── --}}
  <nav aria-label="Filtrar galería por categoría">
    <div class="nav nav-pills gap-2 flex-nowrap overflow-x-auto pb-2 mb-3 justify-content-center" style="scrollbar-width:none">
      <button class="nav-link active flex-shrink-0" onclick="filterGallery('all',this)"
              data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Ver todos los platos">
        <i class="bi bi-grid-3x3-gap me-1"></i> Todos
      </button>
      <button class="nav-link flex-shrink-0" onclick="filterGallery('compartir',this)"
              data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Para compartir en la mesa">
        <i class="bi bi-people me-1"></i> Para compartir
      </button>
      <button class="nav-link flex-shrink-0" onclick="filterGallery('tapas',this)"
              data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Tapas y entrantes">
        <i class="bi bi-egg-fried me-1"></i> Tapas
      </button>
      <button class="nav-link flex-shrink-0" onclick="filterGallery('marisco',this)"
              data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Mariscos y pescados frescos">
        <i class="bi bi-water me-1"></i> Marisco
      </button>
      <button class="nav-link flex-shrink-0" onclick="filterGallery('principal',this)"
              data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Platos principales del día">
        <i class="bi bi-fire me-1"></i> Principales
      </button>
      <button class="nav-link flex-shrink-0" onclick="filterGallery('postre',this)"
              data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Postres de la casa">
        <i class="bi bi-cup-straw me-1"></i> Postres
      </button>
    </div>
  </nav>

  {{-- ─── GRID DE PLATOS ─── --}}
  <div class="platos-grid">
    <article class="plato-card plato-featured" data-cat="compartir">
      <img src="https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&w=900&q=75" alt="Tabla ibérica de la casa: selección de quesos curados, jamón y embutidos" loading="lazy"/>
      <div class="caption">
        <div class="plato-cat">Para compartir</div>
        <h3 class="plato-name">Tabla ibérica de la casa</h3>
        <p class="plato-desc">Selección de quesos curados, jamón y embutidos</p>
      </div>
    </article>
    <article class="plato-card" data-cat="tapas">
      <img src="https://images.unsplash.com/photo-1599974579688-8dbdd335c77f?auto=format&fit=crop&w=600&q=75" alt="Croquetas caseras de jamón con bechamel cremosa" loading="lazy"/>
      <div class="caption">
        <div class="plato-cat">Tapa estrella</div>
        <h3 class="plato-name">Croquetas caseras</h3>
        <p class="plato-desc">De jamón, bechamel cremosa</p>
      </div>
    </article>
    <article class="plato-card" data-cat="tapas">
      <img src="https://images.unsplash.com/photo-1633436374961-09b92742047b?auto=format&fit=crop&w=600&q=75" alt="Salmorejo cordobés con huevo y jamón" loading="lazy"/>
      <div class="caption">
        <div class="plato-cat">Frío</div>
        <h3 class="plato-name">Salmorejo cordobés</h3>
        <p class="plato-desc">Receta tradicional, huevo y jamón</p>
      </div>
    </article>
    <article class="plato-card" data-cat="marisco">
      <img src="https://images.unsplash.com/photo-1559847844-5315695dadae?auto=format&fit=crop&w=600&q=75" alt="Gambas al ajillo con aceite, ajo y guindilla" loading="lazy"/>
      <div class="caption">
        <div class="plato-cat">Marisco</div>
        <h3 class="plato-name">Gambas al ajillo</h3>
        <p class="plato-desc">Aceite, ajo y guindilla</p>
      </div>
    </article>
    <article class="plato-card" data-cat="principal">
      <img src="https://images.unsplash.com/photo-1551183053-bf91a1d81141?auto=format&fit=crop&w=600&q=75" alt="Arroz del día según mercado" loading="lazy"/>
      <div class="caption">
        <div class="plato-cat">Plato principal</div>
        <h3 class="plato-name">Arroz del día</h3>
        <p class="plato-desc">Varía según mercado</p>
      </div>
    </article>
    <article class="plato-card" data-cat="postre">
      <img src="https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=600&q=75" alt="Tarta de queso horneada al estilo de la casa" loading="lazy"/>
      <div class="caption">
        <div class="plato-cat">Postre</div>
        <h3 class="plato-name">Tarta de queso</h3>
        <p class="plato-desc">Horneada al estilo de la casa</p>
      </div>
    </article>
  </div>

  {{-- ─── PRÓXIMAMENTE (Instagram/Facebook) ─── --}}
  <div class="social-placeholder">
    <div class="social-icons">
      <span aria-label="Instagram"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><defs><linearGradient id="ig-grad" x1="0%" y1="100%" x2="100%" y2="0%"><stop offset="0%" stop-color="#fdf497"/><stop offset="5%" stop-color="#fdf497"/><stop offset="45%" stop-color="#fd5949"/><stop offset="60%" stop-color="#d6249f"/><stop offset="90%" stop-color="#285AEB"/></linearGradient></defs><rect x="2" y="2" width="20" height="20" rx="5" fill="url(#ig-grad)"/><circle cx="12" cy="12" r="4.5" fill="none" stroke="#fff" stroke-width="1.8"/><circle cx="17.4" cy="6.6" r="1.2" fill="#fff"/></svg></span>
      <span aria-label="Facebook"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" fill="#1877F2"/><path d="M14.5 12.5h2.2l.3-2.6h-2.5V8.2c0-.75.2-1.3 1.3-1.3h1.4V4.6c-.25-.04-1.1-.1-2.1-.1-2.07 0-3.5 1.25-3.5 3.55v1.85H9.4v2.6h2.2V19h2.9v-6.5z" fill="#fff"/></svg></span>
    </div>
    <h2>Próximamente</h2>
    <p>Publicaciones y Reels traídos de Instagram y Facebook mediante Meta Graph API.</p>
    <div class="social-eta">Tiempo estimado: 3 semanas hasta aprobación</div>
  </div>

  {{-- ─── CTA: ¿Comemos juntos? ─── --}}
  <section class="cta-section">
    <div class="eyebrow">Te esperamos</div>
    <h2>¿Comemos <em>juntos</em>?</h2>
    <p>Reserva tu mesa en un minuto. Cocinamos para ti.</p>
    <a class="btn-cta" href="{{ route('reservar') }}">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
      Reservar ahora
    </a>
  </section>

</main>
@endsection

@push('scripts')
<script>
  function filterGallery(cat, btn) {
    document.querySelectorAll('.plato-card').forEach(function (card) {
      if (cat === 'all' || card.dataset.cat === cat) {
        card.classList.remove('gallery-hidden');
      } else {
        card.classList.add('gallery-hidden');
      }
    });
    document.querySelectorAll('.nav-pills .nav-link').forEach(function (b) {
      b.classList.remove('active');
    });
    btn.classList.add('active');
  }
</script>
@endpush
