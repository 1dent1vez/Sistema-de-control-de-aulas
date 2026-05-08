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
          <option value="Salon">Salón</option>
          <option value="LabComputo">Lab. Cómputo</option>
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
        <option value="Salon">Salón</option>
        <option value="LabComputo">Lab. Cómputo</option>
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

  const edificios = [
    { id: 1, nombre: 'Edificio A', niveles: 4, estatus: 'Activo' },
    { id: 2, nombre: 'Edificio B', niveles: 3, estatus: 'Activo' },
    { id: 3, nombre: 'Edificio C', niveles: 2, estatus: 'Inactivo' }
  ];

  const activeBuildings = edificios.filter(e => e.estatus === 'Activo');
  let aulas = [
    { id: 1, edificio_id: 1, edificio: 'Edificio A', nombre: 'Aula 101', nivel: 1, tipo: 'Salon', capacidad: 35, qr_generado: true },
    { id: 2, edificio_id: 1, edificio: 'Edificio A', nombre: 'Aula 201', nivel: 2, tipo: 'LabComputo', capacidad: 28, qr_generado: false },
    { id: 3, edificio_id: 2, edificio: 'Edificio B', nombre: 'Aula 101', nivel: 1, tipo: 'Salon', capacidad: 40, qr_generado: true },
    { id: 4, edificio_id: 2, edificio: 'Edificio B', nombre: 'Aula 303', nivel: 3, tipo: 'Salon', capacidad: null, qr_generado: false }
  ];
  let nextId = 5;

  let state = { edificio: '', tipo: '', q: '', editingId: null };

  function esc(v) {
    return String(v ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

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
      <button class="toast-close"><i class="fas fa-times"></i></button>
    `;
    wrap.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    const t = setTimeout(() => rm(), 4200);
    function rm() {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 280);
    }
    toast.querySelector('.toast-close').addEventListener('click', () => { clearTimeout(t); rm(); });
  }

  function populateFilters() {
    const f = $('filterEdificio');
    f.innerHTML = '<option value="">Todos los edificios</option>' +
      activeBuildings.map(e => `<option value="${e.id}">${esc(e.nombre)}</option>`).join('');
  }

  function getRows() {
    return aulas.filter(a => {
      const okE = !state.edificio || a.edificio_id === Number(state.edificio);
      const okT = !state.tipo || a.tipo === state.tipo;
      const qq = state.q.trim().toLowerCase();
      const okQ = !qq || a.nombre.toLowerCase().includes(qq) || a.edificio.toLowerCase().includes(qq);
      return okE && okT && okQ;
    });
  }

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
        <td>${r.nivel}</td>
        <td>${r.tipo === 'Salon' ? 'Salón' : 'Lab. Cómputo'}</td>
        <td>${r.capacidad ?? '<span style="color:var(--soft-steel);">N/D</span>'}</td>
        <td>
          <span class="badge-qr ${r.qr_generado ? 'ok' : 'pending'}">
            <i class="fas ${r.qr_generado ? 'fa-check-circle' : 'fa-clock'}"></i>
            ${r.qr_generado ? 'Generado' : 'Pendiente'}
          </span>
        </td>
        <td>
          <div class="aulas-actions">
            <button class="btn btn-secondary btn-sm btn-sm-fixed" data-edit="${r.id}">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn-sm-fixed btn-qr" data-qr="${r.id}" title="Ver QR">
              <i class="fas fa-qrcode"></i>
            </button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  function openPanel(editId = null) {
    state.editingId = editId;
    clearErrors();
    $('formTitle').textContent = editId ? 'Editar Aula' : 'Nueva Aula';
    $('overlayAulas').classList.add('active');
    $('panelAulas').classList.add('open');
    document.body.style.overflow = 'hidden';

    const s = $('fEdificio');
    s.innerHTML = '<option value="">Selecciona...</option>' + activeBuildings.map(e => `<option value="${e.id}">${esc(e.nombre)}</option>`).join('');

    if (editId) {
      const record = aulas.find(x => x.id === editId);
      if (!record) return;
      $('fEdificio').value = String(record.edificio_id);
      syncNiveles(record.nivel);
      $('fNombre').value = record.nombre;
      $('countNombre').textContent = record.nombre.length;
      $('fTipo').value = record.tipo;
      $('fCapacidad').value = record.capacidad ?? '';
    } else {
      $('fEdificio').value = '';
      $('fNivel').innerHTML = '<option value="">Selecciona edificio...</option>';
      $('fNombre').value = '';
      $('countNombre').textContent = '0';
      $('fTipo').value = '';
      $('fCapacidad').value = '';
    }
  }

  function closePanel() {
    state.editingId = null;
    $('overlayAulas').classList.remove('active');
    $('panelAulas').classList.remove('open');
    document.body.style.overflow = '';
  }

  function syncNiveles(selected = null) {
    const edificioId = Number($('fEdificio').value);
    const edif = activeBuildings.find(e => e.id === edificioId);
    const level = $('fNivel');
    if (!edif) {
      level.innerHTML = '<option value="">Selecciona edificio...</option>';
      return;
    }
    let options = '<option value="">Selecciona...</option>';
    for (let i = 1; i <= edif.niveles; i += 1) {
      options += `<option value="${i}" ${selected === i ? 'selected' : ''}>Nivel ${i}</option>`;
    }
    level.innerHTML = options;
  }

  function clearErrors() {
    ['eEdificio','eNombre','eNivel','eTipo','eCapacidad'].forEach(id => {
      const el = $(id);
      el.classList.remove('active');
      el.textContent = '';
    });
  }

  function setError(id, msg) {
    const el = $(id);
    el.textContent = msg;
    el.classList.add('active');
  }

  function validateForm() {
    clearErrors();
    let ok = true;
    const edificioId = Number($('fEdificio').value);
    const nombre = $('fNombre').value.trim();
    const nivel = Number($('fNivel').value);
    const tipo = $('fTipo').value;
    const capacidadRaw = $('fCapacidad').value.trim();
    const capacidad = capacidadRaw ? Number(capacidadRaw) : null;

    if (!edificioId) { setError('eEdificio', 'Selecciona un edificio activo.'); ok = false; }
    if (!nombre) { setError('eNombre', 'El nombre es obligatorio.'); ok = false; }
    else if (nombre.length > 60) { setError('eNombre', 'Máximo 60 caracteres.'); ok = false; }

    const repeated = aulas.some(a =>
      a.edificio_id === edificioId &&
      a.nombre.toLowerCase() === nombre.toLowerCase() &&
      a.id !== state.editingId
    );
    if (nombre && edificioId && repeated) {
      setError('eNombre', 'Ya existe un aula con ese nombre en el edificio seleccionado.');
      ok = false;
    }

    if (!nivel || nivel <= 0) { setError('eNivel', 'Selecciona un nivel válido.'); ok = false; }
    if (!tipo) { setError('eTipo', 'Selecciona el tipo de aula.'); ok = false; }
    if (capacidadRaw && (!Number.isInteger(capacidad) || capacidad <= 0)) {
      setError('eCapacidad', 'La capacidad debe ser un entero positivo.');
      ok = false;
    }
    return ok;
  }

  function saveForm() {
    if (!validateForm()) return;
    const edificioId = Number($('fEdificio').value);
    const edificio = activeBuildings.find(e => e.id === edificioId);
    const record = {
      edificio_id: edificioId,
      edificio: edificio.nombre,
      nombre: $('fNombre').value.trim(),
      nivel: Number($('fNivel').value),
      tipo: $('fTipo').value,
      capacidad: $('fCapacidad').value.trim() ? Number($('fCapacidad').value.trim()) : null
    };
    if (state.editingId) {
      const idx = aulas.findIndex(a => a.id === state.editingId);
      aulas[idx] = { ...aulas[idx], ...record };
      showToast('Aula actualizada', 'Los cambios se guardaron correctamente.', 'success');
    } else {
      aulas.push({ id: nextId++, ...record, qr_generado: false });
      showToast('Aula registrada', 'El aula fue registrada exitosamente.', 'success');
    }
    closePanel();
    renderTable();
  }

  function bootPrecondition() {
    if (activeBuildings.length > 0) return;
    $('preconditionBox').style.display = 'block';
    $('btnNuevaAula').disabled = true;
    $('btnNuevaAula').title = 'Primero registra un edificio activo';
  }

  $('filterEdificio').addEventListener('change', (e) => { state.edificio = e.target.value; renderTable(); });
  $('filterTipo').addEventListener('change', (e) => { state.tipo = e.target.value; renderTable(); });
  $('searchAula').addEventListener('input', (e) => { state.q = e.target.value; renderTable(); });

  $('btnNuevaAula').addEventListener('click', () => {
    if (activeBuildings.length === 0) return;
    openPanel(null);
  });
  $('btnClosePanelAula').addEventListener('click', closePanel);
  $('btnCancelPanelAula').addEventListener('click', closePanel);
  $('overlayAulas').addEventListener('click', closePanel);
  $('btnSaveAula').addEventListener('click', saveForm);
  $('fEdificio').addEventListener('change', () => syncNiveles());
  $('fNombre').addEventListener('input', () => { $('countNombre').textContent = $('fNombre').value.length; });

  $('aulasBody').addEventListener('click', (e) => {
    const edit = e.target.closest('[data-edit]');
    if (edit) {
      openPanel(Number(edit.dataset.edit));
      return;
    }
    const qr = e.target.closest('[data-qr]');
    if (qr) {
      const aulaId = Number(qr.dataset.qr);
      window.location.href = `{{ route('codigosqr') }}?aula_id=${aulaId}`;
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && $('panelAulas').classList.contains('open')) closePanel();
  });

  bootPrecondition();
  populateFilters();
  renderTable();
});
</script>
@endsection
