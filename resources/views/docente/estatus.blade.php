{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Estatus Docente - Proyecto B: Sistema de Control de Aulas
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

@section('title', 'Estatus Docente - GAMA Solutions')

@section('content')
<style>
  .doc-main { margin-left: var(--sidebar-width, 240px); min-height: 100vh; background: var(--ice-blue); padding: 28px 32px; }
  .doc-head { margin-bottom: 14px; }
  .doc-title { font-size: 26px; font-weight: 700; color: var(--midnight); margin-bottom: 4px; }
  .doc-sub { color: var(--soft-steel); font-size: 14px; }
  .doc-grid { display: grid; grid-template-columns: 1fr 1.1fr; gap: 14px; align-items: start; }
  .doc-card { background: #fff; border: 1px solid var(--mist-blue); border-radius: var(--radius-lg); overflow: hidden; }
  .doc-card-h { padding: 12px 16px; border-bottom: 1px solid var(--mist-blue); font-size: 14px; font-weight: 700; color: var(--deep-blue); }
  .doc-card-b { padding: 16px; }
  .cal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
  .cal-month { font-weight: 700; color: var(--midnight); }
  .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; }
  .cal-wd { text-align: center; font-size: 11px; color: var(--soft-steel); font-weight: 600; padding: 4px 0; }
  .cal-day {
    height: 38px; border: 1px solid var(--mist-blue); border-radius: 8px; background: #fff; cursor: pointer;
    font-size: 12px; color: var(--dark-graphite); display: flex; align-items: center; justify-content: center;
  }
  .cal-day:hover { background: var(--light-orange); border-color: var(--corp-orange); }
  .cal-day.muted { opacity: 0.45; cursor: default; background: var(--ice-blue); }
  .cal-day.disabled { opacity: 0.45; cursor: not-allowed; background: #f3f5f7; }
  .cal-day.start, .cal-day.end { background: var(--deep-blue); color: #fff; border-color: var(--deep-blue); font-weight: 700; }
  .cal-day.in-range { background: rgba(19,68,116,0.1); border-color: rgba(19,68,116,0.2); }
  .field { margin-bottom: 12px; }
  .field label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: var(--midnight); }
  .field select, .field input, .field textarea {
    width: 100%; border: 1px solid var(--mist-blue); background: var(--ice-blue); border-radius: var(--radius-md);
    padding: 10px 12px; font-family: var(--font-main); font-size: 14px; outline: none;
  }
  .field select:focus, .field input:focus, .field textarea:focus { border-color: var(--corp-orange); }
  .field textarea { min-height: 82px; resize: vertical; }
  .err { margin-top: 5px; color: #b00000; font-size: 12px; display: none; }
  .err.show { display: block; }
  .class-list { display: grid; gap: 8px; max-height: 230px; overflow-y: auto; }
  .class-item { border: 1px solid var(--mist-blue); border-radius: var(--radius-md); padding: 8px 10px; background: #fff; font-size: 12px; line-height: 1.4; }
  .class-item b { color: var(--midnight); }
  .badge-ausencia {
    display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 20px;
    background: rgba(242,139,44,0.16); color: #F28B2C; font-weight: 700; font-size: 11px;
  }
  .note { margin-top: 10px; padding: 9px 11px; border-radius: var(--radius-md); font-size: 13px; display: none; }
  .note.show { display: block; }
  .note.info { background: rgba(19,68,116,.08); border:1px solid rgba(19,68,116,.2); color: var(--deep-blue); }
  .modal-overlay {
    position: fixed; inset: 0; background: rgba(19,68,116,0.4); display: none; align-items: center; justify-content: center; z-index: 2000;
  }
  .modal-overlay.show { display: flex; }
  .modal-card {
    width: 94%; max-width: 560px; background: #fff; border-radius: var(--radius-lg); border: 1px solid var(--mist-blue); overflow: hidden;
  }
  .modal-h { padding: 14px 16px; border-bottom: 1px solid var(--mist-blue); font-weight: 700; color: var(--midnight); }
  .modal-b { padding: 16px; font-size: 14px; color: var(--dark-graphite); }
  .modal-f { padding: 12px 16px 16px; display: flex; justify-content: flex-end; gap: 8px; }
  @media (max-width: 1120px) { .doc-main { margin-left: 0; } .doc-grid { grid-template-columns: 1fr; } }
</style>

<div class="doc-main">
  <div class="doc-head">
    <h1 class="doc-title">Estatus Docente - Registro de Ausencias</h1>
    <p class="doc-sub">Pantalla exclusiva para rol Docente (RN-01). Selecciona rango y confirma clases afectadas.</p>
  </div>

  <div class="doc-grid">
    <section class="doc-card">
      <div class="doc-card-h">Calendario mensual (selección visual de rango)</div>
      <div class="doc-card-b">
        <div class="cal-header">
          <button class="btn btn-outline btn-sm" id="btnPrevMonth"><i class="fas fa-chevron-left"></i></button>
          <span class="cal-month" id="calMonthLabel"></span>
          <button class="btn btn-outline btn-sm" id="btnNextMonth"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="cal-grid" id="calWeekDays"></div>
        <div class="cal-grid" id="calDays"></div>
      </div>
    </section>

    <section class="doc-card">
      <div class="doc-card-h">Formulario + Clases afectadas</div>
      <div class="doc-card-b">
        <div class="field">
          <label>Tipo de ausencia *</label>
          <select id="fTipo">
            <option value="">Selecciona...</option>
            <option value="Comision">Comisión</option>
            <option value="Junta">Junta</option>
            <option value="Incapacidad">Incapacidad</option>
            <option value="PermisoEconomico">Permiso Económico</option>
            <option value="Otro">Otro</option>
          </select>
          <div class="err" id="eTipo"></div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
          <div class="field">
            <label>Fecha inicio *</label>
            <input id="fInicio" type="date">
            <div class="err" id="eInicio"></div>
          </div>
          <div class="field">
            <label>Fecha fin *</label>
            <input id="fFin" type="date">
            <div class="err" id="eFin"></div>
          </div>
        </div>

        <div class="field">
          <label>Notas (opcional)</label>
          <textarea id="fNotas" placeholder="Detalle de la ausencia..."></textarea>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;margin:10px 0 8px;">
          <strong style="font-size:13px;color:var(--midnight);">Clases afectadas</strong>
          <span id="affectedCount" style="font-size:12px;color:var(--soft-steel);">0 clases</span>
        </div>
        <div class="class-list" id="affectedList"></div>

        <div class="note info show">
          Al registrarse, RF-11 mostrará badge de ausencia <span class="badge-ausencia">#F28B2C</span> en consultas de aula/docente durante el período activo.
        </div>

        <div style="margin-top:12px;">
          <button id="btnRegistrar" class="btn btn-primary btn-md" style="width:100%;">
            <i class="fas fa-save"></i><span>Registrar Ausencia</span>
          </button>
        </div>
      </div>
    </section>
  </div>

  <div class="toast-container" id="toastContainer"></div>
</div>

<div class="modal-overlay" id="conflictModal">
  <div class="modal-card">
    <div class="modal-h">Conflicto de ausencias detectado</div>
    <div class="modal-b">
      <p style="margin-bottom:10px;">El rango seleccionado se traslapa con una ausencia existente:</p>
      <div id="conflictDetail" style="background:var(--ice-blue);border:1px solid var(--mist-blue);border-radius:8px;padding:10px;"></div>
      <p style="margin-top:10px;">¿Deseas sobrescribir el registro existente?</p>
    </div>
    <div class="modal-f">
      <button class="btn btn-outline btn-sm" id="btnCancelConflict">Cancelar</button>
      <button class="btn btn-primary btn-sm" id="btnConfirmConflict">Confirmar sobrescritura</button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const CURRENT_USER = { id: 77, nombre: 'Mtra. Laura Mendez', rol: 'Docente' };
  const DIAS = ['DOM', 'LUN', 'MAR', 'MIE', 'JUE', 'VIE', 'SAB'];
  const DOCENTE_CLASSES = [
    { id: 1, materia: 'Matematicas I', aula: 'Aula 101', dia: 'LUN', hora: '08:00-09:30' },
    { id: 2, materia: 'Matematicas I', aula: 'Aula 101', dia: 'MIE', hora: '08:00-09:30' },
    { id: 3, materia: 'Programacion Web', aula: 'Aula 201', dia: 'VIE', hora: '10:00-11:30' },
    { id: 4, materia: 'Algebra', aula: 'Aula 102', dia: 'MAR', hora: '11:30-13:00' }
  ];
  const ausencias = [
    { id: 1, docente_id: 77, tipo: 'Junta', inicio: plusDays(2), fin: plusDays(3), notas: 'Reunion institucional' }
  ];

  const $ = (id) => document.getElementById(id);
  const today = new Date(); today.setHours(0,0,0,0);
  let viewYear = today.getFullYear();
  let viewMonth = today.getMonth();
  let selectedStart = null;
  let selectedEnd = null;
  let pendingOverwrite = null;

  if (CURRENT_USER.rol !== 'Docente') {
    $('btnRegistrar').disabled = true;
    showToast('Acceso restringido', 'Esta pantalla es exclusiva del rol Docente.');
  }

  const fInicio = $('fInicio');
  const fFin = $('fFin');
  fInicio.min = toIso(today);
  fFin.min = toIso(today);

  $('calWeekDays').innerHTML = ['Dom','Lun','Mar','Mie','Jue','Vie','Sab'].map(w => `<div class="cal-wd">${w}</div>`).join('');

  function plusDays(offset) {
    const d = new Date();
    d.setHours(0,0,0,0);
    d.setDate(d.getDate() + offset);
    return toIso(d);
  }
  function toIso(d) { return d.toISOString().slice(0,10); }
  function parseIso(str) { const d = new Date(str + 'T00:00:00'); d.setHours(0,0,0,0); return d; }
  function sameDate(a,b){ return a && b && a.getTime() === b.getTime(); }
  function inRange(d, a, b){ return a && b && d >= a && d <= b; }

  function renderCalendar() {
    const monthDate = new Date(viewYear, viewMonth, 1);
    const monthName = monthDate.toLocaleDateString('es-MX', { month: 'long', year: 'numeric' });
    $('calMonthLabel').textContent = monthName.charAt(0).toUpperCase() + monthName.slice(1);

    const firstDay = new Date(viewYear, viewMonth, 1);
    const lastDay = new Date(viewYear, viewMonth + 1, 0);
    const totalCells = Math.ceil((firstDay.getDay() + lastDay.getDate()) / 7) * 7;
    let html = '';
    for (let i = 0; i < totalCells; i += 1) {
      const dayNum = i - firstDay.getDay() + 1;
      const d = new Date(viewYear, viewMonth, dayNum);
      const isCurrent = d.getMonth() === viewMonth;
      const disabled = d < today;
      const cls = [
        'cal-day',
        !isCurrent ? 'muted' : '',
        disabled ? 'disabled' : '',
        sameDate(d, selectedStart) ? 'start' : '',
        sameDate(d, selectedEnd) ? 'end' : '',
        inRange(d, selectedStart, selectedEnd) && !sameDate(d, selectedStart) && !sameDate(d, selectedEnd) ? 'in-range' : ''
      ].filter(Boolean).join(' ');
      html += `<button type="button" class="${cls}" data-date="${toIso(d)}" ${disabled ? 'disabled' : ''}>${d.getDate()}</button>`;
    }
    $('calDays').innerHTML = html;
  }

  function updateRangeFromCalendar(dateIso) {
    const d = parseIso(dateIso);
    if (!selectedStart || (selectedStart && selectedEnd)) {
      selectedStart = d; selectedEnd = null;
    } else if (d < selectedStart) {
      selectedEnd = selectedStart; selectedStart = d;
    } else {
      selectedEnd = d;
    }
    fInicio.value = selectedStart ? toIso(selectedStart) : '';
    fFin.value = selectedEnd ? toIso(selectedEnd) : fInicio.value;
    renderCalendar();
    refreshAffectedClasses();
  }

  function refreshFromInputs() {
    selectedStart = fInicio.value ? parseIso(fInicio.value) : null;
    selectedEnd = fFin.value ? parseIso(fFin.value) : (selectedStart ? parseIso(fInicio.value) : null);
    renderCalendar();
    refreshAffectedClasses();
  }

  function dateInSelectedRange(dateObj) {
    if (!selectedStart || !selectedEnd) return false;
    return dateObj >= selectedStart && dateObj <= selectedEnd;
  }

  function refreshAffectedClasses() {
    const list = $('affectedList');
    if (!selectedStart || !selectedEnd) {
      list.innerHTML = '<div class="class-item">Define fecha inicio y fin para visualizar clases afectadas.</div>';
      $('affectedCount').textContent = '0 clases';
      return;
    }
    const affected = [];
    for (let d = new Date(selectedStart); d <= selectedEnd; d.setDate(d.getDate() + 1)) {
      const key = DIAS[d.getDay()];
      DOCENTE_CLASSES.filter(c => c.dia === key).forEach(c => {
        affected.push({ ...c, fecha: toIso(new Date(d)) });
      });
    }
    $('affectedCount').textContent = `${affected.length} clase(s)`;
    if (!affected.length) {
      list.innerHTML = '<div class="class-item">No hay clases programadas del docente en ese período.</div>';
      return;
    }
    list.innerHTML = affected.map(a => `
      <div class="class-item">
        <b>${a.materia}</b><br>
        ${a.aula} · ${a.dia} · ${a.hora}<br>
        Fecha: ${a.fecha}
      </div>`).join('');
  }

  function setErr(id, msg) { const e = $(id); e.textContent = msg; e.classList.add('show'); }
  function clearErrs() { ['eTipo','eInicio','eFin'].forEach(id => { $(id).classList.remove('show'); $(id).textContent=''; }); }

  function validate() {
    clearErrs();
    let ok = true;
    if (CURRENT_USER.rol !== 'Docente') { showToast('Acceso restringido', 'RN-01: solo Docente puede registrar su ausencia.'); return false; }
    if (!$('fTipo').value) { setErr('eTipo', 'Selecciona un tipo de ausencia.'); ok = false; }
    if (!fInicio.value) { setErr('eInicio', 'Define fecha inicio.'); ok = false; }
    if (!fFin.value) { setErr('eFin', 'Define fecha fin.'); ok = false; }
    if (fInicio.value && parseIso(fInicio.value) < today) { setErr('eInicio', 'No se permiten fechas en pasado.'); ok = false; }
    if (fFin.value && parseIso(fFin.value) < today) { setErr('eFin', 'No se permiten fechas en pasado.'); ok = false; }
    if (fInicio.value && fFin.value && parseIso(fFin.value) < parseIso(fInicio.value)) { setErr('eFin', 'La fecha fin debe ser mayor o igual a inicio.'); ok = false; }
    return ok;
  }

  function findConflict() {
    const start = parseIso(fInicio.value);
    const end = parseIso(fFin.value);
    return ausencias.find(a =>
      a.docente_id === CURRENT_USER.id &&
      parseIso(a.inicio) <= end &&
      parseIso(a.fin) >= start
    );
  }

  function registerAbsence(overwriteId = null) {
    const start = fInicio.value;
    const end = fFin.value;
    if (overwriteId) {
      const idx = ausencias.findIndex(a => a.id === overwriteId);
      if (idx >= 0) ausencias.splice(idx, 1);
    }
    ausencias.push({
      id: Date.now(),
      docente_id: CURRENT_USER.id,
      tipo: $('fTipo').value,
      inicio: start,
      fin: end,
      notas: $('fNotas').value.trim()
    });
    const countText = $('affectedCount').textContent.split(' ')[0] || '0';
    showToast('Ausencia registrada', `Ausencia registrada. ${countText} clases marcadas`);
  }

  function showConflictModal(conflict) {
    $('conflictDetail').innerHTML = `
      <strong>Tipo:</strong> ${conflict.tipo}<br>
      <strong>Rango:</strong> ${conflict.inicio} al ${conflict.fin}<br>
      <strong>Notas:</strong> ${conflict.notas || 'Sin notas'}
    `;
    pendingOverwrite = conflict.id;
    $('conflictModal').classList.add('show');
  }
  function hideConflictModal() {
    pendingOverwrite = null;
    $('conflictModal').classList.remove('show');
  }

  function showToast(title, message) {
    const t = document.createElement('div');
    t.className = 'toast success';
    t.innerHTML = `<div class="toast-icon"><i class="fas fa-check"></i></div><div class="toast-content"><div class="toast-title">${title}</div><div class="toast-message">${message}</div></div><button class="toast-close"><i class="fas fa-times"></i></button>`;
    $('toastContainer').appendChild(t);
    setTimeout(() => t.classList.add('show'), 10);
    const rm = () => { t.classList.remove('show'); setTimeout(() => t.remove(), 260); };
    const tm = setTimeout(rm, 4500);
    t.querySelector('.toast-close').addEventListener('click', () => { clearTimeout(tm); rm(); });
  }

  $('btnPrevMonth').addEventListener('click', () => { viewMonth -= 1; if (viewMonth < 0) { viewMonth = 11; viewYear -= 1; } renderCalendar(); });
  $('btnNextMonth').addEventListener('click', () => { viewMonth += 1; if (viewMonth > 11) { viewMonth = 0; viewYear += 1; } renderCalendar(); });
  $('calDays').addEventListener('click', (e) => {
    const btn = e.target.closest('[data-date]');
    if (!btn || btn.disabled) return;
    updateRangeFromCalendar(btn.dataset.date);
  });
  fInicio.addEventListener('change', refreshFromInputs);
  fFin.addEventListener('change', refreshFromInputs);

  $('btnRegistrar').addEventListener('click', () => {
    if (!validate()) return;
    const conflict = findConflict();
    if (conflict) { showConflictModal(conflict); return; }
    registerAbsence(null);
  });

  $('btnCancelConflict').addEventListener('click', hideConflictModal);
  $('btnConfirmConflict').addEventListener('click', () => {
    if (pendingOverwrite) registerAbsence(pendingOverwrite);
    hideConflictModal();
  });
  $('conflictModal').addEventListener('click', (e) => { if (e.target.id === 'conflictModal') hideConflictModal(); });

  renderCalendar();
  refreshAffectedClasses();
});
</script>
@endsection
