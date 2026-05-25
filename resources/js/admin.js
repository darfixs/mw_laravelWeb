/* ===== Miss Whitney · Admin Panel JS =====
   Todas las operaciones van contra la API REST del backend.
   Endpoint base: /api  (configurable en API_BASE abajo)
   ================================================== */

// Ruta base desde la carpeta admin/ hacia la raíz del proyecto
// Si el proyecto está en la raíz del servidor: '../api'
// Si está en una subcarpeta, p.ej. /misswhitney/: '../api'
const API_BASE = (window.MW_API_BASE || '') + '/api';

/* ── State ── */
const STATE = {
  facturas:     [],     // datos cargados desde la API
  filtered:     [],     // resultado del filtrado local
  query:        '',
  statusFilter: 'all',
  sortCol:      'fecha_solicitud',
  sortDir:      'desc',
  page:         1,
  perPage:      10,
  editId:       null,
  loading:      false,
};

/* ── Boot ── */
document.addEventListener('DOMContentLoaded', () => {
  loadFacturas();
  initSearch();
  initMobileMenu();
});

/* ──────────────────────────────────────────
   API CALLS
   Cada función habla con un endpoint REST.
   El backend debe devolver JSON con la forma
   descrita en los comentarios.
─────────────────────────────────────────── */

/**
 * GET /api/facturas
 * Devuelve: array de objetos con las columnas de v_panel_facturas
 */
async function loadFacturas() {
  setLoading(true);
  try {
    const res = await fetch(`${API_BASE}/facturas`);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    STATE.facturas = await res.json();
    refreshStats();
    applyFilters();
    updateSidebarBadge();
  } catch (err) {
    console.error('Error al cargar facturas:', err);
    showToast('No se pudieron cargar las facturas. Comprueba la conexión.', 'error');
    renderEmptyWithError();
  } finally {
    setLoading(false);
  }
}

/**
 * POST /api/facturas
 * Body: { fecha_consumo, nombre_cliente, empresa, nif_cif, email, importe, estado, obs_cliente }
 * Devuelve: el objeto creado con su id y numero_factura generado
 */
async function apiCreate(data) {
  const res = await fetch(`${API_BASE}/facturas`, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
    body:    JSON.stringify(data),
  });
  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.mensaje || `HTTP ${res.status}`);
  }
  return res.json();
}

/**
 * PUT /api/facturas/:id
 * Body: campos a actualizar
 * Devuelve: el objeto actualizado
 */
/**
 * POST api/facturas/actualizar.php?id=X
 * Usamos POST en vez de PUT para evitar problemas con XAMPP/Windows
 */
async function apiUpdate(id, data) {
  const res = await fetch(`${API_BASE}/facturas/${id}`, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
    body:    JSON.stringify({ ...data, id: id }),
  });
  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.mensaje || `HTTP ${res.status}`);
  }
  return res.json();
}

/**
 * POST api/facturas/cambiar_estado.php?id=X
 */
async function apiChangeStatus(id, estado) {
  const res = await fetch(`${API_BASE}/facturas/${id}/estado`, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
    body:    JSON.stringify({ id: id, estado }),
  });
  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.mensaje || `HTTP ${res.status}`);
  }
  return res.json();
}

/**
 * POST api/facturas/eliminar.php?id=X
 */
async function apiDelete(id) {
  const res = await fetch(`${API_BASE}/facturas/${id}/delete`, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
    body:    JSON.stringify({ id: id }),
  });
  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.mensaje || `HTTP ${res.status}`);
  }
}

/* ──────────────────────────────────────────
   BÚSQUEDA Y FILTROS (operan sobre datos ya cargados)
─────────────────────────────────────────── */

function initSearch() {
  const input = document.getElementById('search-input');
  const clear = document.getElementById('search-clear');
  input.addEventListener('input', () => {
    STATE.query = input.value.trim();
    STATE.page  = 1;
    clear.classList.toggle('visible', !!STATE.query);
    applyFilters();
  });
  clear.addEventListener('click', () => {
    input.value = ''; STATE.query = '';
    clear.classList.remove('visible');
    STATE.page = 1;
    applyFilters();
    input.focus();
  });
}

