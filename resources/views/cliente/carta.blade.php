@extends('layouts.app')
@section('title','Carta · Miss Whitney')
@section('description','Carta del restaurante Miss Whitney en Huelva — tapas, entrantes, platos principales, mariscos y postres caseros a precios familiares.')
@push('styles')
<style>
  .cursor-dot {
    display: none
  }

  /* ── Bootstrap nav-pills · brand override ── */
  .nav-pills .nav-link {
    color: var(--marron);
    font-weight: 600;
    font-size: .88rem;
    border: 1.5px solid var(--gris-claro, #e0d8d0);
    background: rgba(255, 255, 255, .8);
    border-radius: 999px;
    white-space: nowrap;
    transition: all .2s ease;
  }

  .nav-pills .nav-link:hover {
    background: rgba(140, 20, 38, .08);
    color: var(--burdeos);
    border-color: var(--burdeos);
  }

  .nav-pills .nav-link.active {
    background: var(--burdeos) !important;
    color: #fff !important;
    border-color: var(--burdeos) !important;
    box-shadow: 0 4px 14px rgba(165, 35, 55, .2);
  }

  /* ── Menu section ── */
  .menu-section {
    display: none;
    animation: fadeIn .35s ease both
  }

  .menu-section.visible {
    display: block
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(8px)
    }

    to {
      opacity: 1;
      transform: none
    }
  }

  .menu-section h2 {
    font-size: clamp(1.6rem, 3vw, 2.2rem);
    margin-bottom: .3rem;
  }

  .menu-section .cat-desc {
    color: var(--gris);
    font-size: .93rem;
    margin-bottom: 1.5rem
  }

  /* ── Dish grid ── */
  .dish-grid {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 320px), 1fr));
  }

  .dish-card {
    padding: 1.2rem 1.3rem;
    background: rgba(255, 255, 255, .75);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(165, 35, 55, .1);
    border-radius: 16px;
    box-shadow: 0 4px 18px rgba(30, 20, 15, .06);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    transition: transform .2s ease, box-shadow .2s ease;
  }

  .dish-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(30, 20, 15, .09)
  }

  .dish-card .dish-info {
    flex: 1
  }

  .dish-card .dish-name {
    font-family: var(--font-display);
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--negro);
    margin: 0 0 .25rem
  }

  .dish-card .dish-desc {
    font-size: .85rem;
    color: var(--gris);
    margin: 0;
    line-height: 1.5
  }

  .dish-card .dish-tags {
    display: flex;
    gap: .3rem;
    flex-wrap: wrap;
    margin-top: .5rem
  }

  .tag {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .04em;
    padding: .18rem .5rem;
    border-radius: 999px;
    text-transform: uppercase;
  }

  .tag-v {
    background: #e8f5e9;
    color: #2e7d32
  }

  .tag-g {
    background: #fff3e0;
    color: #e65100
  }

  .tag-p {
    background: #e3f2fd;
    color: #1565c0
  }

  .tag-s {
    background: #fce4ec;
    color: #880e4f
  }

  .dish-price {
    font-family: var(--font-display);
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--burdeos);
    white-space: nowrap;
  }

  /* ── Allergen note ── */
  .allergen-note {
    margin-top: 2rem;
    padding: .9rem 1.2rem;
    background: var(--burdeos-pale);
    border-left: 3px solid var(--burdeos);
    border-radius: 0 10px 10px 0;
    font-size: .84rem;
    color: var(--marron);
  }

  .allergen-note strong {
    color: var(--burdeos-osc)
  }
