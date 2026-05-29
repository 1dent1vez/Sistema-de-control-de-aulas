{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Estatus Docente - Registro de Ausencias conectado a API REST
 * @autor          Rubén Alejandro Nolasco Ruiz, Equipo GAMA
 * @autorizador    Rubén Alejandro Nolasco Ruiz
 * @prueba         Diego Miguel Hernandez Fabela
 * @mantenimiento  Ghael Garcia Manjarrez
 * @version        1.1.0
 * @creado         07/05/2026
 * @modificado     19/05/2026
 * @cambios        19/05/2026 - Conexión a API REST, eliminación de datos mock
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
  .cal-day.absence { background: rgba(242,139,44,0.2); border-color: var(--corp-orange); color: var(--corp-orange); font-weight: 700; }
  .cal-day.absence:hover { background: rgba(242,139,44,0.3); }
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
  .note { margin-top: 10px; padding: 9px 11px; border-radius: var(--radius-md); font-size: 13px; }
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
  .history-list { display: grid; gap: 8px; max-height: 300px; overflow-y: auto; margin-top: 10px; }
  .history-item { border: 1px solid var(--mist-blue); border-radius: var(--radius-md); padding: 10px; background: #fff; font-size: 13px; line-height: 1.5; }
  .history-item .h-date { font-weight: 600; color: var(--midnight); }
  .history-item .h-type { color: var(--corp-orange); font-weight: 600; }
  .history-empty { text-align: center; padding: 24px; color: var(--soft-steel); font-size: 14px; }
  .spinner {
    display: inline-block; width: 24px; height: 24px; border: 3px solid var(--mist-blue);
    border-top-color: var(--deep-blue); border-radius: 50%; animation: spin 0.7s linear infinite;
  }
  @keyframes spin { to { transform: rotate(360deg); } }
  .loading-overlay { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 40px; color: var(--soft-steel); }
  .hidden { display: none !important; }
  @media (max-width: 1120px) { .doc-main { margin-left: 0; } .doc-grid { grid-template-columns: 1fr; } }
</style>

<div class="doc-main">
  <div class="doc-head">
    <h1 class="doc-title">Estatus Docente - Registro de Ausencias</h1>
    <p class="doc-sub" id="docSub">Cargando datos del docente...</p>
  </div>

  <div id="mainLoader" class="loading-overlay">
    <div class="spinner"></div>
    <span>Cargando información...</span>
  </div>

  <div id="mainContent" class="hidden">
    <div class="doc-grid">
      <section class="doc-card">
        <div class="doc-card-h">Calendario mensual</div>
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

      <section class="doc-card" id="formRegistrar">
        <div class="doc-card-h">Formulario + Clases afectadas</div>
        <div class="doc-card-b">
          <div class="field" style="display: none;">
            <label>Docente *</label>
            <select id="fDocente" style="display: none;"></select>
            <div class="err" id="eDocente"></div>
          </div>

          <div class="field">
            <label>Tipo de ausencia *</label>
            <select id="fTipo">
              <option value="">Cargando...</option>
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

          <div style="margin-top:12px;">
            <button id="btnRegistrar" class="btn btn-primary btn-md" style="width:100%;">
              <i class="fas fa-save"></i><span>Registrar Ausencia</span>
            </button>
          </div>
        </div>
      </section>
    </div>

    <section class="doc-card" style="margin-top:14px;">
      <div class="doc-card-h">Historial de ausencias</div>
      <div class="doc-card-b">
        <div id="historyLoader" class="loading-overlay hidden">
          <div class="spinner"></div>
          <span>Cargando historial...</span>
        </div>
        <div class="history-list" id="historyList"></div>
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
      <p style="margin-top:10px;">¿Deseas registrar de todas formas?</p>
    </div>
    <div class="modal-f">
      <button class="btn btn-outline btn-sm" id="btnCancelConflict">Cancelar</button>
      <button class="btn btn-primary btn-sm" id="btnConfirmConflict">Confirmar registro</button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var TOKEN = localStorage.getItem('auth_token');
  if (!TOKEN) { window.location.href = '/'; return; }

  var DIAS = ['DOM', 'LUN', 'MAR', 'MIE', 'JUE', 'VIE', 'SAB'];
  var DIAS_EN = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];

  var absenceTypes = [];
  var schedules = [];
  var absences = [];
  var selectedTeacherExternalId = null;

  var today = new Date(); today.setHours(0,0,0,0);
  var viewYear = today.getFullYear();
  var viewMonth = today.getMonth();
  var selectedStart = null;
  var selectedEnd = null;
  var pendingConfirmed = null;

  var $ = function (id) { return document.getElementById(id); };

  function apiHeaders() {
    return {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + TOKEN
    };
  }

  function apiGet(url) {
    return fetch(url, { method: 'GET', headers: apiHeaders() }).then(function (r) {
      if (r.status === 401) { localStorage.clear(); window.location.href = '/'; throw new Error('No autenticado'); }
      return r.json().then(function (d) { if (!r.ok) throw d; return d; });
    });
  }

  function apiPost(url, body) {
    return fetch(url, { method: 'POST', headers: apiHeaders(), body: JSON.stringify(body) }).then(function (r) {
      if (r.status === 401) { localStorage.clear(); window.location.href = '/'; throw new Error('No autenticado'); }
      return r.json().then(function (d) { if (!r.ok) throw d; return d; });
    });
  }

  function init() {
    $('calWeekDays').innerHTML = ['Dom','Lun','Mar','Mie','Jue','Vie','Sab'].map(function (w) { return '<div class="cal-wd">' + w + '</div>'; }).join('');

    Promise.all([
      apiGet('/api/v1/auth/me'),
      apiGet('/api/v1/absence-types'),
    ]).then(function (results) {
      var meResp = results[0];
      var typesResp = results[1];
      var me = meResp.data;
      absenceTypes = typesResp.data || [];

      $('docSub').textContent = 'Docente: ' + (me.fullName || me.email) + ' (' + (me.externalId || '') + ')';

      populateTypeDropdown();

      var fDoc = $('fDocente');
      fDoc.innerHTML = '<option value="' + me.externalId + '">' + (me.fullName || me.email) + ' (' + me.externalId + ')</option>';
      fDoc.value = me.externalId;
      selectedTeacherExternalId = me.externalId;

      return loadTeacherData().then(function () {
        $('mainLoader').classList.add('hidden');
        $('mainContent').classList.remove('hidden');

        // Smooth scroll to form if hash is #registrar
        if (window.location.hash === '#registrar') {
          setTimeout(function() {
            var el = $('formRegistrar');
            if (el) el.scrollIntoView({ behavior: 'smooth' });
          }, 300);
        }
      });
    })['catch'](function (err) {
      if (err && err.message) {
        showToast('Error', err.message);
      } else {
        showToast('Error', 'No se pudieron cargar los datos.');
      }
      $('mainLoader').classList.add('hidden');
      $('mainContent').classList.remove('hidden');
    });
  }

  function loadTeacherData() {
    $('mainLoader').classList.remove('hidden');
    $('mainContent').classList.add('hidden');

    return Promise.all([
      apiGet('/api/v1/my-schedules'),
      apiGet('/api/v1/my-absences')
    ]).then(function (res) {
      schedules = res[0].data || [];
      absences = res[1].data || [];

      $('mainLoader').classList.add('hidden');
      $('mainContent').classList.remove('hidden');

      renderCalendar();
      renderHistory();
      refreshAffectedClasses();
    })['catch'](function (err) {
      showToast('Error', 'No se pudieron cargar los datos del docente.');
      $('mainLoader').classList.add('hidden');
      $('mainContent').classList.remove('hidden');
    });
  }

  function populateTypeDropdown() {
    var sel = $('fTipo');
    sel.innerHTML = '<option value="">Selecciona...</option>';
    (absenceTypes || []).forEach(function (t) {
      var opt = document.createElement('option');
      opt.value = t.id;
      opt.textContent = t.name;
      sel.appendChild(opt);
    });
  }

  var fInicio = $('fInicio');
  var fFin = $('fFin');
  fInicio.min = toIso(today);
  fFin.min = toIso(today);

  function toIso(d) {
    var y = d.getFullYear();
    var m = String(d.getMonth() + 1).padStart(2, '0');
    var day = String(d.getDate()).padStart(2, '0');
    return y + '-' + m + '-' + day;
  }

  function parseIso(str) { var d = new Date(str + 'T00:00:00'); d.setHours(0,0,0,0); return d; }
  function sameDate(a, b) { return a && b && a.getTime() === b.getTime(); }
  function inRange(d, a, b) { return a && b && d >= a && d <= b; }

  function hasAbsenceOn(dateIso) {
    return (absences || []).some(function (a) {
      return a.startDate <= dateIso && a.endDate >= dateIso;
    });
  }

  function renderCalendar() {
    var monthDate = new Date(viewYear, viewMonth, 1);
    var monthName = monthDate.toLocaleDateString('es-MX', { month: 'long', year: 'numeric' });
    $('calMonthLabel').textContent = monthName.charAt(0).toUpperCase() + monthName.slice(1);

    var firstDay = new Date(viewYear, viewMonth, 1);
    var lastDay = new Date(viewYear, viewMonth + 1, 0);
    var totalCells = Math.ceil((firstDay.getDay() + lastDay.getDate()) / 7) * 7;
    var html = '';
    for (var i = 0; i < totalCells; i += 1) {
      var dayNum = i - firstDay.getDay() + 1;
      var d = new Date(viewYear, viewMonth, dayNum);
      var isCurrent = d.getMonth() === viewMonth;
      var disabled = d < today;
      var dateIso = toIso(d);
      var cls = [
        'cal-day',
        !isCurrent ? 'muted' : '',
        disabled ? 'disabled' : '',
        sameDate(d, selectedStart) ? 'start' : '',
        sameDate(d, selectedEnd) ? 'end' : '',
        inRange(d, selectedStart, selectedEnd) && !sameDate(d, selectedStart) && !sameDate(d, selectedEnd) ? 'in-range' : '',
        isCurrent && !disabled && hasAbsenceOn(dateIso) ? 'absence' : ''
      ].filter(Boolean).join(' ');
      html += '<button type="button" class="' + cls + '" data-date="' + dateIso + '" ' + (disabled ? 'disabled' : '') + '>' + d.getDate() + '</button>';
    }
    $('calDays').innerHTML = html;
  }

  function updateRangeFromCalendar(dateIso) {
    var d = parseIso(dateIso);
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

  function getWeekdayNameAbbr(dateObj) {
    return DIAS[dateObj.getDay()];
  }

  function getApiWeekdayName(dateObj) {
    return DIAS_EN[dateObj.getDay()];
  }

  function refreshAffectedClasses() {
    var list = $('affectedList');
    if (!selectedStart || !selectedEnd) {
      list.innerHTML = '<div class="class-item">Define fecha inicio y fin para visualizar clases afectadas.</div>';
      $('affectedCount').textContent = '0 clases';
      return;
    }
    var affected = [];
    for (var d = new Date(selectedStart); d <= selectedEnd; d.setDate(d.getDate() + 1)) {
      var apiDay = getApiWeekdayName(d);
      (schedules || []).forEach(function (c) {
        if (c.weekday === apiDay) {
          affected.push({ materia: c.subjectName || 'N/A', aula: 'Aula #' + (c.classroomId || ''), dia: getWeekdayNameAbbr(d), hora: (c.startTime || '') + '-' + (c.endTime || ''), fecha: toIso(new Date(d)) });
        }
      });
    }
    $('affectedCount').textContent = affected.length + ' clase(s)';
    if (!affected.length) {
      list.innerHTML = '<div class="class-item">No hay clases programadas del docente en ese período.</div>';
      return;
    }
    list.innerHTML = affected.map(function (a) {
      return '<div class="class-item"><b>' + a.materia + '</b><br>' + a.aula + ' \u00B7 ' + a.dia + ' \u00B7 ' + a.hora + '<br>Fecha: ' + a.fecha + '</div>';
    }).join('');
  }

  function clearErrs() { ['eDocente','eTipo','eInicio','eFin'].forEach(function (id) { $(id).classList.remove('show'); $(id).textContent = ''; }); }

  function validate() {
    clearErrs();
    var ok = true;
    if (!selectedTeacherExternalId) { setErr('eDocente', 'Selecciona un docente.'); ok = false; }
    if (!$('fTipo').value) { setErr('eTipo', 'Selecciona un tipo de ausencia.'); ok = false; }
    if (!fInicio.value) { setErr('eInicio', 'Define fecha inicio.'); ok = false; }
    if (!fFin.value) { setErr('eFin', 'Define fecha fin.'); ok = false; }
    if (fInicio.value && parseIso(fInicio.value) < today) { setErr('eInicio', 'No se permiten fechas en pasado.'); ok = false; }
    if (fFin.value && parseIso(fFin.value) < today) { setErr('eFin', 'No se permiten fechas en pasado.'); ok = false; }
    if (fInicio.value && fFin.value && parseIso(fFin.value) < parseIso(fInicio.value)) { setErr('eFin', 'La fecha fin debe ser mayor o igual a inicio.'); ok = false; }
    return ok;
  }

  function setErr(id, msg) { var e = $(id); e.textContent = msg; e.classList.add('show'); }

  function registerAbsence(isConfirmed) {
    var btn = $('btnRegistrar');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner" style="width:16px;height:16px;border-width:2px;margin:0 auto;"></div>';

    var body = {
      teacher_external_id: selectedTeacherExternalId,
      absence_type_id: parseInt($('fTipo').value, 10),
      start_date: fInicio.value,
      end_date: fFin.value,
      observations: $('fNotas').value.trim() || null
    };
    if (isConfirmed) { body.is_confirmed = true; }

    apiPost('/api/v1/my-absences', body).then(function () {
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-save"></i><span>Registrar Ausencia</span>';
      var countText = $('affectedCount').textContent.split(' ')[0] || '0';
      showToast('Ausencia registrada', 'Ausencia registrada exitosamente. ' + countText + ' clases marcadas.');
      $('fNotas').value = '';
      return loadTeacherData();
    })['catch'](function (err) {
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-save"></i><span>Registrar Ausencia</span>';

      if (err && err.errors && err.errors.overlap && !isConfirmed) {
        var overlap = err.errors.overlap;
        showConflictModal(overlap);
        return;
      }

      if (err && err.errors && typeof err.errors === 'object' && !Array.isArray(err.errors)) {
        showFieldErrors(err.errors);
      } else if (err && err.message) {
        showToast('Error', err.message);
      } else {
        showToast('Error', 'No se pudo registrar la ausencia.');
      }
    });
  }

  function showFieldErrors(errors) {
    if (errors.teacher_external_id) { setErr('eDocente', errors.teacher_external_id.join('; ')); }
    if (errors.absence_type_id) { setErr('eTipo', errors.absence_type_id.join('; ')); }
    if (errors.start_date) { setErr('eInicio', errors.start_date.join('; ')); }
    if (errors.end_date) { setErr('eFin', errors.end_date.join('; ')); }
    if (errors.observations) { showToast('Error', errors.observations.join('; ')); }
  }

  function showConflictModal(overlap) {
    var detail = 'Tipo: ' + (overlap.absenceTypeName || overlap.absence_type_name || 'N/A') + '<br>';
    detail += 'Rango: ' + (overlap.startDate || overlap.start_date || '') + ' al ' + (overlap.endDate || overlap.end_date || '') + '<br>';
    if (overlap.observations) { detail += 'Notas: ' + overlap.observations; }
    $('conflictDetail').innerHTML = detail;
    pendingConfirmed = true;
    $('conflictModal').classList.add('show');
  }

  function hideConflictModal() { pendingConfirmed = false; $('conflictModal').classList.remove('show'); }

  function renderHistory() {
    var list = $('historyList');
    if (!absences || absences.length === 0) {
      list.innerHTML = '<div class="history-empty">Sin ausencias registradas.</div>';
      return;
    }
    list.innerHTML = absences.map(function (a) {
      var typeName = a.absenceType ? a.absenceType.name : 'Tipo #' + a.absenceTypeId;
      return '<div class="history-item">' +
        '<div><span class="h-type">' + typeName + '</span> <span class="h-date">' + a.startDate + ' al ' + a.endDate + '</span></div>' +
        (a.observations ? '<div style="margin-top:3px;color:var(--dark-graphite);">' + a.observations + '</div>' : '') +
        '</div>';
    }).join('');
  }

  function showToast(title, message) {
    var t = document.createElement('div');
    t.className = 'toast success';
    t.innerHTML = '<div class="toast-icon"><i class="fas fa-check"></i></div><div class="toast-content"><div class="toast-title">' + title + '</div><div class="toast-message">' + message + '</div></div><button class="toast-close"><i class="fas fa-times"></i></button>';
    $('toastContainer').appendChild(t);
    setTimeout(function () { t.classList.add('show'); }, 10);
    var rm = function () { t.classList.remove('show'); setTimeout(function () { t.remove(); }, 260); };
    var tm = setTimeout(rm, 4500);
    t.querySelector('.toast-close').addEventListener('click', function () { clearTimeout(tm); rm(); });
  }

  $('btnPrevMonth').addEventListener('click', function () { viewMonth -= 1; if (viewMonth < 0) { viewMonth = 11; viewYear -= 1; } renderCalendar(); });
  $('btnNextMonth').addEventListener('click', function () { viewMonth += 1; if (viewMonth > 11) { viewMonth = 0; viewYear += 1; } renderCalendar(); });
  $('calDays').addEventListener('click', function (e) {
    var btn = e.target.closest('[data-date]');
    if (!btn || btn.disabled) return;
    updateRangeFromCalendar(btn.dataset.date);
  });
  fInicio.addEventListener('change', refreshFromInputs);
  fFin.addEventListener('change', refreshFromInputs);

  $('btnRegistrar').addEventListener('click', function () {
    if (!validate()) return;
    registerAbsence(false);
  });

  $('btnCancelConflict').addEventListener('click', hideConflictModal);
  $('btnConfirmConflict').addEventListener('click', function () {
    if (pendingConfirmed) registerAbsence(true);
    hideConflictModal();
  });
  $('conflictModal').addEventListener('click', function (e) { if (e.target.id === 'conflictModal') hideConflictModal(); });

  init();
});
</script>
@endsection