function setStatusFilter(val, btn) {
  STATE.statusFilter = val; STATE.page = 1;
  document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
  btn.classList.add('active');
  applyFilters();
}

function applyFilters() {
  const q = STATE.query.toLowerCase();
  STATE.filtered = STATE.facturas.filter(f => {
    const matchStatus = STATE.statusFilter === 'all' || f.estado === STATE.statusFilter;
    const haystack = [
      f.numero_factura, f.referencia_solicitud,
      f.nombre_display, f.nombre_cliente, f.empresa,
      f.nif_cif, f.email, f.importe, f.estado,
      f.fecha_solicitud, f.fecha_consumo, f.obs_cliente,
    ].join(' ').toLowerCase();
    const matchQ = !q || haystack.includes(q);
    return matchStatus && matchQ;
  });
  sortFiltered();
  renderTable();
  updateSearchLabel();
}

/* ──────────────────────────────────────────
   ORDENACIÓN
─────────────────────────────────────────── */

function sortBy(col) {
  if (STATE.sortCol === col) STATE.sortDir = STATE.sortDir === 'asc' ? 'desc' : 'asc';
  else { STATE.sortCol = col; STATE.sortDir = 'asc'; }
  STATE.page = 1;
  sortFiltered();
  renderTable();
  updateSortHeaders();
}

function sortFiltered() {
  const { sortCol: col, sortDir: dir } = STATE;
  STATE.filtered.sort((a, b) => {
    let av = a[col] ?? '', bv = b[col] ?? '';
    if (col === 'importe') { av = parseFloat(av); bv = parseFloat(bv); }
    if (['fecha_solicitud','fecha_consumo','fecha_emision'].includes(col)) {
      av = new Date(av); bv = new Date(bv);
    }
    if (av < bv) return dir === 'asc' ? -1 : 1;
    if (av > bv) return dir === 'asc' ?  1 : -1;
    return 0;
  });
}

function updateSortHeaders() {
  document.querySelectorAll('th[data-col]').forEach(th => {
    th.classList.remove('sorted');
    const ico = th.querySelector('.sort-ico');
    if (ico) ico.textContent = '⇅';
  });
  const active = document.querySelector(`th[data-col="${STATE.sortCol}"]`);
  if (active) {
    active.classList.add('sorted');
    const ico = active.querySelector('.sort-ico');
    if (ico) ico.textContent = STATE.sortDir === 'asc' ? '↑' : '↓';
  }
}

/* ──────────────────────────────────────────
   RENDERIZADO DE TABLA
─────────────────────────────────────────── */

