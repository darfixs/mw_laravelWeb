<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Panel Admin · Miss Whitney</title>
  <meta name="robots" content="noindex,nofollow"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<div class="mobile-overlay" id="mobile-overlay"></div>

<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <img src="{{ asset('images/logo.png') }}" alt="MW"/>
    <div class="brand">Miss Whitney <small>Panel Admin</small></div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-group-label">Principal</div>
    <div class="nav-item active" onclick="setView('facturas',this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      Facturas
      <span class="nav-badge" id="sidebar-badge">—</span>
    </div>
    <div class="nav-item" onclick="setView('dashboard',this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Resumen
    </div>

    <div class="nav-group-label" style="margin-top:.8rem">Web cliente</div>
    <a class="nav-item" href="{{ url('/') }}" target="_blank">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
      Ver web pública
      <svg style="margin-left:auto;width:12px;height:12px;opacity:.4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="topbar-user" style="margin-bottom:.3rem">
      <div class="avatar">A</div>
      <div>
        <strong>Admin</strong>
        <span style="font-size:.72rem;display:block">Miss Whitney</span>
      </div>
    </div>
    <span style="font-size:.72rem">© <span id="yr-side"></span> Miss Whitney</span>
  </div>
</aside>

<div class="main">

  <header class="topbar">
    <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Menú">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <span class="topbar-title">Panel Admin <span>· Miss Whitney</span></span>
    <div class="topbar-spacer"></div>
    <div class="topbar-user">
      <span style="display:none" id="topbar-clock"></span>
      <div class="avatar">A</div>
      <span>Admin</span>
    </div>
  </header>

  <div class="content" id="view-facturas">

    <div class="stats-row">
      <div class="stat-card">
        <div class="sc-label">Total facturas</div>
        <div class="sc-val" id="stat-total">—</div>
        <div class="sc-sub">en el registro</div>
        <span class="sc-ico"><i class="bi bi-receipt"></i></span>
      </div>
      <div class="stat-card">
        <div class="sc-label">Pendientes</div>
        <div class="sc-val" id="stat-pendiente" style="color:var(--s-pending)">—</div>
        <div class="sc-sub">requieren atención</div>
        <span class="sc-ico"><i class="bi bi-hourglass-split"></i></span>
      </div>
      <div class="stat-card">
        <div class="sc-label">Emitidas</div>
        <div class="sc-val" id="stat-emitida" style="color:var(--s-done)">—</div>
        <div class="sc-sub">completadas</div>
        <span class="sc-ico"><i class="bi bi-check-circle-fill"></i></span>
      </div>
      <div class="stat-card">
        <div class="sc-label">Importe facturado</div>
        <div class="sc-val" id="stat-importe" style="font-size:1.5rem">—</div>
        <div class="sc-sub">facturas emitidas</div>
        <span class="sc-ico"><i class="bi bi-cash-coin"></i></span>
      </div>
    </div>

    <div class="search-bar-wrap">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      <input
        type="search" id="search-input"
        placeholder="Buscar por ID, nombre, empresa, NIF, email, importe, estado, fecha…"
        autocomplete="off" spellcheck="false"
      />
      <span class="search-results-label" id="search-label"></span>
      <button class="search-clear" id="search-clear" aria-label="Limpiar búsqueda">✕</button>
    </div>

    <div class="filter-row">
      <button class="filter-chip active" onclick="setStatusFilter('all',this)">Todas</button>
      <button class="filter-chip" onclick="setStatusFilter('pendiente',this)"><i class="bi bi-hourglass-split"></i> Pendiente</button>
      <button class="filter-chip" onclick="setStatusFilter('procesando',this)"><i class="bi bi-arrow-repeat"></i> Procesando</button>
      <button class="filter-chip" onclick="setStatusFilter('emitida',this)"><i class="bi bi-check-circle-fill"></i> Emitida</button>
      <button class="filter-chip" onclick="setStatusFilter('cancelada',this)"><i class="bi bi-x-circle-fill"></i> Cancelada</button>
      <div class="filter-divider"></div>
      <label for="filter-month" style="font-size:.82rem;color:var(--gris);white-space:nowrap;align-self:center">Mes:</label>
      <input type="month" id="filter-month" oninput="setMonthFilter(this.value)" onchange="setMonthFilter(this.value)" style="padding:.42rem .6rem;border:1.5px solid #ddd;border-radius:8px;font-size:.85rem;background:#fff;color:var(--negro);cursor:pointer">
      <button id="btn-clear-month" onclick="clearMonthFilter()" title="Quitar filtro de mes" style="display:none;background:none;border:none;cursor:pointer;color:var(--gris);font-size:.85rem;padding:.2rem .4rem" aria-label="Quitar filtro de mes"><i class="bi bi-x-circle"></i></button>
      <button class="btn-ghost" id="btn-zip-mes" onclick="downloadZipMes()" style="display:none">
        <i class="bi bi-file-zip"></i> Descargar ZIP
      </button>
      <div class="filter-divider"></div>
      <button class="btn-ghost" onclick="exportCSV()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Exportar CSV
      </button>
      <button class="btn-primary" onclick="openCreate()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nueva factura
      </button>
    </div>

    <div class="table-wrap" id="table-area">
      <div class="table-head">
        <h3>Registro de facturas</h3>
        <div class="th-actions">
          <button class="btn-ghost" onclick="applyFilters()" title="Actualizar">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
            Actualizar
          </button>
        </div>
      </div>

      <div style="overflow-x:auto">
        <table class="factura-table">
          <thead>
            <tr>
              <th data-col="id" onclick="sortBy('id')">ID Factura <span class="sort-ico">⇅</span></th>
              <th data-col="fecha" onclick="sortBy('fecha')" class="sorted">Fecha <span class="sort-ico">↓</span></th>
              <th data-col="nombre" onclick="sortBy('nombre')">Cliente / Empresa <span class="sort-ico">⇅</span></th>
              <th data-col="nif" onclick="sortBy('nif')">NIF / CIF <span class="sort-ico">⇅</span></th>
              <th data-col="email" onclick="sortBy('email')">Email <span class="sort-ico">⇅</span></th>
              <th data-col="importe" onclick="sortBy('importe')">Importe <span class="sort-ico">⇅</span></th>
              <th data-col="atendido" onclick="sortBy('atendido')">Atendió <span class="sort-ico">⇅</span></th>
              <th data-col="estado" onclick="sortBy('estado')">Estado <span class="sort-ico">⇅</span></th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="tbody"></tbody>
        </table>
      </div>

      <div class="empty-state" id="empty-state">
        <div class="es-ico"><i class="bi bi-search"></i></div>
        <p>No se han encontrado facturas con esos criterios.<br>Prueba a cambiar la búsqueda o los filtros.</p>
      </div>

      <div class="pagination" id="pagination"></div>
    </div>
  </div>


  <div class="content" id="view-dashboard" style="display:none">
    <h2 style="font-size:2rem;margin-bottom:1.4rem">Resumen del negocio</h2>
    <div style="display:grid;gap:1.4rem;grid-template-columns:repeat(auto-fit,minmax(280px,1fr))">

      <div style="background:#fff;border-radius:16px;padding:1.6rem;box-shadow:var(--shadow);border:1px solid rgba(0,0,0,.04)">
        <h3 style="font-size:1.1rem;margin-bottom:1rem">Estado de solicitudes</h3>
        <div id="dash-status-bars"></div>
      </div>

      <div style="background:#fff;border-radius:16px;padding:1.6rem;box-shadow:var(--shadow);border:1px solid rgba(0,0,0,.04)">
        <h3 style="font-size:1.1rem;margin-bottom:1rem">Últimas solicitudes</h3>
        <div id="dash-recent"></div>
      </div>

      <div style="background:linear-gradient(135deg,var(--burdeos),var(--burdeos-osc));border-radius:16px;padding:1.6rem;color:#fff;box-shadow:0 8px 24px rgba(165,35,55,.28)">
        <h3 style="font-size:1rem;margin-bottom:.3rem;color:#fff;font-family:var(--font-body);font-weight:600">Total facturado (emitidas)</h3>
        <div id="dash-total" style="font-family:var(--font-display);font-size:2.8rem;font-weight:700;line-height:1;margin:.4rem 0"></div>
        <p style="font-size:.82rem;opacity:.75;margin:0" id="dash-total-sub"></p>
      </div>
    </div>
  </div>

