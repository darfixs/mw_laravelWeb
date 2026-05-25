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
  monthFilter:  '',     // YYYY-MM — filtra por fecha_consumo
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
    const matchMonth  = !STATE.monthFilter || String(f.fecha_consumo).slice(0, 7) === STATE.monthFilter;
    const haystack = [
      f.numero_factura, f.referencia_solicitud,
      f.nombre_display, f.nombre_cliente, f.empresa,
      f.nif_cif, f.email, f.importe, f.estado, f.atendido_por,
      f.fecha_solicitud, f.fecha_consumo, f.obs_cliente,
    ].join(' ').toLowerCase();
    const matchQ = !q || haystack.includes(q);
    return matchStatus && matchMonth && matchQ;
  });
  sortFiltered();
  renderTable();
  updateSearchLabel();
}

function setMonthFilter(val) {
  STATE.monthFilter = val || '';
  STATE.page = 1;
  const btnZip   = document.getElementById('btn-zip-mes');
  const btnClear = document.getElementById('btn-clear-month');
  if (btnZip)   btnZip.style.display   = val ? 'inline-flex' : 'none';
  if (btnClear) btnClear.style.display = val ? 'inline-flex' : 'none';
  applyFilters();
}

function clearMonthFilter() {
  const input = document.getElementById('filter-month');
  if (input) input.value = '';
  setMonthFilter('');
}

async function downloadZipMes() {
  if (!STATE.monthFilter) return;
  const [year, month] = STATE.monthFilter.split('-');
  const url = `${API_BASE}/facturas/zip?year=${year}&month=${month}`;
  try {
    const res = await fetch(url, {
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
    });
    if (!res.ok) {
      const data = await res.json().catch(() => ({}));
      showToast(data.mensaje || 'Error al generar el ZIP', 'error');
      return;
    }
    const blob = await res.blob();
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `facturas-${STATE.monthFilter}.zip`;
    a.click();
    URL.revokeObjectURL(a.href);
  } catch (err) {
    showToast('Error al descargar el ZIP: ' + err.message, 'error');
  }
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
        <td>${hl(f.atendido_por || '—')}</td>
        <td><span class="status-badge status-${f.estado}">${ucFirst(f.estado)}</span></td>
        <td>
      <div class="row-actions">
        ${f.id
          ? `<button class="row-btn" onclick="openEdit('${f.id}')" title="Editar"><i class="bi bi-pencil-fill"></i></button>
             <button class="row-btn" onclick="promptChangeStatus('${f.id}')" title="Cambiar estado"><i class="bi bi-arrow-repeat"></i></button>
             <a class="row-btn" href="${window.MW_API_BASE}/api/facturas/${f.id}/pdf?modo=descargar" title="Descargar PDF"><i class="bi bi-file-earmark-pdf-fill"></i></a>
             <button class="row-btn" onclick="enviarFacturaEmail('${f.id}', '${escHtml(f.email || '')}')" title="Enviar por email"><i class="bi bi-envelope-fill"></i></button>
             <button class="row-btn danger" onclick="deleteFactura('${f.id}')" title="Eliminar"><i class="bi bi-trash3-fill"></i></button>`
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
  STATE.lineas = [];
  document.getElementById('modal-title').textContent = 'Nueva factura';
  document.getElementById('modal-sub').textContent   = 'Sube una foto del ticket para autocompletar, o rellena manualmente.';
  clearModalForm();
  document.getElementById('m-id').value    = '(se generará automáticamente)';
  document.getElementById('m-id').readOnly = true;
  // Mostrar zona OCR (solo en crear)
  const ocrZone = document.getElementById('m-ocr-zone');
  if (ocrZone) ocrZone.style.display = 'block';
  renderLineas();
  openModal();
}

/* ──────────────────────────────────────────
   OCR: procesar ticket desde admin
─────────────────────────────────────────── */

async function handleAdminOcr(input) {
  const file = input.files[0];
  if (!file) return;
  if (!file.type.startsWith('image/')) {
    showToast('Solo se permiten imágenes (JPG, PNG, WEBP)', 'error');
    input.value = '';
    return;
  }

  const status = document.getElementById('m-ocr-status');
  status.style.display = 'block';
  status.style.color = 'var(--burdeos)';
  status.innerHTML = '<i class="bi bi-hourglass-split"></i> Analizando ticket...';

  try {
    const fd = new FormData();
    fd.append('ticket', file);
    const res = await fetch(`${window.MW_API_BASE}/api/ocr/ticket`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
      body: fd
    });
    const data = await res.json();
    if (!res.ok || !data.ok) {
      throw new Error(data.mensaje || 'No se pudo leer el ticket');
    }

    const detectados = [];
    if (data.fecha) {
      document.getElementById('m-fecha').value = data.fecha;
      detectados.push('fecha');
    }
    if (data.total) {
      document.getElementById('m-importe').value = data.total.toFixed(2);
      detectados.push('importe');
    }
    if (data.lineas && data.lineas.length > 0) {
      STATE.lineas = data.lineas.slice();
      renderLineas();
      detectados.push(data.lineas.length + ' línea(s)');
    }

    if (detectados.length > 0) {
      status.style.color = '#2f855a';
      status.innerHTML = '<i class="bi bi-check-circle-fill"></i> Ticket leído (' + detectados.join(', ') + ')';
    } else {
      status.style.color = '#a07000';
      status.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> No se detectaron datos. Rellena manualmente.';
    }
  } catch (err) {
    status.style.color = '#c53030';
    status.innerHTML = '<i class="bi bi-x-circle-fill"></i> ' + err.message;
  }
}