function renderTable() {
  const tbody = document.getElementById('tbody');
  const empty = document.getElementById('empty-state');
  const total = STATE.filtered.length;

  if (total === 0) {
    tbody.innerHTML = '';
    empty.style.display = 'block';
    renderPagination(0);
    return;
  }
  empty.style.display = 'none';

  const start = (STATE.page - 1) * STATE.perPage;
  const slice = STATE.filtered.slice(start, start + STATE.perPage);
  const q     = STATE.query.toLowerCase();

  tbody.innerHTML = slice.map(f => {
    const hl = (str) => {
      if (!q || str == null) return escHtml(str ?? '');
      const re = new RegExp(`(${escReg(q)})`, 'gi');
      return escHtml(String(str)).replace(re, '<mark class="hl">$1</mark>');
    };

    const displayName = f.empresa ? f.empresa : f.nombre_cliente;
    const sub = f.empresa
      ? `<span style="display:block;font-size:.78rem;color:var(--gris);font-weight:400">${escHtml(f.nombre_cliente ?? '')}</span>`
      : '';
    const fechaStr = f.fecha_solicitud
      ? new Date(f.fecha_solicitud).toLocaleDateString('es-ES', { day:'2-digit', month:'short', year:'numeric' })
      : '—';
    const importeStr = f.importe != null ? parseFloat(f.importe||0).toFixed(2) + ' €' : '—';

    return `
      <tr data-id="${escHtml(f.id)}">
        <td class="td-id">${hl(f.numero_factura)}</td>
        <td>${hl(fechaStr)}</td>
        <td class="td-name">${hl(displayName)}${sub}</td>
        <td>${hl(f.nif_cif)}</td>
        <td>${hl(f.email)}</td>
        <td class="td-amount">${hl(importeStr)}</td>
        <td><span class="status-badge status-${f.estado}">${ucFirst(f.estado)}</span></td>
        <td>
      <div class="row-actions">
        ${f.id
          ? `<button class="row-btn" onclick="openEdit('${f.id}')" title="Editar">✏️</button>
             <button class="row-btn" onclick="promptChangeStatus('${f.id}')" title="Cambiar estado">🔄</button>
             <a class="row-btn" href="${window.MW_API_BASE}/api/facturas/${f.id}/pdf?modo=descargar" title="Descargar PDF">📄</a>
             <button class="row-btn" onclick="enviarFacturaEmail('${f.id}', '${escHtml(f.email || '')}')" title="Enviar por email">✉️</button>
             <button class="row-btn danger" onclick="deleteFactura('${f.id}')" title="Eliminar">🗑️</button>`
          : `<button class="row-btn" style="color:var(--s-pending);border-color:var(--s-pending)" onclick="crearFacturaDesde('${escHtml(f.referencia_solicitud)}')" title="Crear factura para esta solicitud">＋ Crear factura</button>`
        }
      </div>
        </td>
      </tr>`;
  }).join('');

  renderPagination(total);
  updateSortHeaders();
}

/* ──────────────────────────────────────────
   PAGINACIÓN
─────────────────────────────────────────── */

function renderPagination(total) {
  const pages = Math.ceil(total / STATE.perPage);
  const pg    = document.getElementById('pagination');
  if (pages <= 1) { pg.innerHTML = ''; return; }

  const start = (STATE.page - 1) * STATE.perPage + 1;
  const end   = Math.min(STATE.page * STATE.perPage, total);
  let html    = `<span class="pg-info">${start}–${end} de ${total}</span>`;
  html += `<button class="pg-btn" onclick="goPage(${STATE.page - 1})" ${STATE.page === 1 ? 'disabled' : ''}>‹</button>`;
  for (let i = 1; i <= pages; i++) {
    if (pages > 7 && Math.abs(i - STATE.page) > 2 && i !== 1 && i !== pages) {
      if (i === STATE.page - 3 || i === STATE.page + 3) html += `<span class="pg-info">…</span>`;
      continue;
    }
    html += `<button class="pg-btn ${i === STATE.page ? 'active' : ''}" onclick="goPage(${i})">${i}</button>`;
  }
  html += `<button class="pg-btn" onclick="goPage(${STATE.page + 1})" ${STATE.page === pages ? 'disabled' : ''}>›</button>`;
  pg.innerHTML = html;
}