</div>

<div class="modal-backdrop" id="modal-backdrop">
  <div class="modal">
    <button class="modal-close" onclick="closeModal()" aria-label="Cerrar">✕</button>
    <h3 id="modal-title">Nueva factura</h3>
    <p class="sub" id="modal-sub">Sube una foto del ticket para autocompletar, o rellena manualmente</p>

    <!-- Zona de subir ticket con OCR (solo en modo crear) -->
    <div id="m-ocr-zone" style="margin-bottom:1.2rem;padding:1rem;background:var(--perla);border-radius:10px;border:1.5px dashed #d8c8c8">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:.8rem;flex-wrap:wrap">
        <div>
          <strong style="font-size:.92rem;color:var(--negro)"><i class="bi bi-camera-fill"></i> Subir foto del ticket</strong>
          <p style="margin:.2rem 0 0;font-size:.78rem;color:var(--gris)">Detectaremos fecha, importe y conceptos automáticamente</p>
        </div>
        <label for="m-file-input" class="btn-ghost" style="padding:.55rem 1rem;font-size:.85rem;cursor:pointer;display:inline-flex;align-items:center;margin:0">
          Seleccionar imagen
        </label>
      </div>
      <input type="file" id="m-file-input" accept="image/jpeg,image/png,image/webp,image/jpg" style="position:absolute;left:-9999px;opacity:0;width:1px;height:1px" onchange="handleAdminOcr(this)"/>
      <div id="m-ocr-status" style="display:none;margin-top:.7rem;padding:.5rem .8rem;background:#fff;border-radius:6px;font-size:.82rem"></div>
    </div>

    <div class="modal-row">
      <div class="modal-field">
        <label for="m-id">ID Factura *</label>
        <input type="text" id="m-id" readonly style="background:var(--perla);color:var(--gris)"/>
      </div>
      <div class="modal-field">
        <label for="m-fecha">Fecha *</label>
        <input type="date" id="m-fecha"/>
      </div>
    </div>
    <div class="modal-field">
      <label for="m-nombre">Nombre del cliente *</label>
      <input type="text" id="m-nombre" placeholder="Apellidos, Nombre"/>
    </div>
    <div class="modal-field">
      <label for="m-empresa">Empresa (si aplica)</label>
      <input type="text" id="m-empresa" placeholder="Nombre de empresa o autónomo"/>
    </div>
    <div class="modal-row">
      <div class="modal-field">
        <label for="m-nif">NIF / CIF *</label>
        <input type="text" id="m-nif" placeholder="12345678A" maxlength="9"/>
      </div>
      <div class="modal-field">
        <label for="m-email">Email *</label>
        <input type="email" id="m-email" placeholder="cliente@correo.es"/>
      </div>
    </div>
    <div class="modal-row">
      <div class="modal-field">
        <label for="m-importe">Importe (€) *</label>
        <input type="number" id="m-importe" placeholder="0.00" min="0" step="0.01"/>
      </div>
      <div class="modal-field">
        <label for="m-estado">Estado *</label>
        <select id="m-estado">
          <option value="pendiente">Pendiente</option>
          <option value="procesando">Procesando</option>
          <option value="emitida">Emitida</option>
          <option value="cancelada">Cancelada</option>
        </select>
      </div>
    </div>

    <!-- Editor de líneas del ticket -->
    <div class="modal-field" style="margin-top:.5rem">
      <label style="display:flex;justify-content:space-between;align-items:center">
        <span>Líneas del ticket</span>
        <button type="button" onclick="agregarLinea()" style="background:none;border:1px solid var(--burdeos);color:var(--burdeos);padding:.25rem .6rem;border-radius:6px;font-size:.75rem;cursor:pointer;font-weight:600">+ Añadir línea</button>
      </label>
      <div id="m-lineas-wrap" style="display:flex;flex-direction:column;gap:.4rem;margin-top:.4rem">
        <!-- Las líneas se añaden dinámicamente -->
      </div>
      <p id="m-lineas-empty" style="margin:.5rem 0 0;font-size:.78rem;color:var(--gris);font-style:italic">Sin líneas. Si no añades líneas, el PDF mostrará el concepto genérico.</p>
    </div>

    <div class="modal-field">
      <label for="m-obs">Observaciones</label>
      <input type="text" id="m-obs" placeholder="Mesa, hora aproximada, notas…"/>
    </div>
    <div class="modal-actions">
      <button class="btn-ghost" onclick="closeModal()">Cancelar</button>
      <button class="btn-primary" onclick="saveModal()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Guardar
      </button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
  window.MW_API_BASE = '{{ rtrim(url("/"), "/") }}';