</style>
@endpush
@section('content')
<main class="page-wrap">
  <div class="section-label"><i class="bi bi-journal-text"></i> Carta digital</div>
  <h1 class="section-title">Nuestra carta</h1>
  <p class="section-sub">Cocina casera con ingredientes frescos. Precios con IVA incluido. Consulta disponibilidad estacional.</p>

  <div class="divider"></div>

  <!-- Bootstrap Nav Pills · categorías de la carta -->
  <nav aria-label="Categorías de la carta">
    <div class="nav nav-pills gap-2 flex-nowrap overflow-x-auto pb-2 mb-4" role="tablist" style="scrollbar-width:none">
      <button class="nav-link active flex-shrink-0" role="tab" onclick="showCat('entrantes',this)"
              data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Tapas, ensaladas y para compartir">
        <i class="bi bi-egg-fried me-1"></i> Entrantes
      </button>
      <button class="nav-link flex-shrink-0" role="tab" onclick="showCat('principales',this)"
              data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="El corazón de nuestra cocina">
        <i class="bi bi-fire me-1"></i> Principales
      </button>
      <button class="nav-link flex-shrink-0" role="tab" onclick="showCat('postres',this)"
              data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="El broche dulce de la comida">
        <i class="bi bi-cup-straw me-1"></i> Postres
      </button>
      <button class="nav-link flex-shrink-0" role="tab" onclick="showCat('bebidas',this)"
              data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Vinos, cervezas y sin alcohol">
        <i class="bi bi-cup-hot me-1"></i> Bebidas
      </button>
    </div>
  </nav>

  <!-- Entrantes -->
  <section class="menu-section visible" id="cat-entrantes">
    <h2>Entrantes</h2>
    <p class="cat-desc">Para compartir o para empezar con buen pie</p>
    <div class="dish-grid">
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Croquetas de jamón</p>
          <p class="dish-desc">Bechamel artesana, jamón ibérico curado, rebozado crujiente. Media ración (8 ud.)</p>
          <div class="dish-tags"><span class="tag tag-g">Gluten</span><span class="tag tag-s">Lácteos</span></div>
        </div>
        <span class="dish-price">7,50€</span>
      </div>
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Patatas bravas MW</p>
          <p class="dish-desc">Fritas en aceite de oliva, salsa brava casera y alioli de ajo negro</p>
          <div class="dish-tags"><span class="tag tag-v">Vegano</span></div>
        </div>
        <span class="dish-price">6,50€</span>
      </div>
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Tabla de ibéricos</p>
          <p class="dish-desc">Selección de jamón, lomo y chorizo ibérico con pan de cristal tostado</p>
          <div class="dish-tags"><span class="tag tag-g">Gluten</span></div>
        </div>
        <span class="dish-price">14,90€</span>
      </div>
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Ensalada de temporada</p>
          <p class="dish-desc">Lechugas variadas, tomate cherry, queso de cabra gratinado, nueces y vinagreta de miel</p>
          <div class="dish-tags"><span class="tag tag-v">Vegetariano</span><span class="tag tag-s">Lácteos</span></div>
        </div>
        <span class="dish-price">9,90€</span>
      </div>
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Pulpo a la brasa</p>
          <p class="dish-desc">Pulpo gallego a la brasa, cama de parmentier de patata y pimentón de la Vera</p>
          <div class="dish-tags"><span class="tag tag-p">Cefalópodos</span></div>
        </div>
        <span class="dish-price">16,50€</span>
      </div>
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Pan con tomate y aceite</p>
          <p class="dish-desc">Pan de masa madre, tomate de temporada rallado, AOVE y sal Maldon</p>
          <div class="dish-tags"><span class="tag tag-v">Vegano</span><span class="tag tag-g">Gluten</span></div>
        </div>
        <span class="dish-price">4,50€</span>
      </div>
    </div>
  </section>

  <!-- Principales -->
  <section class="menu-section" id="cat-principales">
    <h2>Platos principales</h2>
    <p class="cat-desc">El corazón de nuestra cocina, con cariño y tiempo</p>
    <div class="dish-grid">
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Secreto ibérico a la brasa</p>
          <p class="dish-desc">Secreto de cerdo ibérico, reducción de Pedro Ximénez, patatas asadas y pimientos del padrón</p>
          <div class="dish-tags"><span class="tag tag-s">Sulfitos</span></div>
        </div>
        <span class="dish-price">19,90€</span>
      </div>
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Bacalao al pil-pil</p>
          <p class="dish-desc">Lomo de bacalao confitado, salsa pil-pil tradicional, pimientos asados</p>
          <div class="dish-tags"><span class="tag tag-p">Pescado</span></div>
        </div>
        <span class="dish-price">21,50€</span>
      </div>
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Cocido madrileño</p>
          <p class="dish-desc">Los tres vuelcos: sopa de fideos, verduras y legumbres, y carnes variadas. Sólo martes y jueves</p>
          <div class="dish-tags"><span class="tag tag-g">Gluten</span><span class="tag tag-s">Apio</span></div>
        </div>
        <span class="dish-price">18,00€</span>
      </div>
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Risotto de setas y trufa</p>
          <p class="dish-desc">Arroz carnaroli, setas silvestres, aceite de trufa negra y parmesano DOP</p>
          <div class="dish-tags"><span class="tag tag-v">Vegetariano</span><span class="tag tag-s">Lácteos</span></div>
        </div>
        <span class="dish-price">17,50€</span>
      </div>
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Entrecot de vaca</p>
          <p class="dish-desc">300g de entrecot nacional, punto de cocción a elegir, patatas fritas y ensalada verde</p>
        </div>
        <span class="dish-price">26,00€</span>
      </div>
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Menú del día</p>
          <p class="dish-desc">Primero + segundo + postre o café. Disponible de lunes a viernes de 13:00 a 16:00h</p>
          <div class="dish-tags"><span class="tag tag-v">Varía diario</span></div>
        </div>
        <span class="dish-price">13,50€</span>
      </div>
    </div>
  </section>

  <!-- Postres -->
  <section class="menu-section" id="cat-postres">
    <h2>Postres</h2>
    <p class="cat-desc">El broche dulce que se merece una buena comida</p>
    <div class="dish-grid">
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Tarta de queso La Viña</p>
          <p class="dish-desc">Tarta vasca de queso, horneada al punto cremoso, con mermelada de frutos rojos</p>
          <div class="dish-tags"><span class="tag tag-s">Lácteos</span><span class="tag tag-g">Gluten</span></div>
        </div>
        <span class="dish-price">6,50€</span>
      </div>
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Crema catalana</p>
          <p class="dish-desc">Crema de yema con caramelizado al momento, cítricos y canela</p>
          <div class="dish-tags"><span class="tag tag-s">Lácteos</span><span class="tag tag-s">Huevos</span></div>
        </div>
        <span class="dish-price">5,50€</span>
      </div>
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Coulant de chocolate</p>
          <p class="dish-desc">Interior fundente de chocolate 70%, bola de vainilla de Madagascar</p>
          <div class="dish-tags"><span class="tag tag-g">Gluten</span><span class="tag tag-s">Huevos</span></div>
        </div>
        <span class="dish-price">7,00€</span>
      </div>
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Fruta de temporada</p>
          <p class="dish-desc">Selección de fruta fresca cortada, reducción de menta y limón</p>
          <div class="dish-tags"><span class="tag tag-v">Vegano</span></div>
        </div>
        <span class="dish-price">4,00€</span>
      </div>
    </div>
  </section>

  <!-- Bebidas -->
  <section class="menu-section" id="cat-bebidas">
    <h2>Bebidas</h2>
    <p class="cat-desc">Vinos, cervezas, refrescos y cócteles sin alcohol</p>
    <div class="dish-grid">
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Vino de la casa (tinto/blanco/rosado)</p>
          <p class="dish-desc">Selección de vino joven de Denominación de Origen. Copa o media botella</p>
        </div>
        <span class="dish-price">2,50€ / 8€</span>
      </div>
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Cerveza artesanal</p>
          <p class="dish-desc">Rubia, tostada o IPA de productores locales. Caña o tercio</p>
          <div class="dish-tags"><span class="tag tag-g">Gluten</span></div>
        </div>
        <span class="dish-price">2,80€ / 3,50€</span>
      </div>
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Agua mineral (50cl)</p>
          <p class="dish-desc">Con o sin gas</p>
          <div class="dish-tags"><span class="tag tag-v">Vegano</span></div>
        </div>
        <span class="dish-price">1,50€</span>
      </div>
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Sangría de la casa</p>
          <p class="dish-desc">Vino tinto, frutas frescas de temporada, canela y naranja. Jarra 1L</p>
          <div class="dish-tags"><span class="tag tag-s">Sulfitos</span></div>
        </div>
        <span class="dish-price">12,00€ jarra</span>
      </div>
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Café</p>
          <p class="dish-desc">Solo, cortado, con leche, americano o capuchino. Mezcla tostada artesanal</p>
          <div class="dish-tags"><span class="tag tag-s">Puede tener lácteos</span></div>
        </div>
        <span class="dish-price">1,50€ – 2,20€</span>
      </div>
      <div class="dish-card">
        <div class="dish-info">
          <p class="dish-name">Licores de sobremesa</p>
          <p class="dish-desc">Orujo, pacharán, limoncello, brandy. Consumición incluida en menú del día</p>
        </div>
        <span class="dish-price">2,50€</span>
      </div>
    </div>
  </section>

  <div class="allergen-note">
    <strong>Alérgenos:</strong> V=Vegano · Veg=Vegetariano · G=Gluten · L=Lácteos · H=Huevos · P=Pescado/Marisco · S=Sulfitos.<br>
    Infórmanos de cualquier alergia o intolerancia antes de pedir. Manipulamos alérgenos en cocina.
  </div>
</main>
@endsection

@push('scripts')
<script>
  function showCat(id, btn) {
    document.querySelectorAll('.menu-section').forEach(function (s) {
      s.classList.remove('visible');
    });
    document.getElementById('cat-' + id).classList.add('visible');
    document.querySelectorAll('.nav-pills .nav-link').forEach(function (b) {
      b.classList.remove('active');
    });
    btn.classList.add('active');
  }
</script>
@endpush