function goPage(n) {
  const pages = Math.ceil(STATE.filtered.length / STATE.perPage);
  if (n < 1 || n > pages) return;
  STATE.page = n;
  renderTable();
  document.getElementById('table-area').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/* ──────────────────────────────────────────
   ESTADÍSTICAS
─────────────────────────────────────────── */

function refreshStats() {
  const all = STATE.facturas;
  document.getElementById('stat-total').textContent     = all.length;
  document.getElementById('stat-pendiente').textContent = all.filter(f => f.estado === 'pendiente').length;
  document.getElementById('stat-emitida').textContent   = all.filter(f => f.estado === 'emitida').length;
  const totalEur = all
    .filter(f => f.estado === 'emitida')
    .reduce((s, f) => s + parseFloat(f.importe || 0), 0);
  document.getElementById('stat-importe').textContent =
    totalEur.toLocaleString('es-ES', { minimumFractionDigits: 2 }) + ' €';
}

function updateSidebarBadge() {
  const pending = STATE.facturas.filter(f => f.estado === 'pendiente').length;
  const badge = document.getElementById('sidebar-badge');
  if (badge) badge.textContent = pending;
}

function updateSearchLabel() {
  const lbl = document.getElementById('search-label');
  lbl.textContent = STATE.query
    ? `${STATE.filtered.length} resultado${STATE.filtered.length !== 1 ? 's' : ''} para "${STATE.query}"`
    : '';
}

/* ──────────────────────────────────────────
   CRUD: CREAR (manual desde admin)
─────────────────────────────────────────── */

function openCreate() {
  STATE.editId = null;
  document.getElementById('modal-title').textContent = 'Nueva factura';
  document.getElementById('modal-sub').textContent   = 'Añade una factura manual al registro.';
  clearModalForm();
  document.getElementById('m-id').value    = '(se generará automáticamente)';
  document.getElementById('m-id').readOnly = true;
  openModal();
}

/* ──────────────────────────────────────────
   CRUD: EDITAR
─────────────────────────────────────────── */

function openEdit(id) {
  const f = STATE.facturas.find(x => String(x.id) === String(id));
  if (!f) return;
  STATE.editId = id;

  document.getElementById('modal-title').textContent = 'Editar factura';
  document.getElementById('modal-sub').textContent   = `Modificando ${f.numero_factura}`;
  document.getElementById('m-id').value      = f.numero_factura ?? '';
  document.getElementById('m-id').readOnly   = true;
  document.getElementById('m-fecha').value   = (f.fecha_consumo ?? '').slice(0, 10);
  document.getElementById('m-nombre').value  = f.nombre_cliente  ?? '';
  document.getElementById('m-empresa').value = f.empresa         ?? '';
  document.getElementById('m-nif').value     = f.nif_cif         ?? '';
  document.getElementById('m-email').value   = f.email           ?? '';
  document.getElementById('m-importe').value = f.importe         ?? '';
  document.getElementById('m-estado').value  = f.estado          ?? 'pendiente';
  document.getElementById('m-obs').value     = f.obs_cliente     ?? '';
  openModal();
}

/* ──────────────────────────────────────────
   CRUD: GUARDAR modal (crear o actualizar)
─────────────────────────────────────────── */

async function saveModal() {
  const fecha   = document.getElementById('m-fecha').value;
  const nombre  = document.getElementById('m-nombre').value.trim();
  const nif     = document.getElementById('m-nif').value.trim();
  const email   = document.getElementById('m-email').value.trim();
  const importe = parseFloat(document.getElementById('m-importe').value);
  const estado  = document.getElementById('m-estado').value;

  if (!fecha || !nombre || !nif || !email || isNaN(importe)) {
    showToast('Rellena todos los campos obligatorios', 'error'); return;
  }

  const payload = {
    fecha_consumo:  fecha,
    nombre_cliente: nombre,
    empresa:        document.getElementById('m-empresa').value.trim() || null,
    nif_cif:        nif,
    email,
    importe,
    estado,
    obs_cliente:    document.getElementById('m-obs').value.trim() || null,
  };

  try {
    if (STATE.editId) {
      const updated = await apiUpdate(STATE.editId, payload);
      const idx = STATE.facturas.findIndex(f => String(f.id) === String(STATE.editId));
      if (idx > -1) STATE.facturas[idx] = { ...STATE.facturas[idx], ...updated };
      showToast('Factura actualizada', 'success');
    } else {
      const created = await apiCreate(payload);
      STATE.facturas.unshift(created);
      showToast(`Factura ${created.numero_factura} creada`, 'success');
    }
    closeModal();
    refreshStats();
    updateSidebarBadge();
    applyFilters();
  } catch (err) {
    showToast('Error al guardar: ' + err.message, 'error');
  }
}

/* ──────────────────────────────────────────
   CRUD: CAMBIAR ESTADO (botón rápido en tabla)
─────────────────────────────────────────── */

async function promptChangeStatus(id) {
  const f = STATE.facturas.find(x => String(x.id) === String(id));
  if (!f) return;
  const cycle = { pendiente: 'procesando', procesando: 'emitida', emitida: 'pendiente', cancelada: 'pendiente' };
  const nuevoEstado = cycle[f.estado] || 'pendiente';

  try {
    await apiChangeStatus(id, nuevoEstado);
    f.estado = nuevoEstado;
    refreshStats();
    updateSidebarBadge();
    applyFilters();
    showToast(`Estado → "${ucFirst(nuevoEstado)}"`, 'success');
  } catch (err) {
    showToast('Error: ' + err.message, 'error');
  }
}

/* ──────────────────────────────────────────
   CRUD: ELIMINAR
─────────────────────────────────────────── */

async function deleteFactura(id) {
  const f   = STATE.facturas.find(x => String(x.id) === String(id));
  const ref = f ? f.numero_factura : id;
  if (!confirm(`¿Eliminar la factura ${ref}?\nEsta acción no se puede deshacer.`)) return;

  try {
    await apiDelete(id);
    STATE.facturas = STATE.facturas.filter(x => String(x.id) !== String(id));
    refreshStats();
    updateSidebarBadge();
    applyFilters();
    showToast(`Factura ${ref} eliminada`, '');
  } catch (err) {
    showToast('Error al eliminar: ' + err.message, 'error');
  }
}

/* ──────────────────────────────────────────
   Crear factura desde una solicitud sin factura
   (para solicitudes antiguas o importadas)
─────────────────────────────────────────── */

/* ─────────────────────────────────────────────
   Enviar factura por email al receptor
   Permite cambiar el destinatario en un prompt
─────────────────────────────────────────── */

async function enviarFacturaEmail(id, emailPredeterminado) {
  const destino = prompt(
    'Email destino:\n\n(deja vacío para usar el del receptor)',
    emailPredeterminado || ''
  );

  // Cancelado por el usuario
  if (destino === null) return;

  const emailFinal = (destino || '').trim();

  // Si lo dejó vacío y no había predeterminado, abortamos
  if (!emailFinal && !emailPredeterminado) {
    showToast('No hay email destino', 'error');
    return;
  }

  // Validación básica
  if (emailFinal && !emailFinal.includes('@')) {
    showToast('Email no válido', 'error');
    return;
  }

  try {
    const res = await fetch(`${API_BASE}/facturas/${id}/email`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
      },
      body: JSON.stringify({ email: emailFinal || emailPredeterminado || '' }),
    });
    const data = await res.json();
    if (!res.ok || !data.ok) throw new Error(data.mensaje || `HTTP ${res.status}`);
    showToast('✓ Factura enviada a ' + (data.email || emailFinal), 'ok');
  } catch (err) {
    showToast('Error al enviar: ' + err.message, 'error');
  }
}