</script>
<script src="{{ asset('js/admin.js') }}?v={{ filemtime(public_path('js/admin.js')) }}"></script>
<script>
  document.getElementById('yr-side').textContent = new Date().getFullYear();

  const clk = document.getElementById('topbar-clock');
  function tick() { clk.textContent = new Date().toLocaleTimeString('es-ES',{hour:'2-digit',minute:'2-digit'}) }
  tick(); setInterval(tick, 10000);

  setTimeout(() => {
    const pending = STATE.facturas.filter(f => f.estado === 'pendiente').length;
    document.getElementById('sidebar-badge').textContent = pending;
  }, 100);

  function setView(view, navItem) {
    ['facturas','dashboard'].forEach(v => {
      document.getElementById('view-'+v).style.display = v === view ? 'block' : 'none';
    });
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    navItem.classList.add('active');
    if (view === 'dashboard') renderDashboard();
  }

  function renderDashboard() {
    const all = STATE.facturas;
    const byStatus = { pendiente:0, procesando:0, emitida:0, cancelada:0 };
    all.forEach(f => { if (byStatus[f.estado] !== undefined) byStatus[f.estado]++ });
    const colors = { pendiente:'var(--s-pending)', procesando:'var(--s-processing)', emitida:'var(--s-done)', cancelada:'var(--s-cancelled)' };
    const labels = { pendiente:'Pendiente', procesando:'Procesando', emitida:'Emitida', cancelada:'Cancelada' };
    const maxV = Math.max(...Object.values(byStatus), 1);

    document.getElementById('dash-status-bars').innerHTML = Object.entries(byStatus).map(([k,v]) => `
      <div style="margin-bottom:.9rem">
        <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:.3rem">
          <span style="font-weight:600;color:var(--negro)">${labels[k]}</span>
          <span style="color:var(--gris)">${v}</span>
        </div>
        <div style="background:var(--gris-claro);border-radius:999px;height:8px;overflow:hidden">
          <div style="height:100%;width:${(v/maxV*100).toFixed(1)}%;background:${colors[k]};border-radius:999px;transition:width .6s ease"></div>
        </div>
      </div>
    `).join('');

    const recent = [...all].sort((a,b) => new Date(b.fecha) - new Date(a.fecha)).slice(0,5);
    document.getElementById('dash-recent').innerHTML = recent.map(f => `
      <div style="display:flex;align-items:center;gap:.6rem;padding:.5rem 0;border-bottom:1px solid var(--gris-claro);font-size:.84rem">
        <span class="status-badge status-${f.estado}" style="flex-shrink:0">${labels[f.estado]}</span>
        <div style="flex:1;min-width:0">
          <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${f.empresa || f.nombre}</div>
          <div style="color:var(--gris);font-size:.76rem">${new Date(f.fecha+'T00:00:00').toLocaleDateString('es-ES',{day:'2-digit',month:'short'})}</div>
        </div>
        <span style="font-family:var(--font-display);font-weight:700;color:var(--burdeos-osc);flex-shrink:0">${parseFloat(f.importe||0).toFixed(2)}€</span>
      </div>
    `).join('');

    const total = all.filter(f => f.estado === 'emitida').reduce((s,f) => s+parseFloat(f.importe||0), 0);
    document.getElementById('dash-total').textContent = total.toLocaleString('es-ES',{minimumFractionDigits:2}) + ' €';
    document.getElementById('dash-total-sub').textContent = `${byStatus.emitida} factura${byStatus.emitida!==1?'s':''} emitida${byStatus.emitida!==1?'s':''}`;
  }

  document.getElementById('modal-backdrop').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeModal();
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModal();
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault(); document.getElementById('search-input').focus();
    }
  });
</script>
</body>
</html>
