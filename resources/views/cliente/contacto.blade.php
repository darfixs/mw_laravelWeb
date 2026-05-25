@extends('layouts.app')
@section('title','Contacto · Miss Whitney')
@section('description','Contacta con Miss Whitney — Avenida Escultora Miss Whitney 15, Huelva. Teléfono: 959 254 960. Horario de lunes a sábado.')
@push('styles')
<style>
.contact-grid {
      display: grid; gap: 2rem;
      grid-template-columns: 1fr 1fr;
      align-items: start;
    }
    @media (max-width:800px) { .contact-grid { grid-template-columns: 1fr } }

    /* ── Contact card ── */
    .contact-card {
      padding: 2rem 2.2rem;
      background: rgba(255,255,255,.78);
      backdrop-filter: blur(14px);
      border: 1px solid rgba(255,255,255,.9);
      box-shadow: var(--shadow-lg); border-radius: 24px;
    }
    .contact-card h2 { font-size: 1.6rem; margin-bottom: 1.4rem }

    .contact-method {
      display: flex; gap: 1rem; align-items: center;
      padding: .95rem 1rem; border-radius: 14px;
      border: 1px solid rgba(165,35,55,.1);
      background: rgba(255,255,255,.7);
      margin-bottom: .8rem;
      transition: transform .18s ease, box-shadow .18s ease;
      text-decoration: none; color: inherit; cursor: pointer;
    }
    .contact-method:hover { transform: translateY(-2px); box-shadow: var(--shadow); cursor: var(--cursor-pointer) }
    .contact-method .cm-ico {
      width: 44px; height: 44px; border-radius: 12px;
      background: linear-gradient(135deg, var(--burdeos-pale), #fde8ec);
      border: 1px solid rgba(165,35,55,.18);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.3rem; flex-shrink: 0;
    }
    .contact-method .cm-info strong { display: block; font-size: .88rem; color: var(--negro) }
    .contact-method .cm-info span { font-size: .85rem; color: var(--burdeos); font-weight: 600 }

    /* ── Message form ── */
    .msg-card {
      padding: 2rem 2.2rem;
      background: rgba(255,255,255,.78);
      backdrop-filter: blur(14px);
      border: 1px solid rgba(255,255,255,.9);
      box-shadow: var(--shadow-lg); border-radius: 24px;
    }
    .msg-card h2 { font-size: 1.6rem; margin-bottom: .3rem }
    .msg-card p.sub { color: var(--gris); font-size: .88rem; margin-bottom: 1.4rem }

    /* ── Map section ── */
    .map-section { margin-top: 2.5rem }
    .map-section h2 { font-size: 1.6rem; margin-bottom: .3rem }
    .map-section p.sub { color: var(--gris); font-size: .9rem; margin-bottom: 1.2rem }

    .map-wrap {
      border-radius: 20px; overflow: hidden;
      box-shadow: var(--shadow-lg);
      border: 1px solid rgba(165,35,55,.1);
    }
    .map-placeholder {
      height: 340px; background: linear-gradient(135deg, #f0eae8, var(--burdeos-pale));
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      gap: 1rem; color: var(--marron);
    }
    .map-placeholder .map-ico { font-size: 3rem }
    .map-placeholder strong { font-family: var(--font-display); font-size: 1.5rem; color: var(--burdeos-osc) }
    .map-placeholder span { font-size: .9rem; color: var(--gris) }
    .map-placeholder a {
      margin-top: .4rem; padding: .6rem 1.4rem; border-radius: 999px;
      background: var(--burdeos); color: #fff; font-weight: 700; font-size: .88rem;
      box-shadow: 0 6px 18px rgba(165,35,55,.22);
      display: inline-flex; align-items: center; gap: .4rem;
    }
    .map-placeholder a:hover { background: var(--burdeos-osc) }

    /* ── Hours table ── */
    .hours-table { width: 100%; border-collapse: collapse; font-size: .9rem; margin-top: 1.2rem }
    .hours-table td { padding: .6rem .4rem; border-bottom: 1px solid rgba(165,35,55,.08) }
    .hours-table td:first-child { font-weight: 600; color: var(--negro) }
    .hours-table td:last-child { color: var(--gris); text-align: right }
    .hours-table tr:last-child td { border-bottom: none }
    .badge-open {
      display: inline-flex; align-items: center; gap: .3rem;
      background: #e8f5e9; color: #2e7d32; font-size: .74rem; font-weight: 700;
      padding: .15rem .55rem; border-radius: 999px; text-transform: uppercase;
    }
    .badge-closed { background: #fce4ec; color: #880e4f }
    .badge-open::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #4caf50; display: block }
    .badge-closed::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #e91e63; display: block }

    /* success */
    .success-msg { display: none; text-align: center; padding: 2rem }
    .success-msg .check { font-size: 3.5rem; margin-bottom: 1rem }

    /* Responsive — evitar overflow horizontal en móvil */
    @media (max-width: 600px) {
      .contact-card, .msg-card {
        padding: 1.3rem 1.1rem !important;
      }
      .map-section { margin-top: 1.5rem }
      .contact-method { padding: .8rem !important; gap: .7rem !important }
      .contact-method .cm-ico {
        width: 38px !important; height: 38px !important;
        font-size: 1.1rem !important;
      }
      .contact-method .cm-info strong { font-size: .82rem !important }
      .contact-method .cm-info span { font-size: .78rem !important; word-break: break-word }
      .hours-table td { font-size: .82rem; padding: .5rem .2rem }
      .map-wrap iframe { height: 280px !important }
    }
    @media (max-width: 400px) {
      .contact-card, .msg-card { padding: 1.1rem .95rem !important }
    }

  
    /* Cuando cm-ico contiene SVG (redes sociales), sin fondo decorativo */
    .contact-method .cm-ico:has(svg) {
      background: transparent;
      border: none;
      padding: 0;
    }
    .contact-method .cm-ico svg { width: 100%; height: 100% }

</style>
@endpush
@section('content')
<main class="page-wrap">
    <div class="contact-grid">
      <!-- Métodos de contacto + horarios -->
      <div>
        <div class="contact-card">
          <h2>Cómo contactarnos</h2>
          <a class="contact-method" href="tel:+34959254960">
            <div class="cm-ico"><i class="bi bi-telephone-fill"></i></div>
            <div class="cm-info"><strong>Teléfono</strong><span>959 254 960</span></div>
          </a>
          <a class="contact-method" href="mailto:misswhitneyfacturas@gmail.com">
            <div class="cm-ico"><i class="bi bi-envelope-fill"></i></div>
            <div class="cm-info"><strong>Email</strong><span>misswhitneyfacturas@gmail.com</span></div>
          </a>
          <a class="contact-method" href="https://www.instagram.com/restaurantemisswhitney/" target="_blank" rel="noopener">
            <div class="cm-ico"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><defs><linearGradient id="ig-grad" x1="0%" y1="100%" x2="100%" y2="0%"><stop offset="0%" stop-color="#fdf497"/><stop offset="5%" stop-color="#fdf497"/><stop offset="45%" stop-color="#fd5949"/><stop offset="60%" stop-color="#d6249f"/><stop offset="90%" stop-color="#285AEB"/></linearGradient></defs><rect x="2" y="2" width="20" height="20" rx="5" fill="url(#ig-grad)"/><circle cx="12" cy="12" r="4.5" fill="none" stroke="#fff" stroke-width="1.8"/><circle cx="17.4" cy="6.6" r="1.2" fill="#fff"/></svg></div>
            <div class="cm-info"><strong>Instagram</strong><span>@restaurantemisswhitney</span></div>
          </a>
          <a class="contact-method" href="https://www.facebook.com/profile.php?id=61583563838133&locale=es_ES" target="_blank" rel="noopener">
            <div class="cm-ico"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" fill="#1877F2"/><path d="M14.5 12.5h2.2l.3-2.6h-2.5V8.2c0-.75.2-1.3 1.3-1.3h1.4V4.6c-.25-.04-1.1-.1-2.1-.1-2.07 0-3.5 1.25-3.5 3.55v1.85H9.4v2.6h2.2V19h2.9v-6.5z" fill="#fff"/></svg></div>
            <div class="cm-info"><strong>Facebook</strong><span>Restaurante Bar Miss Whitney</span></div>
          </a>
        </div>

        <div style="margin-top:1.2rem; padding:1.6rem; background:rgba(255,255,255,.72); backdrop-filter:blur(12px); border-radius:20px; border:1px solid rgba(165,35,55,.1); box-shadow:var(--shadow)">
          <h3 style="font-size:1.2rem;margin-bottom:.2rem">Horario</h3>
          <p style="color:var(--gris);font-size:.85rem;margin-bottom:.6rem">Cocina y barra</p>
          <table class="hours-table">
            <tr><td>Lunes – Viernes</td><td>08:00 – 17:00</td></tr>
            <tr><td>Sábado</td><td>07:00 – 16:00</td></tr>
            <tr><td>Domingo</td><td>Cerrado</td></tr>
          </table>
          <div style="margin-top:.8rem;display:flex;align-items:center;gap:.6rem;font-size:.84rem;color:var(--gris)">
            Estado ahora: <span class="badge-open" id="status-badge">Abierto</span>
          </div>
        </div>
      </div>

      <!-- Formulario de mensaje -->
      <div class="msg-card">
        <h2>Envíanos un mensaje</h2>
        <p class="sub">Te respondemos en menos de 24h en días laborables.</p>

        <div id="msg-form">
          <div class="field">
            <label for="c-nombre">Nombre *</label>
            <input type="text" id="c-nombre" placeholder="Tu nombre"/>
          </div>
          <div class="field">
            <label for="c-email">Email *</label>
            <input type="email" id="c-email" placeholder="tu@correo.es"/>
          </div>
          <div class="field">
            <label for="c-asunto">Asunto</label>
            <select id="c-asunto">
              <option>Consulta general</option>
              <option>Reserva o evento</option>
              <option>Facturación</option>
              <option>Felicitación / sugerencia</option>
              <option>Otro</option>
            </select>
          </div>
          <div class="field">
            <label for="c-msg">Mensaje *</label>
            <textarea id="c-msg" placeholder="Cuéntanos en qué podemos ayudarte…" style="min-height:120px"></textarea>
          </div>
          <button class="btn-primary" onclick="sendMsg()" style="width:100%">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13M22 2L15 22l-4-9-9-4 19-7z"/></svg>
            Enviar mensaje
          </button>
        </div>

        <div class="success-msg" id="msg-success">
          <div class="check"><i class="bi bi-envelope-fill"></i></div>
          <h3 style="font-size:1.5rem">¡Mensaje enviado!</h3>
          <p style="color:var(--gris)">Gracias por escribirnos. Te contestaremos pronto.</p>
          <button class="btn-secondary" onclick="resetMsg()" style="margin-top:1rem">Nuevo mensaje</button>
        </div>
      </div>
    </div>

    <!-- Mapa / Dónde estamos -->
    <section class="map-section" id="mapa">
      <div class="section-label"><i class="bi bi-geo-alt-fill"></i> Dónde estamos</div>
      <h2 class="section-title" style="font-size:clamp(1.6rem,3.5vw,2.4rem)">Encuéntranos</h2>
      <p class="sub">En el corazón del barrio, fácil acceso en metro y autobús. Parking público a 200m.</p>
      <div class="map-wrap">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3176.8!2d-6.9445445!3d37.2538009!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzfCsDE1JzEzLjciTiA2wrA1NicxNi40Ilc!5e0!3m2!1ses!2ses!4v1"
          width="100%" height="340" style="border:0;display:block" allowfullscreen
          loading="lazy" referrerpolicy="no-referrer-when-downgrade"
          title="Ubicación de Miss Whitney en Huelva">
        </iframe>
      </div>
      </section>
  </main>

  <div class="toast" id="toast"></div>

@push('scripts')
<script>
(function(){
  // ── Badge estado abierto/cerrado ──
  const badge = document.getElementById('status-badge');
  if(badge){
    const now = new Date();
    const h = now.getHours(), m = now.getMinutes(), min = h*60+m;
    const dow = now.getDay(); // 0=dom,6=sab
    let open = false;
    // Lun-Vie 8-17 · Sab 7-16 · Dom cerrado
    if(dow>=1&&dow<=5) open=(min>=480&&min<1020);
    if(dow===6)        open=(min>=420&&min<960);
    if(dow===0)        open=false;
    badge.className = open ? 'badge-open' : 'badge-closed';
    badge.textContent = open ? 'Abierto ahora' : 'Cerrado ahora';
  }

  // ── Formulario de contacto ──
  function toast(msg, type=''){
    const t = document.getElementById('toast');
    t.textContent = msg; t.className = 'toast show ' + type;
    setTimeout(()=>{ t.className='toast' }, 4000);
  }

  window.sendMsg = async function(){
    const nombre  = document.getElementById('c-nombre').value.trim();
    const email   = document.getElementById('c-email').value.trim();
    const asunto  = document.getElementById('c-asunto').value;
    const mensaje = document.getElementById('c-msg').value.trim();

    if(!nombre){ toast('Escribe tu nombre','error'); return; }
    if(!email) { toast('Escribe tu email','error');  return; }
    if(!mensaje){ toast('Escribe un mensaje','error'); return; }

    const btn = document.querySelector('[onclick="sendMsg()"]');
    btn.disabled = true;
    btn.innerHTML = '<span style="display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite"></span> Enviando…';

    try{
      const csrf = document.querySelector('meta[name="csrf-token"]').content;
      const res = await fetch('{{ route("api.contacto") }}', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
        body: JSON.stringify({nombre,email,asunto,mensaje})
      });
      const data = await res.json();
      if(data.ok){
        document.getElementById('msg-form').style.display = 'none';
        document.getElementById('msg-success').style.display = 'block';
      } else {
        toast(data.mensaje || 'Error al enviar', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-envelope-fill"></i> Enviar mensaje';
      }
    } catch(e){
      toast('Error de red. Inténtalo de nuevo.','error');
      btn.disabled = false;
      btn.innerHTML = '✉ Enviar mensaje';
    }
  };

  window.resetMsg = function(){
    ['c-nombre','c-email','c-msg'].forEach(id=>document.getElementById(id).value='');
    document.getElementById('msg-form').style.display = 'block';
    document.getElementById('msg-success').style.display = 'none';
  };
})();
</script>
<style>@keyframes spin{to{transform:rotate(360deg)}}</style>
@endpush
@endsection