async function crearFacturaDesde(referencia) {
  const sol = STATE.facturas.find(f => f.referencia_solicitud === referencia);
  if (!sol) { showToast('Solicitud no encontrada', 'error'); return; }

  const payload = {
    fecha_consumo:  (sol.fecha_consumo  ?? '').slice(0, 10),
    nombre_cliente: sol.nombre_cliente  ?? '',
    empresa:        sol.empresa         ?? null,
    nif_cif:        sol.nif_cif         ?? '',
    email:          sol.email           ?? '',
    importe:        sol.importe         ?? 0,
    estado:         'pendiente',
    obs_cliente:    sol.obs_cliente     ?? null,
    referencia_solicitud: referencia,
  };

  try {
    const created = await apiCreate(payload);
    // Actualizar la fila local con el id real
    const idx = STATE.facturas.findIndex(f => f.referencia_solicitud === referencia);
    if (idx > -1) STATE.facturas[idx] = { ...STATE.facturas[idx], ...created };
    refreshStats();
    updateSidebarBadge();
    applyFilters();
    showToast(`Factura ${created.numero_factura} creada`, 'success');
  } catch (err) {
    showToast('Error al crear factura: ' + err.message, 'error');
  }
}

function exportCSV() {
  const cols = [
    ['numero_factura',      'ID Factura'],
    ['fecha_solicitud',     'Fecha solicitud'],
    ['fecha_consumo',       'Fecha consumo'],
    ['nombre_cliente',      'Nombre cliente'],
    ['empresa',             'Empresa'],
    ['nif_cif',             'NIF/CIF'],
    ['email',               'Email'],
    ['importe',             'Importe (€)'],
    ['estado',              'Estado'],
    ['obs_cliente',         'Observaciones'],
  ];
  const header = cols.map(c => c[1]);
  const rows   = STATE.filtered.map(f => cols.map(c => f[c[0]] ?? ''));
  const csv    = [header, ...rows]
    .map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`).join(','))
    .join('\n');
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8' });
  const a    = document.createElement('a');
  a.href     = URL.createObjectURL(blob);
  a.download = `facturas_misswhitney_${new Date().toISOString().slice(0, 10)}.csv`;
  a.click();
  URL.revokeObjectURL(a.href);
  showToast(`${STATE.filtered.length} registros exportados`, 'success');
}

/* ──────────────────────────────────────────
   MODAL HELPERS
─────────────────────────────────────────── */

function openModal() {
  document.getElementById('modal-backdrop').classList.add('open');
  setTimeout(() => document.getElementById('m-nombre').focus(), 80);
}
function closeModal() {
  document.getElementById('modal-backdrop').classList.remove('open');
  STATE.editId = null;
}
function clearModalForm() {
  ['m-fecha','m-nombre','m-empresa','m-nif','m-email','m-importe','m-obs'].forEach(id => {
    const el = document.getElementById(id); if (el) el.value = '';
  });
  document.getElementById('m-estado').value = 'pendiente';
  const now = new Date(); const pad = n => String(n).padStart(2, '0');
  document.getElementById('m-fecha').value =
    `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
}

