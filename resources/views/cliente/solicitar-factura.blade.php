@extends('layouts.app')
@section('title','Solicitar Factura · Miss Whitney')
@push('styles')
<style>
.factura-layout {
      max-width: 720px;
      margin: 0 auto;
    }

    .factura-card {
      padding: 1.4rem 1.6rem;
      background: rgba(255,255,255,.8);
      backdrop-filter: blur(14px);
      border: 1px solid rgba(255,255,255,.9);
      box-shadow: var(--shadow-lg); border-radius: 24px;
    }
    .factura-card h2 { font-size: 1.6rem; margin-bottom: .25rem }
    .factura-card p.sub { color: var(--gris); font-size: .88rem; margin-bottom: 1.5rem }

    /* En modo embedded el card es la ventana entera */
    body.is-embedded .factura-card {
      background: #fff;
      box-shadow: 0 30px 80px rgba(0,0,0,.4);
      margin: clamp(20px, 4vw, 50px) auto;
    }
    body.is-embedded .factura-layout {
      grid-template-columns: 1fr !important;
    }
    body.is-embedded .info-sidebar {
      display: none !important;
    }
    @media (max-width: 600px) {
      body.is-embedded .factura-card {
        margin: 0; border-radius: 0;
        min-height: 100vh; min-height: 100dvh;
        padding: 3.5rem 1.2rem 2rem;
      }
    }

    /* ── Step indicator ── */
    .steps {
      display: flex; align-items: center; gap: 0; margin-bottom: 2rem;
    }
    .step {
      display: flex; align-items: center; gap: .4rem; font-size: .82rem; font-weight: 600;
    }
    .step-num {
      width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
      font-size: .8rem; font-weight: 700; flex-shrink: 0;
      background: var(--gris-claro); color: var(--gris);
      transition: background .3s, color .3s;
    }
    .step.active .step-num { background: var(--burdeos); color: #fff }
    .step.done .step-num { background: #2e7d32; color: #fff }
    .step-line { flex: 1; height: 1px; background: var(--gris-claro); margin: 0 .6rem }
    .step-label { color: var(--gris) }
    .step.active .step-label { color: var(--burdeos); font-weight: 700 }
    .step.done .step-label { color: #2e7d32 }

    /* ── Form sections ── */
    .form-step { display: none }
    .form-step.active { display: block; animation: fadeIn .3s ease }
    @keyframes fadeIn { from { opacity:0; transform:translateY(8px) } to { opacity:1; transform:none } }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem }
    @media (max-width:520px) { .form-row { grid-template-columns: 1fr } }

    /* ── Import ticket section ── */
    .ticket-area {
      border: 2px dashed var(--gris-claro); border-radius: 14px;
      padding: 1.8rem 1.2rem; text-align: center;
      background: var(--burdeos-pale); cursor: pointer;
      transition: border-color .2s, background .2s;
    }
    .ticket-area:hover { border-color: var(--burdeos); background: #fce8ec }
    .ticket-area .ta-ico { font-size: 2.4rem; margin-bottom: .5rem }
    .ticket-area p { font-size: .88rem; color: var(--gris); margin: 0 }
    .ticket-area strong { display: block; font-size: .95rem; color: var(--marron); margin-bottom: .3rem }
    #file-input { display: none }

    /* OCR spinner */
    .ocr-spinner {
      display: inline-block; width: 14px; height: 14px;
      border: 2px solid rgba(140,20,38,.2); border-top-color: var(--burdeos);
      border-radius: 50%; animation: ocrspin .8s linear infinite;
    }
    @keyframes ocrspin { to { transform: rotate(360deg) } }

    .preview-wrap { margin-top: 1rem; border-radius: 12px; overflow: hidden; display: none }
    .preview-wrap img { width: 100%; max-height: 260px; object-fit: cover }
    .preview-wrap .prev-label {
      display: flex; align-items: center; gap: .5rem; justify-content: space-between;
      padding: .6rem .8rem; background: rgba(255,255,255,.8); font-size: .84rem;
    }
    .prev-del { background: none; border: none; color: var(--burdeos); cursor: pointer; font-size: .8rem; font-weight: 700 }

    /* ── Summary card ── */
    .summary-row {
      display: flex; justify-content: space-between; align-items: center;
      padding: .6rem 0; border-bottom: 1px solid rgba(165,35,55,.08); font-size: .9rem;
    }
    .summary-row:last-child { border-bottom: none }
    .summary-row span:first-child { color: var(--gris) }
    .summary-row span:last-child { font-weight: 600; color: var(--negro) }

    /* ── Info sidebar ── */
    .info-sidebar {
      padding: 1.8rem;
      background: linear-gradient(150deg, var(--burdeos-pale), #fff4f2);
      border: 1px solid rgba(165,35,55,.14);
      border-radius: 22px; box-shadow: var(--shadow);
    }
    .info-sidebar h3 { font-size: 1.2rem; margin-bottom: 1rem }
    .info-step-row {
      display: flex; gap: .8rem; margin-bottom: 1.1rem;
    }
    .info-step-ico {
      width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
      background: rgba(165,35,55,.12); display: flex; align-items: center; justify-content: center;
      font-size: 1rem;
    }
    .info-step-row p { font-size: .84rem; color: var(--gris); margin: 0; line-height: 1.5 }
    .info-step-row strong { display: block; font-size: .88rem; color: var(--negro); margin-bottom: .15rem }

    .legal-note {
      margin-top: 1.4rem; padding: .8rem 1rem;
      background: rgba(255,255,255,.7); border-radius: 10px;
      border: 1px solid rgba(165,35,55,.1); font-size: .78rem; color: var(--gris);
      line-height: 1.6;
    }
    .legal-note a { color: var(--burdeos); text-decoration: underline }

    /* success */
    .success-screen { display: none; text-align: center; padding: 2.5rem 1rem }
    .success-screen .big-ico { font-size: 4rem; margin-bottom: 1rem }
    .success-screen h3 { font-size: 2rem; margin-bottom: .5rem }
    .success-screen .ref {
      display: inline-block; margin: .8rem 0;
      padding: .5rem 1.2rem; background: var(--burdeos-pale);
      border-radius: 999px; font-family: var(--font-display);
      font-size: 1.1rem; font-weight: 700; color: var(--burdeos-osc);
      letter-spacing: .05em;
    }
    .btn-nav { display: flex; gap: .8rem; justify-content: flex-end; margin-top: 1.4rem; flex-wrap: wrap }

    /* NIF validator indicator */
    .nif-ok { color: #2e7d32; font-size: .78rem; font-weight: 600; margin-top: .3rem; display: none }
    .nif-err { color: var(--burdeos); font-size: .78rem; font-weight: 600; margin-top: .3rem; display: none }

    .field-err { color: #c0392b; font-size: .75rem; margin-top: .22rem; display: none }

    /* Lineas ticket */
    .linea-row { display: grid; grid-template-columns: 62px 1fr 90px 28px; gap: .4rem; margin-bottom: .4rem; align-items: center }
    .linea-row input { padding: .42rem .55rem; border: 1.5px solid #e0d8d8; border-radius: 8px; font-size: .84rem; width: 100%; box-sizing: border-box; font-family: inherit }
    .linea-row input:focus { outline: none; border-color: var(--burdeos) }
    .l-del { background: none; border: none; color: #c0392b; cursor: pointer; font-size: .9rem; padding: .2rem; line-height: 1 }
    .btn-add-linea { background: none; border: 1.5px solid var(--burdeos); color: var(--burdeos); border-radius: 6px; padding: .28rem .65rem; font-size: .78rem; font-weight: 600; cursor: pointer; transition: background .15s }
    .btn-add-linea:hover { background: var(--burdeos-pale) }

    /* -- Hero madera -- */
    .sf-hero {
      position: relative;
      background: linear-gradient(135deg, #6b0f1a 0%, #8c1426 45%, #5b3a26 100%);
      padding: clamp(2rem,6vw,3.5rem) 1.5rem 0;
      text-align: center;
      overflow: hidden;
    }
    .sf-hero__inner { position: relative; z-index: 1; padding-bottom: 2rem }
    .sf-hero__icon { font-size: 2.2rem; margin-bottom: .6rem; display: block }
    .sf-hero__title {
      font-family: 'Cormorant Garamond', Georgia, serif;
      font-size: clamp(1.8rem, 5vw, 3rem);
      font-style: italic; font-weight: 600;
      color: #fff; margin: 0 0 .6rem;
    }
    .sf-hero__sub {
      font-size: .92rem; color: rgba(255,215,120,.8);
      line-height: 1.6; margin: 0 auto; max-width: 44ch;
    }
    .sf-hero__wave { display: block; line-height: 0 }
    .sf-hero__wave svg { width: 100%; height: 54px; display: block }

    @media (max-width:860px) {
      .sf-hero { margin-top: 68px }
    }


    /* Botón "¿Cómo funciona?" */
    .info-btn-sf {
      display: inline-flex; align-items: center; gap: .4rem;
      background: var(--burdeos-pale);
      color: var(--burdeos);
      border: 1px solid rgba(155,26,46,.2);
      padding: .42rem .8rem; border-radius: 999px;
      font-size: .8rem; font-weight: 600; cursor: pointer;
      transition: background .18s, transform .18s;
    }
    .info-btn-sf:hover { background: #fce8ec; transform: translateY(-1px) }

    /* Modal interno ¿Cómo funciona? */
    .howto-overlay {
      position: fixed; inset: 0; z-index: 200;
      display: none;
      background: rgba(20,10,10,.55);
      backdrop-filter: blur(4px);
      align-items: center; justify-content: center;
      padding: 1rem;
      animation: htFade .25s ease;
    }
    .howto-overlay.is-open { display: flex }
    @keyframes htFade { from { opacity: 0 } to { opacity: 1 } }

    .howto-panel {
      background: #fff;
      border-radius: 20px;
      width: min(480px, 100%);
      max-height: 86vh; overflow-y: auto;
      padding: 1.6rem 1.8rem;
      box-shadow: 0 24px 60px rgba(0,0,0,.35);
      animation: htSlide .35s cubic-bezier(.22,1,.36,1);
    }
    @keyframes htSlide { from { transform: translateY(20px); opacity: 0 } to { transform: none; opacity: 1 } }

    .howto-panel__head {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 1.2rem;
    }
    .howto-panel__head h3 { margin: 0; font-size: 1.4rem; color: var(--burdeos-osc) }
    .howto-panel__close {
      width: 32px; height: 32px; border-radius: 50%;
      border: none; background: var(--burdeos-pale);
      color: var(--burdeos); cursor: pointer;
      display: grid; place-items: center;
      transition: background .18s, transform .18s;
    }
    .howto-panel__close:hover { background: #fce8ec; transform: rotate(90deg) }

    .howto-step {
      display: flex; gap: .9rem; align-items: flex-start;
      padding: .85rem 0;
      border-bottom: 1px solid rgba(155,26,46,.08);
    }
    .howto-step:last-child { border-bottom: none }
    .howto-step__num {
      flex-shrink: 0;
      width: 30px; height: 30px; border-radius: 50%;
      background: var(--burdeos); color: #fff;
      display: grid; place-items: center;
      font-weight: 700; font-size: .85rem;
    }
    .howto-step strong { display: block; font-size: .92rem; color: var(--negro); margin-bottom: .15rem }
    .howto-step span   { font-size: .82rem; color: var(--gris); line-height: 1.55 }



    /* ── Modal embedded: márgenes simétricos ── */
    body.is-embedded .page-wrap {
      padding: 1rem !important;
      max-width: 100% !important;
      min-height: 0 !important;
    }
    body.is-embedded .factura-layout {
      max-width: 100% !important;
      margin: 0 !important;
    }
    body.is-embedded .factura-card {
      max-width: 100%;
      padding: 1.2rem 1.4rem 1.4rem;
      margin: 0;
    }
    body.is-embedded .steps { margin-bottom: .9rem }
    body.is-embedded .field { margin-bottom: .7rem }
    body.is-embedded .field label { margin-bottom: .25rem; font-size: .82rem }
    body.is-embedded .field input,
    body.is-embedded .field select,
    body.is-embedded .field textarea { padding: .55rem .8rem }
    body.is-embedded .form-step h2 { margin-bottom: .15rem; font-size: 1.4rem }
    body.is-embedded .form-step .sub { font-size: .8rem; margin-bottom: 1rem }
    body.is-embedded .info-sidebar { display: none }
</style>
@endpush
@section('content')
<main class="page-wrap">

    <div class="factura-layout">

      <!-- Main form card -->
      <div class="factura-card" id="main-card">

        <!-- Steps -->
        <div class="steps" id="steps-indicator">
          <div class="step active" id="step-dot-1">
            <span class="step-num">1</span>
            <span class="step-label">Tus datos</span>
          </div>
          <div class="step-line"></div>
          <div class="step" id="step-dot-2">
            <span class="step-num">2</span>
            <span class="step-label">Ticket</span>
          </div>
          <div class="step-line"></div>
          <div class="step" id="step-dot-3">
            <span class="step-num">3</span>
            <span class="step-label">Confirmar</span>
          </div>
        </div>

        <!-- PASO 1: Datos fiscales -->
        <div class="form-step active" id="step1">
          <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:.4rem">
            <h2 style="margin:0">Datos fiscales</h2>
            <button type="button" class="info-btn-sf" onclick="openHowto()">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              ¿Cómo funciona?
            </button>
          </div>
          <p class="sub">Introduce los datos del receptor de la factura.</p>

          <div class="field">
            <label for="f-tipo">Tipo de factura *</label>
            <select id="f-tipo" onchange="toggleTipo()">
              <option value="particular">Particular (persona física)</option>
              <option value="empresa">Empresa / Autónomo</option>
            </select>
          </div>

          <div class="form-row">
            <div class="field">
              <label for="f-nombre" id="label-nombre">Nombre completo *</label>
              <input type="text" id="f-nombre" placeholder="Tu nombre y apellidos"/>
              <span class="field-err" id="err-nombre"></span>
            </div>
            <div class="field" id="empresa-field" style="display:none">
              <label for="f-empresa">Nombre de empresa *</label>
              <input type="text" id="f-empresa" placeholder="Empresa S.L."/>
              <span class="field-err" id="err-empresa"></span>
            </div>
          </div>

          <div class="form-row">
            <div class="field">
              <label for="f-nif" id="label-nif">NIF / DNI *</label>
              <input type="text" id="f-nif" placeholder="12345678A" oninput="validateNIF(this)" maxlength="9"/>
              <span class="nif-ok" id="nif-ok">Formato valido</span>
              <span class="nif-err" id="nif-err">Formato incorrecto</span>
              <span class="field-err" id="err-nif"></span>
            </div>
            <div class="field">
              <label for="f-email">Email para recibir factura *</label>
              <input type="email" id="f-email" placeholder="tu@correo.es"/>
              <span class="field-err" id="err-email"></span>
            </div>
          </div>

          <div class="field">
            <label for="f-direccion">Direccion fiscal *</label>
            <input type="text" id="f-direccion" placeholder="Calle, numero, piso..."/>
            <span class="field-err" id="err-dir"></span>
          </div>

          <div class="form-row">
            <div class="field">
              <label for="f-cp">Codigo postal *</label>
              <input type="text" id="f-cp" placeholder="28001" maxlength="5"/>
              <span class="field-err" id="err-cp"></span>
            </div>
            <div class="field">
              <label for="f-ciudad">Ciudad *</label>
              <input type="text" id="f-ciudad" placeholder="Madrid"/>
              <span class="field-err" id="err-ciudad"></span>
            </div>
          </div>

          <div class="btn-nav">
            <button class="btn-primary" onclick="goStep(2)">
              Siguiente
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </button>
          </div>
        </div>

        <!-- PASO 2: Ticket -->
        <div class="form-step" id="step2">
          <h2>Adjunta el ticket</h2>
          <p class="sub">Sube una foto clara del ticket. Detectaremos la fecha, importe y conceptos automáticamente.</p>

          <div class="ticket-area" onclick="document.getElementById('file-input').click()" id="drop-area">
            <div class="ta-ico"><i class="bi bi-camera-fill"></i></div>
            <strong>Sube o haz una foto del ticket</strong>
            <p>JPG, PNG o WEBP · Máximo 10MB<br>Toca para seleccionar archivo o foto de la cámara</p>
          </div>
          <input type="file" id="file-input" accept="image/jpeg,image/png,image/webp" capture="environment" onchange="handleFile(this)"/>

          <div class="preview-wrap" id="preview-wrap">
            <img id="preview-img" src="" alt="Vista previa del ticket"/>
            <div class="prev-label">
              <span id="prev-name">ticket.jpg</span>
              <button class="prev-del" onclick="removeFile()">✕ Eliminar</button>
            </div>
          </div>

          <div id="ocr-status" style="display:none;align-items:center;gap:.5rem;margin-top:.7rem;padding:.6rem .9rem;background:#fdf2f4;border-radius:8px;font-size:.85rem;color:var(--burdeos)"></div>

          <!-- Datos extraídos del ticket (se muestran solo tras OCR) -->
          <div id="ocr-fields" style="display:none;margin-top:1.4rem;padding:1.1rem 1.2rem;background:#f9f6f6;border-radius:12px;border:1px solid #efe6e6">
            <p style="margin:0 0 .9rem;font-size:.82rem;color:var(--gris);text-transform:uppercase;letter-spacing:.06em;font-weight:600">
              Datos del ticket
            </p>

            <div class="field" style="margin-bottom:.85rem">
              <label for="f-fecha" style="font-size:.85rem">Fecha del consumo</label>
              <input type="date" id="f-fecha" readonly style="background:#fff;cursor:default"/>
            </div>

            <div class="field" style="margin-bottom:0">
              <label for="f-importe" style="font-size:.85rem">Importe total (€)</label>
              <input type="number" id="f-importe" placeholder="0.00" min="0" step="0.01" readonly style="background:#fff;cursor:default"/>
            </div>

            <p style="margin:.9rem 0 0;font-size:.78rem;color:var(--gris)">
              ¿Algún dato incorrecto?
              <button type="button" onclick="permitirEdicion()" style="background:none;border:none;color:var(--burdeos);font-weight:600;cursor:pointer;padding:0;font-size:.78rem;text-decoration:underline">
                Editar manualmente
              </button>
            </p>
          </div>

          <!-- Lineas del ticket -->
          <div id="lineas-section" style="display:none;margin-top:1.2rem;padding:1rem 1.1rem;background:#f9f6f6;border-radius:12px;border:1px solid #efe6e6">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.6rem">
              <span style="font-size:.78rem;color:var(--gris);text-transform:uppercase;letter-spacing:.06em;font-weight:600">Lineas del ticket</span>
              <button type="button" class="btn-add-linea" onclick="addLinea()">+ Anadir linea</button>
            </div>
            <div style="display:grid;grid-template-columns:62px 1fr 90px 28px;gap:.4rem;padding:0 .1rem;margin-bottom:.3rem">
              <span style="font-size:.7rem;color:var(--gris);font-weight:700">CANT.</span>
              <span style="font-size:.7rem;color:var(--gris);font-weight:700">CONCEPTO</span>
              <span style="font-size:.7rem;color:var(--gris);font-weight:700;text-align:right;padding-right:.3rem">IMPORTE</span>
              <span></span>
            </div>
            <div id="lineas-container"></div>
          </div>

          <div class="field" style="margin-top:1rem">
            <label for="f-obs">Observaciones (opcional)</label>
            <input type="text" id="f-obs" placeholder="Numero de mesa, hora aproximada..."/>
          </div>

          <div class="btn-nav">
            <button class="btn-secondary" onclick="goStep(1)">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
              Atrás
            </button>
            <button class="btn-primary" onclick="goStep(3)">
              Revisar solicitud
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </button>
          </div>
        </div>

        <!-- PASO 3: Confirmar -->
        <div class="form-step" id="step3">
          <h2>Confirmar solicitud</h2>
          <p class="sub">Revisa los datos antes de enviar. Procesaremos tu factura en 48h laborables.</p>

          <div style="background:var(--burdeos-pale);border-radius:14px;padding:1.2rem 1.4rem;margin-bottom:1.2rem;border:1px solid rgba(165,35,55,.14)">
            <p style="font-size:.78rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--burdeos);margin:0 0 .8rem">Resumen</p>
            <div class="summary-row"><span>Nombre / Empresa</span><span id="sum-nombre">—</span></div>
            <div class="summary-row"><span>NIF / CIF</span><span id="sum-nif">—</span></div>
            <div class="summary-row"><span>Email</span><span id="sum-email">—</span></div>
            <div class="summary-row"><span>Dirección</span><span id="sum-dir">—</span></div>
            <div class="summary-row"><span>Fecha consumo</span><span id="sum-fecha">—</span></div>
            <div class="summary-row"><span>Importe</span><span id="sum-importe">—</span></div>
            <div class="summary-row"><span>Ticket adjunto</span><span id="sum-ticket">—</span></div>
          </div>

          <label style="display:flex;gap:.6rem;align-items:flex-start;font-size:.86rem;color:var(--gris);cursor:pointer;margin-bottom:1rem">
            <input type="checkbox" id="acepto-lopd" style="margin-top:3px;accent-color:var(--burdeos)"/>
            Acepto que Miss Whitney trate mis datos con el único fin de emitir esta factura, según la <a href="#" style="color:var(--burdeos);text-decoration:underline">política de privacidad</a>.
          </label>

          <div class="btn-nav">
            <button class="btn-secondary" onclick="goStep(2)">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
              Atrás
            </button>
            <button class="btn-primary" onclick="submitFactura()">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
              Enviar solicitud
            </button>
          </div>
        </div>

        <!-- SUCCESS -->
        <div class="success-screen" id="success-screen">
          <div class="big-ico"><i class="bi bi-check-circle-fill" style="color:#2da64a"></i></div>
          <h3>¡Solicitud enviada!</h3>
          <p style="color:var(--gris)">Tu número de referencia:</p>
          <span class="ref" id="ref-code">MW-2026-XXXX</span>

          <!-- Descarga inmediata si el PDF se generó -->
          <div id="pdf-download-wrap" style="display:none;margin-top:1.4rem">
            <p style="color:var(--gris);font-size:.88rem;margin:0 0 .8rem">
              Tu factura está lista. Puedes descargarla ahora:
            </p>
            <a id="pdf-download-btn" href="#" target="_blank" rel="noopener"
               style="display:inline-flex;align-items:center;gap:.55rem;text-decoration:none;
                      padding:.85rem 1.8rem;border-radius:12px;
                      background:linear-gradient(135deg,var(--burdeos),#6e0f1c);
                      color:#fff;font-weight:700;font-size:.95rem;
                      box-shadow:0 8px 24px rgba(140,20,38,.28)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              Descargar factura PDF
            </a>
          </div>

          <!-- Enviar al correo -->
          <div id="email-send-wrap" style="display:none;margin-top:1.6rem;padding-top:1.4rem;border-top:1px solid #f0e8e8;max-width:420px;margin-left:auto;margin-right:auto">
            <p style="color:var(--gris);font-size:.88rem;margin:0 0 .6rem">
              ¿Prefieres recibirla por email?
            </p>
            <div style="display:flex;gap:.5rem;align-items:stretch">
              <input type="email" id="email-send-input" placeholder="tu@correo.com"
                     style="flex:1;padding:.7rem .9rem;border:1.5px solid #e0d8d8;border-radius:10px;font-size:.92rem;font-family:inherit;outline:none">
              <button id="email-send-btn" type="button" onclick="enviarPorEmail()"
                      style="padding:.7rem 1.2rem;border:none;border-radius:10px;cursor:pointer;
                             background:#1b1b1b;color:#fff;font-weight:600;font-size:.9rem;font-family:inherit">
                Enviar
              </button>
            </div>
            <p id="email-send-feedback" style="font-size:.82rem;margin:.7rem 0 0;color:var(--gris);min-height:1.2em"></p>
          </div>

          <!-- Si el PDF no se generó, mostrar mensaje -->
          <div id="pdf-pending-wrap" style="display:none;margin-top:1.2rem">
            <p style="color:var(--gris);font-size:.9rem;max-width:40ch;margin:0 auto">
              Recibirás tu factura en <strong id="conf-email" style="color:var(--negro)"></strong>
              en un plazo máximo de <strong>48h laborables</strong>.
            </p>
          </div>

          <button class="btn-secondary" onclick="resetFactura()" style="margin-top:1.4rem">Nueva solicitud</button>
        </div>
      </div>


    </div>

  {{-- ─── Modal interno: ¿Cómo funciona? ─── --}}
  <div class="howto-overlay" id="howto-overlay">
    <div class="howto-panel" role="dialog" aria-modal="true">
      <div class="howto-panel__head">
        <h3>¿Cómo funciona?</h3>
        <button class="howto-panel__close" onclick="closeHowto()" aria-label="Cerrar">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>

      <div class="info-step-row">
        <div class="info-step-ico"><i class="bi bi-1-circle-fill"></i></div>
        <div><strong>Introduce tus datos fiscales</strong><p>Nombre o empresa, NIF/CIF y dirección para la factura.</p></div>
      </div>
      <div class="info-step-row">
        <div class="info-step-ico"><i class="bi bi-2-circle-fill"></i></div>
        <div><strong>Adjunta el ticket</strong><p>Haz una foto del ticket. Detectamos la fecha, importe y conceptos automáticamente.</p></div>
      </div>
      <div class="info-step-row">
        <div class="info-step-ico"><i class="bi bi-3-circle-fill"></i></div>
        <div><strong>Envía la solicitud</strong><p>Revisamos los datos y generamos tu factura en 48h laborables.</p></div>
      </div>
      <div class="info-step-row">
        <div class="info-step-ico"><i class="bi bi-envelope-fill"></i></div>
        <div><strong>Recibes la factura</strong><p>Te la enviamos en PDF por email. También puedes pedirla al teléfono <a href="tel:+34959254960" style="color:var(--burdeos);font-weight:600">959 254 960</a>.</p></div>
      </div>
      <div class="legal-note">
        <strong>Plazo legal:</strong> Las facturas se emiten en un máximo de 48h laborables desde la recepción de la solicitud.<br><br>
        <strong>Importante:</strong> El ticket debe ser de una consumición realizada en Miss Whitney. No emitimos facturas de consumiciones ajenas.
      </div>
    </div>
  </div>

  </main>
<div class="toast" id="toast"></div>
@endsection
@push('scripts')
<script>
  window.openHowto  = () => document.getElementById('howto-overlay').classList.add('is-open');
  window.closeHowto = () => document.getElementById('howto-overlay').classList.remove('is-open');
  document.addEventListener('click', e => {
    if(e.target.id === 'howto-overlay') closeHowto();
  });
</script>
<script>
  window.MW_SOLICITUDES_URL = '{{ route("api.solicitudes.store") }}';
  window.MW_API_BASE        = '{{ rtrim(url("/"), "/") }}';
</script>
<script>
  let currentStep = 1;
  let uploadedFile = null;
  let atendidoPorOcr = null;

  // ── Helpers ──────────────────────────────────────────────────────

  function toast(msg, type='') {
    const t = document.getElementById('toast'); t.textContent = msg;
    t.className = 'toast show ' + type; setTimeout(() => t.classList.remove('show'), 3500);
  }

  function showFieldErr(id, msg) {
    const el = document.getElementById('err-' + id);
    if (!el) return;
    el.textContent = msg;
    el.style.display = msg ? 'block' : 'none';
  }

  function clearStep1Errors() {
    ['nombre','empresa','nif','email','dir','cp','ciudad'].forEach(id => showFieldErr(id, ''));
  }

  // ── NIF/DNI/NIE/CIF validation ───────────────────────────────────

  function isValidDNI(v) {
    if (!/^[0-9]{8}[A-Z]$/.test(v)) return false;
    return v[8] === 'TRWAGMYFPDXBNJZSQVHLCKE'[parseInt(v.slice(0,8)) % 23];
  }

  function isValidNIE(v) {
    if (!/^[XYZ][0-9]{7}[A-Z]$/.test(v)) return false;
    const num = v.replace('X','0').replace('Y','1').replace('Z','2');
    return v[8] === 'TRWAGMYFPDXBNJZSQVHLCKE'[parseInt(num.slice(0,8)) % 23];
  }

  function isValidCIF(v) {
    if (!/^[ABCDEFGHJKLMNPQRSUVW][0-9]{7}[0-9A-J]$/.test(v)) return false;
    let sum = 0;
    for (let i = 1; i <= 7; i++) {
      let d = parseInt(v[i]);
      if (i % 2 !== 0) { d *= 2; if (d > 9) d -= 9; }
      sum += d;
    }
    const ctrl = (10 - (sum % 10)) % 10;
    return v[8] === String(ctrl) || v[8] === 'JABCDEFGHI'[ctrl];
  }

  function isValidNIF(val) {
    const v = val.toUpperCase().trim();
    return isValidDNI(v) || isValidNIE(v) || isValidCIF(v);
  }

  window.validateNIF = function(input) {
    const v = input.value.toUpperCase().trim();
    const ok  = document.getElementById('nif-ok');
    const err = document.getElementById('nif-err');
    if (v.length < 8) { ok.style.display='none'; err.style.display='none'; showFieldErr('nif',''); return; }
    const valid = isValidNIF(v);
    ok.style.display  = valid ? 'block' : 'none';
    err.style.display = valid ? 'none'  : 'block';
    showFieldErr('nif', '');
  };

  // ── Validación en tiempo real step 1 (blur) ──────────────────────

  function vNombre() {
    showFieldErr('nombre', document.getElementById('f-nombre').value.trim() ? '' : 'Campo obligatorio');
  }
  function vEmpresa() {
    const tipo = document.getElementById('f-tipo').value;
    if (tipo !== 'empresa') { showFieldErr('empresa', ''); return; }
    showFieldErr('empresa', document.getElementById('f-empresa').value.trim() ? '' : 'Campo obligatorio');
  }
  function vNif() {
    const v    = document.getElementById('f-nif').value.trim();
    const tipo = document.getElementById('f-tipo').value;
    if (!v) { showFieldErr('nif', 'Campo obligatorio'); return; }
    showFieldErr('nif', isValidNIF(v) ? '' : (tipo === 'empresa' ? 'CIF no valido' : 'DNI/NIF no valido'));
  }
  function vEmail() {
    const v = document.getElementById('f-email').value.trim();
    if (!v) { showFieldErr('email', 'Campo obligatorio'); return; }
    showFieldErr('email', /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) ? '' : 'Email no valido');
  }
  function vDir()    { showFieldErr('dir',    document.getElementById('f-direccion').value.trim() ? '' : 'Campo obligatorio'); }
  function vCp()     { showFieldErr('cp',     document.getElementById('f-cp').value.trim()        ? '' : 'Campo obligatorio'); }
  function vCiudad() { showFieldErr('ciudad', document.getElementById('f-ciudad').value.trim()    ? '' : 'Campo obligatorio'); }

  document.getElementById('f-nombre').addEventListener('blur',    vNombre);
  document.getElementById('f-empresa').addEventListener('blur',   vEmpresa);
  document.getElementById('f-nif').addEventListener('blur',       vNif);
  document.getElementById('f-email').addEventListener('blur',     vEmail);
  document.getElementById('f-direccion').addEventListener('blur', vDir);
  document.getElementById('f-cp').addEventListener('blur',        vCp);
  document.getElementById('f-ciudad').addEventListener('blur',    vCiudad);

  // ── Step navigation ──────────────────────────────────────────────

  window.toggleTipo = function() {
    const tipo = document.getElementById('f-tipo').value;
    document.getElementById('empresa-field').style.display = tipo === 'empresa' ? 'block' : 'none';
    document.getElementById('label-nif').textContent    = tipo === 'empresa' ? 'CIF *' : 'NIF / DNI *';
    document.getElementById('label-nombre').textContent = tipo === 'empresa' ? 'Nombre del responsable *' : 'Nombre completo *';
  };

  window.goStep = function(n) {
    if (n > currentStep) {
      if (currentStep === 1) {
        clearStep1Errors();
        const tipo    = document.getElementById('f-tipo').value;
        const nombre  = document.getElementById('f-nombre').value.trim();
        const empresa = document.getElementById('f-empresa').value.trim();
        const nif     = document.getElementById('f-nif').value.trim();
        const email   = document.getElementById('f-email').value.trim();
        const dir     = document.getElementById('f-direccion').value.trim();
        const cp      = document.getElementById('f-cp').value.trim();
        const ciudad  = document.getElementById('f-ciudad').value.trim();
        let ok = true;

        if (!nombre)  { showFieldErr('nombre', 'Campo obligatorio'); ok = false; }
        if (tipo === 'empresa' && !empresa) { showFieldErr('empresa', 'Campo obligatorio'); ok = false; }
        if (!nif) {
          showFieldErr('nif', 'Campo obligatorio'); ok = false;
        } else if (!isValidNIF(nif)) {
          showFieldErr('nif', tipo === 'empresa' ? 'CIF no valido' : 'DNI/NIF no valido'); ok = false;
        }
        if (!email) {
          showFieldErr('email', 'Campo obligatorio'); ok = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          showFieldErr('email', 'Email no valido'); ok = false;
        }
        if (!dir)    { showFieldErr('dir',    'Campo obligatorio'); ok = false; }
        if (!cp)     { showFieldErr('cp',     'Campo obligatorio'); ok = false; }
        if (!ciudad) { showFieldErr('ciudad', 'Campo obligatorio'); ok = false; }

        if (!ok) return;
      }
      if (currentStep === 2) {
        if (!uploadedFile) { toast('Sube una foto del ticket', 'error'); return; }
        const fecha    = document.getElementById('f-fecha').value;
        const importe  = document.getElementById('f-importe').value;
        if (!fecha || !importe) {
          toast('No se pudieron leer todos los datos. Pulsa "Editar manualmente" o sube una foto mas nitida.', 'error'); return;
        }
        // Resumen
        const tipo    = document.getElementById('f-tipo').value;
        const nombre  = document.getElementById('f-nombre').value.trim();
        const empresa = document.getElementById('f-empresa').value.trim();
        document.getElementById('sum-nombre').textContent  = tipo==='empresa' && empresa ? `${nombre} (${empresa})` : nombre;
        document.getElementById('sum-nif').textContent     = document.getElementById('f-nif').value.trim().toUpperCase();
        document.getElementById('sum-email').textContent   = document.getElementById('f-email').value.trim();
        document.getElementById('sum-dir').textContent     = `${document.getElementById('f-direccion').value.trim()}, ${cp_val()} ${document.getElementById('f-ciudad').value.trim()}`;
        document.getElementById('sum-fecha').textContent   = new Date(fecha).toLocaleDateString('es-ES',{year:'numeric',month:'long',day:'numeric'});
        document.getElementById('sum-importe').textContent = parseFloat(importe).toFixed(2) + ' €';
        document.getElementById('sum-ticket').textContent  = uploadedFile ? '+ ' + uploadedFile.name : '- Sin adjunto';
      }
    }

    document.getElementById('step'+currentStep).classList.remove('active');
    document.getElementById('step-dot-'+currentStep).classList.remove('active');
    if (n > currentStep) document.getElementById('step-dot-'+currentStep).classList.add('done');
    else document.getElementById('step-dot-'+currentStep).classList.remove('done');

    currentStep = n;
    document.getElementById('step'+currentStep).classList.add('active');
    document.getElementById('step-dot-'+currentStep).classList.add('active');
  };

  function cp_val() { return document.getElementById('f-cp').value.trim(); }

  // ── Lineas de ticket ─────────────────────────────────────────────

  window.addLinea = function(data) {
    document.getElementById('lineas-section').style.display = 'block';
    const container = document.getElementById('lineas-container');
    const row = document.createElement('div');
    row.className = 'linea-row';
    const cant = data && data.cantidad != null ? data.cantidad : '';
    const conc = data && data.concepto   ? data.concepto   : '';
    const imp  = data && data.importe   != null ? parseFloat(data.importe).toFixed(2) : '';
    row.innerHTML =
      `<input type="number" class="l-cant" min="1" value="${cant}" placeholder="1" oninput="recalcTotal()"/>` +
      `<input type="text"   class="l-conc" value="${conc}" placeholder="Concepto"/>` +
      `<input type="number" class="l-imp"  min="0" step="0.01" value="${imp}" placeholder="0.00" oninput="recalcTotal()"/>` +
      `<button type="button" class="l-del" onclick="removeLinea(this)">✕</button>`;
    container.appendChild(row);
    recalcTotal();
  };

  window.removeLinea = function(btn) {
    btn.closest('.linea-row').remove();
    recalcTotal();
    if (!document.getElementById('lineas-container').children.length) {
      document.getElementById('lineas-section').style.display = 'none';
    }
  };

  function recalcTotal() {
    let total = 0;
    document.querySelectorAll('#lineas-container .l-imp').forEach(inp => {
      total += parseFloat(inp.value) || 0;
    });
    if (total > 0) document.getElementById('f-importe').value = total.toFixed(2);
  }

  function renderLineas(lineas) {
    document.getElementById('lineas-container').innerHTML = '';
    if (lineas && lineas.length) {
      lineas.forEach(l => addLinea(l));
      document.getElementById('lineas-section').style.display = 'block';
    } else {
      document.getElementById('lineas-section').style.display = 'none';
    }
  }

  function getLineas() {
    const rows = document.querySelectorAll('#lineas-container .linea-row');
    if (!rows.length) return null;
    const lineas = [];
    rows.forEach(row => {
      const cant = parseInt(row.querySelector('.l-cant').value) || 1;
      const conc = row.querySelector('.l-conc').value.trim();
      const imp  = parseFloat(row.querySelector('.l-imp').value) || 0;
      if (conc || imp) lineas.push({ cantidad: cant, concepto: conc, importe: imp });
    });
    return lineas.length ? lineas : null;
  }

  // ── File / OCR ───────────────────────────────────────────────────

  window.handleFile = function(input) {
    const file = input.files[0]; if (!file) return;
    if (!file.type.startsWith('image/')) {
      toast('Solo se permiten imagenes (JPG, PNG, WEBP)', 'error');
      input.value = ''; return;
    }
    uploadedFile = file;
    document.getElementById('prev-name').textContent = file.name;
    const reader = new FileReader();
    reader.onload = e => { document.getElementById('preview-img').src = e.target.result; };
    reader.readAsDataURL(file);
    document.getElementById('preview-wrap').style.display = 'block';
    procesarTicketOCR(file);
  };

  async function procesarTicketOCR(file) {
    const ocrStatus = document.getElementById('ocr-status');
    ocrStatus.style.display = 'flex';
    ocrStatus.style.color = 'var(--burdeos)';
    ocrStatus.innerHTML = '<span class="ocr-spinner"></span> Analizando ticket...';
    try {
      const fd = new FormData();
      fd.append('ticket', file);
      const res = await fetch(window.MW_API_BASE + '/api/ocr/ticket', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: fd
      });
      const data = await res.json();
      if (!res.ok || !data.ok) throw new Error(data.mensaje || 'No se pudo leer el ticket');

      const campos = [];
      if (data.fecha)  { document.getElementById('f-fecha').value = data.fecha; campos.push('fecha'); }
      if (data.total)  { document.getElementById('f-importe').value = data.total.toFixed(2); campos.push('importe'); }
      if (data.atendido_por) atendidoPorOcr = data.atendido_por;

      if (data.lineas && data.lineas.length) {
        renderLineas(data.lineas);
        campos.push(data.lineas.length + ' linea(s)');
      }

      if (data.fecha || data.total) document.getElementById('ocr-fields').style.display = 'block';

      ocrStatus.style.color = campos.length ? '#2f855a' : '#a07000';
      ocrStatus.innerHTML   = campos.length
        ? 'Ticket leido correctamente (' + campos.join(', ') + ').'
        : 'No se detectaron datos. Vuelve a subir una foto mas nitida.';

    } catch (err) {
      ocrStatus.style.color = '#a07000';
      ocrStatus.innerHTML = err.message + '. Rellena manualmente.';
    }
  }

  window.removeFile = function() {
    uploadedFile = null;
    atendidoPorOcr = null;
    document.getElementById('file-input').value = '';
    document.getElementById('preview-wrap').style.display = 'none';
    document.getElementById('preview-img').src = '';
    document.getElementById('f-fecha').value = '';
    document.getElementById('f-importe').value = '';
    document.getElementById('ocr-status').style.display = 'none';
    document.getElementById('ocr-fields').style.display = 'none';
    renderLineas([]);
  };

  window.permitirEdicion = function() {
    ['f-fecha','f-importe'].forEach(id => {
      const el = document.getElementById(id);
      el.readOnly = false; el.style.cursor = 'auto';
    });
  };

  // ── Submit ───────────────────────────────────────────────────────

  let enviando = false;

  window.submitFactura = async function() {
    if (enviando) return;
    if (!document.getElementById('acepto-lopd').checked) {
      toast('Debes aceptar la politica de privacidad para continuar', 'error'); return;
    }

    const fd = new FormData();
    fd.append('tipo_receptor',  document.getElementById('f-tipo').value);
    fd.append('nombre_cliente', document.getElementById('f-nombre').value.trim());
    fd.append('nombre_empresa', document.getElementById('f-empresa').value.trim());
    fd.append('nif_cif',        document.getElementById('f-nif').value.trim());
    fd.append('email',          document.getElementById('f-email').value.trim());
    fd.append('direccion',      document.getElementById('f-direccion').value.trim());
    fd.append('codigo_postal',  document.getElementById('f-cp').value.trim());
    fd.append('ciudad',         document.getElementById('f-ciudad').value.trim());
    fd.append('fecha_consumo',  document.getElementById('f-fecha').value);
    fd.append('importe_ticket', document.getElementById('f-importe').value);
    fd.append('observaciones',  document.getElementById('f-obs').value.trim());
    fd.append('acepta_lopd',    '1');
    if (atendidoPorOcr) fd.append('atendido_por', atendidoPorOcr);
    const lineas = getLineas();
    if (lineas) fd.append('lineas_ticket', JSON.stringify(lineas));
    const fileInput = document.getElementById('file-input');
    if (fileInput.files.length) fd.append('ticket', fileInput.files[0]);

    enviando = true;
    const btn = document.querySelector('#step3 .btn-primary');
    const btnHtml = btn ? btn.innerHTML : '';
    if (btn) { btn.disabled = true; btn.textContent = 'Enviando...'; }

    try {
      const res = await fetch(window.MW_SOLICITUDES_URL, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
        body: fd
      });
      let data = {};
      try { data = await res.json(); } catch(e) {}

      if (!res.ok || !data.ok) {
        enviando = false;
        if (btn) { btn.disabled = false; btn.innerHTML = btnHtml; }
        toast(data.mensaje || 'Error al enviar la solicitud', 'error'); return;
      }

      document.getElementById('ref-code').textContent = data.referencia;
      window.MW_FACTURA_ID = data.factura_id || null;

      if (data.pdf_url) {
        document.getElementById('pdf-download-btn').href = data.pdf_url;
        document.getElementById('pdf-download-wrap').style.display = 'block';
        document.getElementById('pdf-pending-wrap').style.display = 'none';
        const emailInput = document.getElementById('email-send-input');
        const emailFb    = document.getElementById('email-send-feedback');
        if (emailInput) emailInput.value = data.email || '';
        if (emailFb)    emailFb.textContent = '';
        document.getElementById('email-send-wrap').style.display = 'block';
      } else {
        const confEmail = document.getElementById('conf-email');
        if (confEmail) confEmail.textContent = data.email;
        document.getElementById('pdf-download-wrap').style.display = 'none';
        document.getElementById('email-send-wrap').style.display = 'none';
        document.getElementById('pdf-pending-wrap').style.display = 'block';
      }

      document.getElementById('steps-indicator').style.display = 'none';
      ['step1','step2','step3'].forEach(id => document.getElementById(id).classList.remove('active'));
      document.getElementById('success-screen').style.display = 'block';

    } catch (err) {
      toast('Comprueba tu conexion. Si ya ves la referencia, la solicitud se registro correctamente.', 'error');
      enviando = false;
      if (btn) { btn.disabled = false; btn.innerHTML = btnHtml; }
    }
  };

  // ── Email envio ──────────────────────────────────────────────────

  window.enviarPorEmail = async function() {
    const input = document.getElementById('email-send-input');
    const btn   = document.getElementById('email-send-btn');
    const fb    = document.getElementById('email-send-feedback');
    const email = (input.value || '').trim();

    if (!window.MW_FACTURA_ID) { fb.style.color='#c53030'; fb.textContent='No se pudo identificar la factura.'; return; }
    if (!email || !email.includes('@')) { fb.style.color='#c53030'; fb.textContent='Introduce un email valido.'; input.focus(); return; }

    btn.disabled = true; btn.textContent = 'Enviando...';
    fb.style.color = 'var(--gris)'; fb.textContent = '';
    try {
      const res = await fetch(window.MW_API_BASE + '/api/facturas/' + window.MW_FACTURA_ID + '/email', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({ email })
      });
      const data = await res.json();
      if (res.ok && data.ok) {
        fb.style.color = '#2f855a'; fb.textContent = 'Enviado a ' + (data.email || email);
        btn.textContent = 'Enviado'; btn.style.background = '#2f855a';
      } else throw new Error(data.mensaje || 'Error al enviar');
    } catch (err) {
      fb.style.color = '#c53030'; fb.textContent = err.message;
      btn.disabled = false; btn.textContent = 'Enviar';
    }
  };

  // ── Reset ────────────────────────────────────────────────────────

  window.resetFactura = function() {
    enviando = false; currentStep = 1;
    document.getElementById('steps-indicator').style.display = 'flex';
    document.getElementById('success-screen').style.display = 'none';
    ['step-dot-1','step-dot-2','step-dot-3'].forEach(id => document.getElementById(id).classList.remove('active','done'));
    document.getElementById('step-dot-1').classList.add('active');
    document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
    document.getElementById('step1').classList.add('active');
    ['f-nombre','f-empresa','f-nif','f-email','f-direccion','f-cp','f-ciudad','f-fecha','f-importe','f-obs'].forEach(id => {
      const el = document.getElementById(id); if (el) el.value = '';
    });
    document.getElementById('acepto-lopd').checked = false;
    document.getElementById('pdf-download-wrap').style.display = 'none';
    document.getElementById('pdf-pending-wrap').style.display = 'none';
    const ews = document.getElementById('email-send-wrap');
    if (ews) ews.style.display = 'none';
    clearStep1Errors();
    ['nif-ok','nif-err'].forEach(id => document.getElementById(id).style.display='none');
    removeFile();
  };

  // ── Drag & drop ──────────────────────────────────────────────────

  const dropArea = document.getElementById('drop-area');
  dropArea.addEventListener('dragover',  e => { e.preventDefault(); dropArea.style.borderColor = 'var(--burdeos)'; });
  dropArea.addEventListener('dragleave', () => { dropArea.style.borderColor = ''; });
  dropArea.addEventListener('drop', e => {
    e.preventDefault(); dropArea.style.borderColor = '';
    const file = e.dataTransfer.files[0];
    if (file) { document.getElementById('file-input').files = e.dataTransfer.files; handleFile(document.getElementById('file-input')); }
  });
</script>
@endpush
