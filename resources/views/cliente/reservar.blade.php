@extends('layouts.app')
@section('title','Reservar · Miss Whitney')
@section('description','Reserva tu mesa en Miss Whitney, Huelva. Formulario rápido y confirmación inmediata. Grupos, celebraciones y menús especiales.')

@push('styles')
<style>
  .res-card {
    max-width: 640px; margin: 0 auto;
    padding: 1.4rem 1.5rem 1.5rem;
    background: rgba(255,255,255,.95);
    backdrop-filter: blur(14px);
    border: 1px solid rgba(255,255,255,.9);
    box-shadow: var(--shadow-lg);
    border-radius: 24px;
  }

  /* En modo embedded el card es la ventana entera */
  body.is-embedded .res-card {
    background: #fff;
    box-shadow: 0 30px 80px rgba(0,0,0,.4);
    padding: 2.2rem 2rem 2rem;
    margin: clamp(20px, 4vw, 50px) auto;
  }
  @media (max-width: 600px) {
    body.is-embedded .res-card {
      margin: 0; border-radius: 0;
      min-height: 100vh; min-height: 100dvh;
      padding: 3.5rem 1.2rem 2rem;  /* top = espacio para el botón X fijo */
    }
  }
  .res-card__head {
    display: flex; align-items: center; justify-content: space-between;
    gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;
  }
  .res-card__head h2 {
    font-size: 1.5rem; margin: 0;
    color: var(--burdeos-osc);
  }
  .info-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    background: var(--burdeos-pale);
    color: var(--burdeos);
    border: 1px solid rgba(155,26,46,.2);
    padding: .45rem .85rem; border-radius: 999px;
    font-size: .82rem; font-weight: 600;
    cursor: pointer;
    transition: background .18s, transform .18s;
  }
  .info-btn:hover { background: #fce8ec; transform: translateY(-1px) }

  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem }
  @media (max-width: 500px) { .form-row { grid-template-columns: 1fr } }

  .personas-grid {
    display: grid; grid-template-columns: repeat(5,1fr); gap: .4rem; margin-top: .38rem;
  }
  .pers-btn {
    aspect-ratio: 1; border-radius: 10px;
    border: 1.5px solid var(--gris-claro);
    background: rgba(255,255,255,.8);
    font-weight: 700; font-size: .9rem; cursor: pointer;
    transition: all .18s ease;
  }
  .pers-btn:hover { border-color: var(--burdeos); color: var(--burdeos) }
  .pers-btn.selected {
    background: var(--burdeos); color: #fff; border-color: var(--burdeos);
    box-shadow: 0 4px 12px rgba(155,26,46,.22);
  }

  /* Modal interno de Información útil */
  .info-overlay {
    position: fixed; inset: 0; z-index: 200;
    display: none;
    background: rgba(20,10,10,.55);
    backdrop-filter: blur(4px);
    align-items: center; justify-content: center;
    padding: 1rem;
    animation: infoFade .25s ease;
  }
  .info-overlay.is-open { display: flex }
  @keyframes infoFade { from { opacity: 0 } to { opacity: 1 } }

  .info-panel {
    background: #fff;
    border-radius: 20px;
    width: min(480px, 100%);
    max-height: 86vh; overflow-y: auto;
    padding: 1.6rem 1.8rem;
    box-shadow: 0 24px 60px rgba(0,0,0,.35);
    animation: infoSlide .35s cubic-bezier(.22,1,.36,1);
  }
  @keyframes infoSlide { from { transform: translateY(20px); opacity: 0 } to { transform: none; opacity: 1 } }

  .info-panel__head {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 1.2rem;
  }
  .info-panel__head h3 {
    margin: 0; font-size: 1.4rem;
    color: var(--burdeos-osc);
  }
  .info-panel__close {
    width: 32px; height: 32px; border-radius: 50%;
    border: none; background: var(--burdeos-pale);
    color: var(--burdeos); cursor: pointer;
    display: grid; place-items: center;
    transition: background .18s, transform .18s;
  }
  .info-panel__close:hover { background: #fce8ec; transform: rotate(90deg) }

  .info-row {
    display: flex; gap: .9rem; align-items: flex-start;
    padding: .85rem 0;
    border-bottom: 1px solid rgba(155,26,46,.08);
  }
  .info-row:last-child { border-bottom: none }
  .info-ico { font-size: 1.35rem; flex-shrink: 0 }
  .info-row strong { display: block; font-size: .9rem; color: var(--negro); margin-bottom: .15rem }
  .info-row span { font-size: .82rem; color: var(--gris); line-height: 1.55 }

  /* éxito */
  .success-msg { display: none; text-align: center; padding: 2rem 1rem }
  .success-msg .check { font-size: 3rem; margin-bottom: .8rem }
  .success-msg h3 { font-size: 1.5rem; margin-bottom: .3rem }
  .success-msg p { color: var(--gris); margin: 0 }

  .field-err  { color: #c0392b; font-size: .75rem; margin-top: .22rem; display: none }
  .field-nota { color: #7a5a00; font-size: .75rem; margin-top: .22rem; display: none }
  .pers-info  { display: none; margin-top: .5rem; padding: .5rem .8rem; background: #fff8e1; border: 1px solid #e6c84a; border-radius: 8px; font-size: .82rem; color: #6b4f00 }


  /* Cuando aparece dentro del modal iframe → menos espacio */
  /* ── Modal embedded: márgenes simétricos ── */
  body.is-embedded .page-wrap {
    padding: 1rem !important;
    max-width: 100% !important;
    min-height: 0 !important;
  }
  body.is-embedded .res-card {
    max-width: 100%;        /* el card ocupa todo el ancho disponible */
    padding: 1.2rem 1.4rem 1.4rem;
    margin: 0;              /* sin auto-center, ya está centrado por el page-wrap */
  }
  body.is-embedded .res-card__head { margin-bottom: 1rem }
  body.is-embedded .field { margin-bottom: .7rem }
  body.is-embedded .field label { margin-bottom: .25rem; font-size: .82rem }
  body.is-embedded .field input,
  body.is-embedded .field select,
  body.is-embedded .field textarea { padding: .55rem .8rem }
  body.is-embedded .field textarea { min-height: 70px }
  body.is-embedded .pers-btn { font-size: .82rem }
  body.is-embedded .form-row { gap: .7rem }
</style>
@endpush

@section('content')
<main class="page-wrap">

  <div class="res-card">
    <div class="res-card__head">
      <h2>Datos de la reserva</h2>
      <button type="button" class="info-btn" onclick="openInfo()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
        </svg>
        Información útil
      </button>
    </div>

    <div id="form-area">
      <div class="form-row">
        <div class="field">
          <label for="r-nombre">Nombre completo *</label>
          <input type="text" id="r-nombre" placeholder="Tu nombre"/>
          <span class="field-err" id="err-nombre"></span>
        </div>
        <div class="field">
          <label for="r-tel">Telefono *</label>
          <input type="tel" id="r-tel" placeholder="6XX XXX XXX"/>
          <span class="field-err" id="err-tel"></span>
        </div>
      </div>
      <div class="field">
        <label for="r-email">Email</label>
        <input type="email" id="r-email" placeholder="tu@correo.es"/>
        <span class="field-err" id="err-email"></span>
      </div>
      <div class="form-row">
        <div class="field">
          <label for="r-fecha">Fecha *</label>
          <input type="date" id="r-fecha"/>
          <span class="field-err" id="err-fecha"></span>
        </div>
        <div class="field">
          <label for="r-hora">Hora *</label>
          <input type="text" id="r-hora" placeholder="14:00" maxlength="5" inputmode="numeric"/>
          <span class="field-err"  id="err-hora"></span>
          <span class="field-nota" id="nota-hora">La cocina cierra a las 15:40.</span>
        </div>
      </div>
      <div class="field">
        <label>Numero de personas *</label>
        <div class="personas-grid" id="pers-grid">
          <button class="pers-btn" type="button" onclick="setPers(1,this)">1</button>
          <button class="pers-btn" type="button" onclick="setPers(2,this)">2</button>
          <button class="pers-btn" type="button" onclick="setPers(3,this)">3</button>
          <button class="pers-btn" type="button" onclick="setPers(4,this)">4</button>
          <button class="pers-btn" type="button" onclick="setPers(5,this)">5</button>
          <button class="pers-btn" type="button" onclick="setPers(6,this)">6</button>
          <button class="pers-btn" type="button" onclick="setPers(7,this)">7</button>
          <button class="pers-btn" type="button" onclick="setPers(8,this)">8</button>
          <button class="pers-btn" type="button" onclick="setPers(9,this)">+8</button>
        </div>
        <div class="pers-info" id="pers-info">Para grupos de mas de 8 personas llama al <a href="tel:+34959254960" style="color:#7b1c2b;font-weight:700">959 254 960</a>.</div>
        <span class="field-err" id="err-pers"></span>
      </div>
      <div class="field">
        <label for="r-notas">Notas o peticiones especiales</label>
        <textarea id="r-notas" placeholder="Alergias, ocasión especial, zona preferida…"></textarea>
      </div>
      <button class="btn-primary" onclick="submitReserva()" style="width:100%">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13M22 2L15 22l-4-9-9-4 19-7z"/></svg>
        Enviar reserva
      </button>
    </div>

    <div class="success-msg" id="success-msg">
      <div class="check"><i class="bi bi-calendar2-check"></i></div>
      <h3>¡Reserva recibida!</h3>
      <p>Te contactaremos en breve para confirmar tu mesa.</p>
      <button class="btn-secondary" onclick="resetForm()" style="margin-top:1.2rem">Nueva reserva</button>
    </div>
  </div>

  {{-- ─── Modal interno: Información útil ─── --}}
  <div class="info-overlay" id="info-overlay">
    <div class="info-panel" role="dialog" aria-modal="true">
      <div class="info-panel__head">
        <h3>Información útil</h3>
        <button class="info-panel__close" onclick="closeInfo()" aria-label="Cerrar">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>

      <div class="info-row">
        <span class="info-ico"><i class="bi bi-clock"></i></span>
        <div>
          <strong>Horario</strong>
          <span>Lunes a viernes: 08:00 – 17:00<br>Sábados: 07:00 – 16:00<br>Domingos: cerrado</span>
        </div>
      </div>
      <div class="info-row">
        <span class="info-ico"><i class="bi bi-people-fill"></i></span>
        <div>
          <strong>Grupos y celebraciones</strong>
          <span>Para grupos de más de 8 personas o eventos privados, contáctanos directamente.</span>
        </div>
      </div>
      <div class="info-row">
        <span class="info-ico"><i class="bi bi-telephone-fill"></i></span>
        <div>
          <strong>Teléfono directo</strong>
          <span>También puedes llamarnos al <a href="tel:+34959254960" style="color:var(--burdeos);font-weight:600">959 254 960</a></span>
        </div>
      </div>
      <div class="info-row">
        <span class="info-ico"><i class="bi bi-exclamation-triangle-fill"></i></span>
        <div>
          <strong>Política de reservas</strong>
          <span>Mantenemos la mesa durante 15 minutos. Para cancelaciones avísanos con 2h de antelación.</span>
        </div>
      </div>
    </div>
  </div>
</main>

<div class="toast" id="toast"></div>

@push('scripts')
<script>
(function(){
  const today = new Date().toISOString().split('T')[0];
  document.getElementById('r-fecha').min = today;

  // ── Funciones de validación por campo ───────────────────────────

  function validarNombre() {
    const v = document.getElementById('r-nombre').value.trim();
    showErr('nombre', v ? '' : 'El nombre es obligatorio');
  }

  function validarTel() {
    const v = document.getElementById('r-tel').value.trim();
    if (!v) showErr('tel', 'El telefono es obligatorio');
    else if (!/^[6789]\d{8}$/.test(v.replace(/[\s\-]/g, ''))) showErr('tel', 'Formato invalido (ej. 612 345 678)');
    else showErr('tel', '');
  }

  function validarEmail() {
    const v = document.getElementById('r-email').value.trim();
    showErr('email', (v && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) ? 'El email no tiene un formato valido' : '');
  }

  function validarFecha() {
    const v = document.getElementById('r-fecha').value;
    if (!v)        showErr('fecha', 'La fecha es obligatoria');
    else if (v < today) showErr('fecha', 'La fecha no puede ser anterior a hoy');
    else           showErr('fecha', '');
    // Re-validar hora porque el horario depende del día
    if (document.getElementById('r-hora').value.trim()) validarHora();
    else checkHorarioNota();
  }

  function validarHora() {
    const hora  = document.getElementById('r-hora').value.trim();
    const fecha = document.getElementById('r-fecha').value;
    if (!hora) { showErr('hora', ''); checkHorarioNota(); return; }
    if (!/^\d{2}:\d{2}$/.test(hora)) { showErr('hora', 'Formato invalido (ej. 14:00)'); checkHorarioNota(); return; }
    showErr('hora', validarHorario(fecha, hora) || '');
    checkHorarioNota();
  }

  // ── Listeners ────────────────────────────────────────────────────

  document.getElementById('r-nombre').addEventListener('blur',   validarNombre);
  document.getElementById('r-tel').addEventListener('blur',      validarTel);
  document.getElementById('r-email').addEventListener('blur',    validarEmail);
  document.getElementById('r-fecha').addEventListener('change',  validarFecha);

  // Hora: auto-formato + validación en tiempo real al completar HH:MM
  document.getElementById('r-hora').addEventListener('input', function() {
    let v = this.value.replace(/[^0-9]/g, '');
    if (v.length > 2) v = v.slice(0,2) + ':' + v.slice(2,4);
    this.value = v;
    if (/^\d{2}:\d{2}$/.test(v)) validarHora();
    else { showErr('hora', ''); checkHorarioNota(); }
  });
  document.getElementById('r-hora').addEventListener('blur', validarHora);

  // Devuelve mensaje de error si la hora está fuera del horario del día, o null si es válida
  function validarHorario(fecha, hora) {
    if (!fecha || !hora || !/^\d{2}:\d{2}$/.test(hora)) return null;
    const day  = new Date(fecha + 'T12:00:00').getDay(); // 0=dom,1=lun,...,6=sab
    const mins = parseInt(hora.slice(0,2)) * 60 + parseInt(hora.slice(3,5));
    if (day === 0) return 'Los domingos estamos cerrados';
    if (day === 6) {
      if (mins < 8*60)  return 'Los sabados abrimos a las 08:00';
      if (mins > 16*60) return 'Cerrado';
    } else {
      if (mins < 7*60)  return 'De lunes a viernes abrimos a las 07:00';
      if (mins > 17*60) return 'Cerrado';
    }
    return null;
  }

  function checkHorarioNota() {
    const hora  = document.getElementById('r-hora').value.trim();
    const fecha = document.getElementById('r-fecha').value;
    const nota  = document.getElementById('nota-hora');
    if (!hora || !fecha || !/^\d{2}:\d{2}$/.test(hora)) { nota.style.display = 'none'; return; }
    const day  = new Date(fecha + 'T12:00:00').getDay();
    const mins = parseInt(hora.slice(0,2)) * 60 + parseInt(hora.slice(3,5));
    const esDentroHorario = !validarHorario(fecha, hora);
    const limite = (day === 6) ? 16*60 : 17*60; // sabado cierra 16h, resto 17h
    nota.style.display = (esDentroHorario && mins >= 15*60 && mins < limite) ? 'block' : 'none';
  }

  let personas = 0;

  window.setPers = function(n, btn) {
    personas = n;
    document.querySelectorAll('.pers-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    document.getElementById('pers-info').style.display = n > 8 ? 'block' : 'none';
    showErr('pers', '');
  };

  window.openInfo  = () => document.getElementById('info-overlay').classList.add('is-open');
  window.closeInfo = () => document.getElementById('info-overlay').classList.remove('is-open');
  document.getElementById('info-overlay').addEventListener('click', e => {
    if (e.target.id === 'info-overlay') closeInfo();
  });

  function toast(msg, type='') {
    const t = document.getElementById('toast');
    t.textContent = msg; t.className = 'toast show ' + type;
    setTimeout(() => t.className = 'toast', 4000);
  }

  function showErr(id, msg) {
    const el = document.getElementById('err-' + id);
    if (!el) return;
    el.textContent = msg;
    el.style.display = msg ? 'block' : 'none';
  }

  function clearErrors() {
    ['nombre','tel','email','fecha','hora','pers'].forEach(id => showErr(id, ''));
    document.getElementById('nota-hora').style.display = 'none';
  }

  window.submitReserva = async function() {
    clearErrors();
    const nombre   = document.getElementById('r-nombre').value.trim();
    const telefono = document.getElementById('r-tel').value.trim();
    const email    = document.getElementById('r-email').value.trim();
    const fecha    = document.getElementById('r-fecha').value;
    const hora     = document.getElementById('r-hora').value.trim();
    const notas    = document.getElementById('r-notas').value.trim();

    let valid = true;
    if (!nombre)   { showErr('nombre', 'El nombre es obligatorio'); valid = false; }
    if (!telefono) {
      showErr('tel', 'El telefono es obligatorio'); valid = false;
    } else if (!/^[6789]\d{8}$/.test(telefono.replace(/[\s\-]/g, ''))) {
      showErr('tel', 'Formato invalido (ej. 612 345 678)'); valid = false;
    }
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      showErr('email', 'El email no tiene un formato valido'); valid = false;
    }
    if (!fecha) {
      showErr('fecha', 'La fecha es obligatoria'); valid = false;
    } else if (fecha < today) {
      showErr('fecha', 'La fecha no puede ser anterior a hoy'); valid = false;
    }
    if (!hora) {
      showErr('hora', 'La hora es obligatoria'); valid = false;
    } else if (!/^\d{2}:\d{2}$/.test(hora)) {
      showErr('hora', 'Formato invalido (ej. 14:00)'); valid = false;
    } else {
      const errHorario = validarHorario(fecha, hora);
      if (errHorario) { showErr('hora', errHorario); valid = false; }
    }
    if (!personas) { showErr('pers', 'Indica el numero de personas'); valid = false; }

    if (!valid) return;

    const btn = document.querySelector('[onclick="submitReserva()"]');
    btn.disabled = true;
    btn.innerHTML = '<span style="display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:rspin .7s linear infinite"></span> Enviando...';

    try {
      const csrf = document.querySelector('meta[name="csrf-token"]').content;
      const res = await fetch('{{ route("api.reserva") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ nombre, telefono, email, fecha, hora, personas, notas })
      });
      const data = await res.json();
      if (data.ok) {
        document.getElementById('form-area').style.display = 'none';
        document.getElementById('success-msg').style.display = 'block';
      } else {
        toast(data.mensaje || 'Error al enviar', 'error');
        btn.disabled = false;
        btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13M22 2L15 22l-4-9-9-4 19-7z"/></svg> Enviar reserva';
      }
    } catch(e) {
      toast('Error de red. Intentalo de nuevo.', 'error');
      btn.disabled = false;
      btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13M22 2L15 22l-4-9-9-4 19-7z"/></svg> Enviar reserva';
    }
  };

  window.resetForm = function() {
    clearErrors();
    ['r-nombre','r-tel','r-email','r-fecha','r-hora','r-notas'].forEach(id => {
      const el = document.getElementById(id); if (el) el.value = '';
    });
    document.querySelectorAll('.pers-btn').forEach(b => b.classList.remove('selected'));
    document.getElementById('pers-info').style.display = 'none';
    personas = 0;
    document.getElementById('form-area').style.display = 'block';
    document.getElementById('success-msg').style.display = 'none';
  };
})();
</script>
<style>@keyframes rspin{to{transform:rotate(360deg)}}</style>
@endpush
@endsection