/* ──────────────────────────────────────────
   MOBILE SIDEBAR
─────────────────────────────────────────── */

function initMobileMenu() {
  document.getElementById('mobile-menu-btn').addEventListener('click', () => {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('mobile-overlay').classList.add('show');
  });
  document.getElementById('mobile-overlay').addEventListener('click', closeSidebar);
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('mobile-overlay').classList.remove('show');
}

/* ──────────────────────────────────────────
   LOADING / ERROR STATES
─────────────────────────────────────────── */

function setLoading(on) {
  STATE.loading = on;
  if (on) {
    document.getElementById('tbody').innerHTML = `
      <tr><td colspan="8" style="text-align:center;padding:3rem;color:var(--gris)">
        <span style="display:inline-flex;align-items:center;gap:.6rem">
          <span style="display:inline-block;animation:spin 1s linear infinite">⏳</span>
          Cargando facturas…
        </span>
      </td></tr>`;
  }
}
function renderEmptyWithError() {
  document.getElementById('tbody').innerHTML = `
    <tr><td colspan="8" style="text-align:center;padding:3rem;color:var(--gris)">
      ⚠️ No se pudieron cargar los datos.<br>
      <span style="font-size:.82rem">Verifica que el servidor está en marcha.</span><br><br>
      <button class="btn-ghost" onclick="loadFacturas()">Reintentar</button>
    </td></tr>`;
}

/* ──────────────────────────────────────────
   TOAST
─────────────────────────────────────────── */

function showToast(msg, type = '') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className   = 'toast show ' + type;
  clearTimeout(t._to);
  t._to = setTimeout(() => t.classList.remove('show'), 3200);
}

/* ──────────────────────────────────────────
   UTILIDADES
─────────────────────────────────────────── */

function escHtml(s) {
  return String(s)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escReg(s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') }
function ucFirst(s) { return s ? s[0].toUpperCase() + s.slice(1) : '' }
