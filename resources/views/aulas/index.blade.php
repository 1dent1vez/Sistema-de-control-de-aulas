{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Gestión de Aulas - Proyecto B: Sistema de Control de Aulas
 * @autor          Rubén Alejandro Nolasco Ruiz
 * @autorizador    Rubén Alejandro Nolasco Ruiz
 * @prueba         Diego Miguel Hernandez Fabela
 * @mantenimiento  Ghael Garcia Manjarrez
 * @version        1.0.0
 * @creado         07/05/2026
 * @modificado     07/05/2026
 */
--}}

@extends('layouts.app')

@section('title', 'Gestión de Aulas - GAMA Solutions')

@section('content')
<style>
  .aulas-main {
    margin-left: var(--sidebar-width, 240px);
    min-height: 100vh;
    background: var(--ice-blue);
    padding: 28px 32px;
  }

  .aulas-header {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: flex-start;
    flex-wrap: wrap;
    margin-bottom: 20px;
  }

  .aulas-title {
    font-size: 26px;
    color: var(--midnight);
    font-weight: 700;
    margin-bottom: 4px;
  }

  .aulas-subtitle {
    color: var(--soft-steel);
    font-size: 14px;
  }

  .aulas-card {
    background: #fff;
    border: 1px solid var(--mist-blue);
    border-radius: var(--radius-lg);
    overflow: hidden;
  }

  .aulas-toolbar {
    padding: 16px 22px;
    border-bottom: 1px solid var(--mist-blue);
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
  }

  .aulas-filters {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
  }

  .aulas-select, .aulas-search {
    border: 1px solid var(--mist-blue);
    background: var(--ice-blue);
    border-radius: var(--radius-md);
    padding: 9px 12px;
    font-size: 13px;
    font-family: var(--font-main);
    color: var(--dark-graphite);
    outline: none;
  }

  .aulas-search { min-width: 220px; }
  .aulas-select:focus, .aulas-search:focus { border-color: var(--corp-orange); }

  .aulas-table-wrap { overflow-x: auto; }

  .aulas-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
  }

  .aulas-table thead th {
    background: var(--deep-blue);
    color: #fff;
    text-align: left;
    padding: 12px 14px;
    font-weight: 600;
  }

  .aulas-table tbody tr:nth-child(odd) { background: var(--light-blue); }
  .aulas-table tbody tr:nth-child(even) { background: #fff; }
  .aulas-table tbody tr:hover { background: var(--light-orange); }

  .aulas-table tbody td {
    padding: 12px 14px;
    border-bottom: 1px solid var(--mist-blue);
    color: var(--dark-graphite);
    vertical-align: middle;
  }

  .badge-qr {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
  }

  .badge-qr.ok {
    background: rgba(90,154,90,0.14);
    color: var(--status-active);
  }

  .badge-qr.pending {
    background: rgba(242,139,44,0.18);
    color: var(--deep-orange);
  }

  .aulas-actions {
    display: flex;
    gap: 6px;
    align-items: center;
  }

  .btn-sm-fixed {
    height: 34px;
    padding: 0 12px;
    font-size: 12px;
    border-radius: var(--radius-sm);
  }

  .btn-qr {
    background: transparent;
    border: 1px solid var(--mist-blue);
    color: var(--deep-blue);
    cursor: pointer;
  }

  .btn-qr:hover { border-color: var(--deep-blue); background: rgba(19,68,116,0.06); }

  .precondition-box {
    border: 1px dashed rgba(242,139,44,0.5);
    background: rgba(242,139,44,0.08);
    color: var(--deep-orange);
    border-radius: var(--radius-md);
    padding: 12px 14px;
    font-size: 13px;
    margin-bottom: 14px;
    display: none;
  }

  .modal-overlay-aulas {
    position: fixed;
    inset: 0;
    background: rgba(19,68,116,0.3);
    display: none;
    z-index: 1600;
  }

  .modal-overlay-aulas.active { display: block; }

  .modal-panel-aulas {
    position: fixed;
    right: 0;
    top: 0;
    bottom: 0;
    width: 390px;
    background: #fff;
    z-index: 1601;
    transform: translateX(100%);
    transition: transform 0.24s ease;
    border-left: 1px solid var(--mist-blue);
    display: flex;
    flex-direction: column;
  }

  .modal-panel-aulas.open { transform: translateX(0); }

  .modal-head, .modal-foot {
    padding: 16px 18px;
    border-bottom: 1px solid var(--mist-blue);
  }

  .modal-foot {
    border-top: 1px solid var(--mist-blue);
    border-bottom: 0;
    display: flex;
    gap: 8px;
  }

  .modal-body {
    padding: 18px;
    overflow-y: auto;
    flex: 1;
  }

  .field { margin-bottom: 14px; }
  .field label { display: block; font-size: 13px; font-weight: 600; color: var(--midnight); margin-bottom: 6px; }
  .field input, .field select, .field textarea {
    width: 100%;
    border: 1px solid var(--mist-blue);
    border-radius: var(--radius-md);
    background: var(--ice-blue);
    padding: 10px 12px;
    font-family: var(--font-main);
    font-size: 14px;
    outline: none;
  }

  .field input:focus, .field select:focus, .field textarea:focus { border-color: var(--corp-orange); }

  .field .err {
    margin-top: 5px;
    color: var(--status-inactive);
    font-size: 12px;
    display: none;
  }

  .field .err.active { display: block; }
  .field .hint { font-size: 11px; color: var(--soft-steel); margin-top: 4px; text-align: right; }

  @media (max-width: 1024px) { .aulas-main { margin-left: 0; } }
  @media (max-width: 640px) {
    .aulas-main { padding: 16px; }
    .modal-panel-aulas { width: 100%; }
  }
</style>

<div class="aulas-main">
  <div class="aulas-header">
    <div>
      <h1 class="aulas-title">Gestión de Aulas</h1>
      <p class="aulas-subtitle">Tabla de aulas filtrable por edificio y tipo, con estado de QR.</p>
    </div>
    <button class="btn btn-primary btn-md" id="btnNuevaAula">
      <i class="fas fa-plus"></i>
      <span>Nueva Aula</span>
    </button>
  </div>

  <div class="precondition-box" id="preconditionBox">
    Se requiere al menos un edificio activo registrado para gestionar aulas.
  </div>

  <section class="aulas-card">
    <div class="aulas-toolbar">
      <div class="aulas-filters">
        <select id="filterEdificio" class="aulas-select"></select>
        <select id="filterTipo" class="aulas-select">
          <option value="">Todos los tipos</option>
          <option value="classroom">Salón</option>
          <option value="computer_lab">Laboratorio de Cómputo</option>
        </select>
        <input id="searchAula" class="aulas-search" type="text" placeholder="Buscar aula..." autocomplete="off">
      </div>
      <span id="resultsInfo" style="font-size:12px;color:var(--soft-steel);"></span>
    </div>

    <div class="aulas-table-wrap">
      <table class="aulas-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Edificio</th>
            <th>Aula</th>
            <th>Nivel</th>
            <th>Tipo</th>
            <th>Capacidad</th>
            <th>QR</th>
            <th style="width:170px;">Acciones</th>
          </tr>
        </thead>
        <tbody id="aulasBody"></tbody>
      </table>
    </div>
  </section>
</div>

<div class="modal-overlay-aulas" id="overlayAulas"></div>
<aside class="modal-panel-aulas" id="panelAulas">
  <div class="modal-head">
    <div style="display:flex;justify-content:space-between;gap:8px;align-items:flex-start;">
      <div>
        <div id="formTitle" style="font-weight:700;color:var(--midnight);">Nueva Aula</div>
        <small style="color:var(--soft-steel);">Registro de aula por edificio</small>
      </div>
      <button id="btnClosePanelAula" class="btn btn-ghost btn-sm" style="height:30px;width:30px;padding:0;">
        <i class="fas fa-times"></i>
      </button>
    </div>
  </div>

  <div class="modal-body">
    <div class="field">
      <label for="fEdificio">Edificio *</label>
      <select id="fEdificio"></select>
      <div class="err" id="eEdificio"></div>
    </div>

    <div class="field">
      <label for="fNombre">Nombre del aula *</label>
      <input id="fNombre" maxlength="60" placeholder="Ej. Aula 101">
      <div class="hint"><span id="countNombre">0</span>/60</div>
      <div class="err" id="eNombre"></div>
    </div>

    <div class="field">
      <label for="fNivel">Nivel *</label>
      <select id="fNivel"></select>
      <div class="err" id="eNivel"></div>
    </div>

    <div class="field">
      <label for="fTipo">Tipo *</label>
      <select id="fTipo">
        <option value="">Selecciona...</option>
        <option value="classroom">Salón</option>
        <option value="computer_lab">Laboratorio de Cómputo</option>
      </select>
      <div class="err" id="eTipo"></div>
    </div>

    <div class="field">
      <label for="fCapacidad">Capacidad (opcional)</label>
      <input id="fCapacidad" type="number" min="1" step="1" placeholder="Ej. 35">
      <div class="err" id="eCapacidad"></div>
    </div>
  </div>

  <div class="modal-foot">
    <button class="btn btn-outline btn-md" id="btnCancelPanelAula" style="flex:1;">Cancelar</button>
    <button class="btn btn-primary btn-md" id="btnSaveAula" style="flex:1;">Guardar</button>
  </div>
</aside>

<div class="toast-container" id="toastContainer"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const $ = (id) => document.getElementById(id);

  /* Verificar token */
  const authToken = localStorage.getItem('auth_token');
  if (!authToken) {
    window.location.href = '/';
    return;
  }

  /* Estado */
  let buildings      = [];   // edificios activos
  let aulas          = [];   // classrooms completos
  let levelsCache    = {};   // { buildingId: [{id, name}] }
  let state          = { edificio: '', tipo: '', q: '', editingId: null };

  /* CSRF */
  function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  }

  /* API helper */
  async function apiFetch(url, opts = {}) {
    const res = await fetch(url, {
      headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': getCsrf(), 'Authorization': `Bearer ${authToken}`, ...opts.headers },
      ...opts,
    });
    if (res.status === 401) {
      localStorage.clear();
      window.location.href = '/';
      return;
    }
    const json = await res.json();
    if (!res.ok) throw { status: res.status, json };
    return json;
  }

  /* Estado */
  let buildings      = [];   // edificios activos
  let aulas          = [];   // classrooms completos
  let levelsCache    = {};   // { buildingId: [{id, name}] }
  let state          = { edificio: '', tipo: '', q: '', editingId: null };

  /* CSRF */
  function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  }

  /* API helper */
  async function apiFetch(url, opts = {}) {
    const res = await fetch(url, {
      headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': getCsrf(), 'Authorization': `Bearer ${authToken}`, ...opts.headers },
      ...opts,
    });
    if (res.status === 401) {
      localStorage.clear();
      window.location.href = '/';
      return;
    }
    const json = await res.json();
    if (!res.ok) throw { status: res.status, json };
    return json;
  }

  /* â”€â”€ API helper â”€â”€ */
  async function apiFetch(url, opts = {}) {
    const res = await fetch(url, {
      headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': getCsrf(), ...opts.headers },
      ...opts,
    });
    const json = await res.json();
    if (!res.ok) throw { status: res.status, json };
    return json;
  }

  /* â”€â”€ Toast â”€â”€ */
  function showToast(title, message, type = 'success') {
    const wrap = $('toastContainer');
    const icon = type === 'success' ? 'check' : (type === 'warning' ? 'exclamation' : 'times');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
      <div class="toast-icon"><i class="fas fa-${icon}"></i></div>
      <div class="toast-content">
        <div class="toast-title">${esc(title)}</div>
        <div class="toast-message">${esc(message)}</div>
      </div>
      <button class="toast-close"><i class="fas fa-times"></i></button>`;
    wrap.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    const t = setTimeout(() => rm(), 4200);
    function rm() { toast.classList.remove('show'); setTimeout(() => toast.remove(), 280); }
    toast.querySelector('.toast-close').addEventListener('click', () => { clearTimeout(t); rm(); });
  }

  function esc(v) {
    return String(v ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  /* ── Mapeo de tipo de aula ── */
  const TIPOS = { classroom: 'Salón', computer_lab: 'Laboratorio de Cómputo' };

  /* â”€â”€ Carga inicial â”€â”€ */
  async function loadAll() {
    $('aulasBody').innerHTML = '<tr><td colspan="8" style="text-align:center;padding:32px;color:var(--soft-steel);"><i class="fas fa-spinner fa-spin" style="font-size:22px;"></i></td></tr>';
    
    let buildingsLoaded = false;
    try {
      const buildRes = await apiFetch('/api/v1/buildings');
      buildings = (buildRes.data ?? [])
        .filter(b => b.isActive)
        .map(b => ({ id: b.id, nombre: b.name, levelCount: b.levelCount }));
      buildingsLoaded = true;
    } catch(e) {
      showToast('Error', 'No se pudieron cargar los edificios.', 'error');
      $('aulasBody').innerHTML = '<tr><td colspan="8" style="text-align:center;padding:32px;color:var(--status-inactive);">No se pudieron cargar los edificios.</td></tr>';
      bootPrecondition();
      return;
    }

    try {
      const aulaRes = await apiFetch('/api/v1/classrooms');
      aulas = (aulaRes.data ?? []).map(c => ({
        id:          c.id,
        buildingId:  c.buildingId,
        edificio:    c.buildingName ?? '',
        levelId:     c.levelId,
        levelName:   c.levelName ?? '',
        nombre:      c.classroomName,
        tipo:        c.classroomType,
        tipoLabel:   c.classroomTypeLabel ?? (TIPOS[c.classroomType] ?? c.classroomType),
        isActive:    c.isActive,
        qrGenerado:  c.hasActiveQr ?? false,
      }));

      bootPrecondition();
      populateFilters();
      renderTable();
    } catch(e) {
      $('aulasBody').innerHTML = '<tr><td colspan="8" style="text-align:center;padding:32px;color:var(--status-inactive);">Error al cargar las aulas de la API.</td></tr>';
      showToast('Error', 'No se pudieron cargar las aulas.', 'error');
    }
  }

  /* ── Cargar niveles de un edificio ── */
  async function loadLevels(buildingId) {
    if (levelsCache[buildingId]) return levelsCache[buildingId];
    try {
      const res = await apiFetch(`/api/v1/buildings/${buildingId}/levels`);
      levelsCache[buildingId] = (res.data ?? []).map(l => ({ id: l.id, name: l.name }));
      return levelsCache[buildingId];
    } catch(e) {
      return null;
    }
  }

  /* â”€â”€ Filtros â”€â”€ */
  function populateFilters() {
    const f = $('filterEdificio');
    f.innerHTML = '<option value="">Todos los edificios</option>' +
      buildings.map(b => `<option value="${b.id}">${esc(b.nombre)}</option>`).join('');
  }

  function getRows() {
    return aulas.filter(a => {
      const okE = !state.edificio || String(a.buildingId) === String(state.edificio);
      const okT = !state.tipo || a.tipo === state.tipo;
      const qq  = state.q.trim().toLowerCase();
      const okQ = !qq || a.nombre.toLowerCase().includes(qq) || a.edificio.toLowerCase().includes(qq);
      return okE && okT && okQ;
    });
  }

  /* â”€â”€ Render tabla â”€â”€ */
  function renderTable() {
    const rows = getRows();
    $('resultsInfo').textContent = `${rows.length} aula(s) encontrada(s)`;
    const body = $('aulasBody');
    if (rows.length === 0) {
      body.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--soft-steel);padding:28px;">Sin resultados</td></tr>';
      return;
    }
    body.innerHTML = rows.map((r, idx) => `
      <tr>
        <td>${idx + 1}</td>
        <td>${esc(r.edificio)}</td>
        <td style="font-weight:600;color:var(--midnight);">${esc(r.nombre)}</td>
        <td>${esc(r.levelName)}</td>
        <td>${esc(r.tipoLabel)}</td>
        <td><span style="color:var(--soft-steel);">N/D</span></td>
        <td>
          <span class="badge-qr ${r.qrGenerado ? 'ok' : 'pending'}">
            <i class="fas ${r.qrGenerado ? 'fa-check-circle' : 'fa-clock'}"></i>
            ${r.qrGenerado ? 'Generado' : 'Pendiente'}
          </span>
        </td>
        <td>
          <div class="aulas-actions">
            <button class="btn btn-secondary btn-sm btn-sm-fixed" data-edit="${r.id}" title="Editar">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn-sm-fixed btn-qr" data-qr="${r.id}" title="Ver QR">
              <i class="fas fa-qrcode"></i>
            </button>
          </div>
        </td>
      </tr>`).join('');
  }

  /* ── Panel nuevo/editar ── */
  function checkFormValidity() {
    const buildingId = $('fEdificio').value;
    const nombre     = $('fNombre').value.trim();
    const levelId    = $('fNivel').value;
    const tipo       = $('fTipo').value;

    const isValid = buildingId && nombre && nombre.length <= 30 && levelId && tipo;
    $('btnSaveAula').disabled = !isValid;
  }

  async function openPanel(editId = null) {
    state.editingId = editId;
    clearErrors();
    $('formTitle').textContent = editId ? 'Editar Aula' : 'Nueva Aula';
    $('overlayAulas').classList.add('active');
    $('panelAulas').classList.add('open');
    document.body.style.overflow = 'hidden';

    const s = $('fEdificio');
    s.innerHTML = '<option value="">Selecciona...</option>' +
      buildings.map(b => `<option value="${b.id}">${esc(b.nombre)}</option>`).join('');

    if (editId) {
      const record = aulas.find(x => x.id === editId);
      if (!record) return;
      $('fEdificio').value = String(record.buildingId);
      await syncNiveles(record.levelId);
      $('fNombre').value = record.nombre;
      $('countNombre').textContent = record.nombre.length;
      $('fTipo').value = record.tipo;
    } else {
      $('fEdificio').value = '';
      $('fNivel').innerHTML = '<option value="">Selecciona edificio...</option>';
      $('fNombre').value = '';
      $('countNombre').textContent = '0';
      $('fTipo').value = '';
    }

    checkFormValidity();
  }

  function closePanel() {
    state.editingId = null;
    $('overlayAulas').classList.remove('active');
    $('panelAulas').classList.remove('open');
    document.body.style.overflow = '';
  }

  async function syncNiveles(selectedId = null) {
    const buildingId = Number($('fEdificio').value);
    const level = $('fNivel');
    
    // Limpiar errores previos de niveles
    $('eNivel').textContent = '';
    $('eNivel').classList.remove('active');
    
    if (!buildingId) {
      level.innerHTML = '<option value="">Selecciona edificio...</option>';
      return;
    }
    level.innerHTML = '<option value="">Cargando...</option>';
    const levels = await loadLevels(buildingId);
    if (levels === null) {
      showToast('Error', 'No se pudieron cargar los niveles.', 'error');
      setError('eNivel', 'No se pudieron cargar los niveles.');
      level.innerHTML = '<option value="">Error al cargar niveles</option>';
      level.disabled = true;
      return;
    }
    level.disabled = false;
    level.innerHTML = '<option value="">Selecciona...</option>' +
      levels.map(l => `<option value="${l.id}" ${selectedId == l.id ? 'selected' : ''}>${esc(l.name)}</option>`).join('');
  }

  /* â”€â”€ Errores â”€â”€ */
  function clearErrors() {
    ['eEdificio','eNombre','eNivel','eTipo','eCapacidad'].forEach(id => {
      $(id).classList.remove('active');
      $(id).textContent = '';
    });
  }

  function setError(id, msg) {
    $(id).textContent = msg;
    $(id).classList.add('active');
  }

  /* â”€â”€ Guardar â”€â”€ */
  async function saveForm() {
    clearErrors();
    const buildingId = Number($('fEdificio').value);
    const nombre     = $('fNombre').value.trim();
    const levelId    = Number($('fNivel').value);
    const tipo       = $('fTipo').value;
    let ok = true;

    if (!buildingId) { setError('eEdificio', 'Selecciona un edificio activo.'); ok = false; }
    if (!nombre)     { setError('eNombre',   'El nombre es obligatorio.');       ok = false; }
    else if (nombre.length > 30) { setError('eNombre', 'Máximo 30 caracteres.'); ok = false; }
    if (!levelId)    { setError('eNivel',    'Selecciona un nivel válido.');     ok = false; }
    if (!tipo)       { setError('eTipo',     'Selecciona el tipo de aula.');     ok = false; }
    if (!ok) return;

    const payload = {
      building_id:    buildingId,
      level_id:       levelId,
      classroom_name: nombre,
      classroom_type: tipo,
    };

    const saveBtn = $('btnSaveAula');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Guardandoâ€¦';

    try {
      if (state.editingId) {
        await apiFetch(`/api/v1/classrooms/${state.editingId}`, { method:'PUT', body: JSON.stringify(payload) });
        showToast('Aula actualizada', 'Los cambios se guardaron correctamente.', 'success');
      } else {
        await apiFetch('/api/v1/classrooms', { method:'POST', body: JSON.stringify(payload) });
        showToast('Aula registrada', 'El aula fue registrada exitosamente.', 'success');
      }
      closePanel();
      levelsCache = {};
      await loadAll();
    } catch(err) {
      const errs = err.json?.errors ?? {};
      if (errs.building_id)    setError('eEdificio', errs.building_id[0]);
      if (errs.classroom_name) setError('eNombre',   errs.classroom_name[0]);
      if (errs.level_id)       setError('eNivel',    errs.level_id[0]);
      if (errs.classroom_type) setError('eTipo',     errs.classroom_type[0]);
      if (!Object.keys(errs).length)
        showToast('Error', err.json?.message ?? 'No se pudo guardar el aula.', 'error');
    } finally {
      saveBtn.disabled = false;
      saveBtn.textContent = 'Guardar';
    }
  }

  /* â”€â”€ Precondición â”€â”€ */
  function bootPrecondition() {
    if (buildings.length > 0) return;
    $('preconditionBox').style.display = 'block';
    $('btnNuevaAula').disabled = true;
    $('btnNuevaAula').title = 'Primero registra un edificio activo';
  }

  /* ── Eventos ── */
  $('filterEdificio').addEventListener('change', (e) => { state.edificio = e.target.value; renderTable(); });
  $('filterTipo').addEventListener('change',     (e) => { state.tipo     = e.target.value; renderTable(); });
  $('searchAula').addEventListener('input',      (e) => { state.q        = e.target.value; renderTable(); });

  $('btnNuevaAula').addEventListener('click',        () => { if (buildings.length) openPanel(null); });
  $('btnClosePanelAula').addEventListener('click',   closePanel);
  $('btnCancelPanelAula').addEventListener('click',  closePanel);
  $('overlayAulas').addEventListener('click',        closePanel);
  $('btnSaveAula').addEventListener('click',         saveForm);
  
  $('fEdificio').addEventListener('change',          () => { syncNiveles().then(() => checkFormValidity()); });
  $('fNombre').addEventListener('input',             () => { $('countNombre').textContent = $('fNombre').value.length; checkFormValidity(); });
  $('fNivel').addEventListener('change',             checkFormValidity);
  $('fTipo').addEventListener('change',              checkFormValidity);

  $('aulasBody').addEventListener('click', (e) => {
    const edit = e.target.closest('[data-edit]');
    if (edit) { openPanel(Number(edit.dataset.edit)); return; }
    const qr = e.target.closest('[data-qr]');
    if (qr) window.location.href = `{{ route('codigosqr') }}?aula_id=${qr.dataset.qr}`;
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && $('panelAulas').classList.contains('open')) closePanel();
  });

  /* â”€â”€ Arranque â”€â”€ */
  loadAll();
});
</script>
@endsection