/* ──────────────────────────────────────────
   Editor de líneas del ticket
─────────────────────────────────────────── */

function renderLineas() {
  const wrap = document.getElementById('m-lineas-wrap');
  const empty = document.getElementById('m-lineas-empty');
  if (!wrap) return;
  wrap.innerHTML = '';
  if (!STATE.lineas || STATE.lineas.length === 0) {
    if (empty) empty.style.display = 'block';
    desbloquearImporte();
    return;
  }
  if (empty) empty.style.display = 'none';
  STATE.lineas.forEach((l, i) => {
    const row = document.createElement('div');
    row.style.cssText = 'display:grid;grid-template-columns:60px 1fr 90px 32px;gap:.4rem;align-items:center';
    row.innerHTML = `
      <input type="number" min="1" value="${l.cantidad ?? 1}" onchange="STATE.lineas[${i}].cantidad=parseInt(this.value)||1;recalcularTotalDesdeLineas()"
             style="padding:.45rem .5rem;border:1px solid #e0d8d8;border-radius:6px;font-size:.85rem"/>
      <input type="text" value="${(l.concepto ?? '').replace(/"/g,'&quot;')}" onchange="STATE.lineas[${i}].concepto=this.value"
             placeholder="Descripción" style="padding:.45rem .6rem;border:1px solid #e0d8d8;border-radius:6px;font-size:.85rem"/>
      <input type="number" step="0.01" min="0" value="${(l.importe ?? 0).toFixed(2)}" onchange="STATE.lineas[${i}].importe=parseFloat(this.value)||0;recalcularTotalDesdeLineas()"
             style="padding:.45rem .5rem;border:1px solid #e0d8d8;border-radius:6px;font-size:.85rem;text-align:right"/>
      <button type="button" onclick="quitarLinea(${i})" title="Quitar"
             style="background:none;border:none;color:#c53030;cursor:pointer;font-size:1.1rem;padding:0"><i class="bi bi-x-lg"></i></button>
    `;
    wrap.appendChild(row);
  });
  // Cuando hay líneas, el importe se calcula y se bloquea
  bloquearImporte();
  recalcularTotalDesdeLineas();
}

function recalcularTotalDesdeLineas() {
  if (!STATE.lineas || STATE.lineas.length === 0) return;
  const total = STATE.lineas.reduce((s, l) => s + (parseFloat(l.importe) || 0), 0);
  const inp = document.getElementById('m-importe');
  if (inp) inp.value = total.toFixed(2);
}

function bloquearImporte() {
  const inp = document.getElementById('m-importe');
  if (!inp) return;
  inp.readOnly = true;
  inp.style.background = 'var(--perla)';
  inp.style.cursor = 'not-allowed';
  inp.title = 'Calculado automáticamente desde las líneas';
}

function desbloquearImporte() {
  const inp = document.getElementById('m-importe');
  if (!inp) return;
  inp.readOnly = false;
  inp.style.background = '';
  inp.style.cursor = '';
  inp.title = '';
}

function agregarLinea() {
  if (!STATE.lineas) STATE.lineas = [];
  STATE.lineas.push({ cantidad: 1, concepto: '', importe: 0 });
  renderLineas();
}

function quitarLinea(i) {
  STATE.lineas.splice(i, 1);
  renderLineas();
}

/* ──────────────────────────────────────────
   CRUD: EDITAR
─────────────────────────────────────────── */

function openEdit(id) {
  const f = STATE.facturas.find(x => String(x.id) === String(id));
  if (!f) return;
  STATE.editId = id;
  STATE.lineas = Array.isArray(f.lineas_ticket) ? f.lineas_ticket.map(l => ({...l})) : [];

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
  // Ocultar zona OCR en modo edición (no debería re-leer)
  const ocrZone = document.getElementById('m-ocr-zone');
  if (ocrZone) ocrZone.style.display = 'none';
  renderLineas();
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
    lineas_ticket:  (STATE.lineas && STATE.lineas.length > 0) ? STATE.lineas : null,
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
    showToast('Factura enviada a ' + (data.email || emailFinal), 'ok');
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

  // Limpiar OCR status, file input y líneas
  const ocrStatus = document.getElementById('m-ocr-status');
  if (ocrStatus) { ocrStatus.style.display = 'none'; ocrStatus.innerHTML = ''; }
  const fileInput = document.getElementById('m-file-input');
  if (fileInput) fileInput.value = '';
  STATE.lineas = [];
  renderLineas();
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
          <span style="display:inline-block;animation:spin 1s linear infinite"><i class="bi bi-hourglass-split"></i></span>
          Cargando facturas…
        </span>
      </td></tr>`;
  }
}
function renderEmptyWithError() {
  document.getElementById('tbody').innerHTML = `
    <tr><td colspan="8" style="text-align:center;padding:3rem;color:var(--gris)">
      <i class="bi bi-exclamation-triangle-fill"></i> No se pudieron cargar los datos.<br>
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
