{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Gestión de Ausencias Docentes - Panel de Administración
 * @autor          Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 * @autorizador    Ruben Alejandro Nolasco Ruiz
 * @prueba         Diego Miguel Hernandez Fabela
 * @mantenimiento  Ghael Garcia Manjarrez
 * @version        1.0.0
 * @creado         2026-05-25
 * @modificado     2026-05-25
 * @cambios        2026-05-25 - Creación inicial de la vista de administración de ausencias.
 */
--}}

@extends('layouts.app')

@section('title', 'Gestión de Ausencias - GAMA Solutions')

@section('content')
<style>
  .abs-main {
    margin-left: var(--sidebar-width, 240px);
    min-height: 100vh;
    background: var(--ice-blue);
    padding: 28px 32px;
  }

  .abs-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 20px;
  }

  .abs-title {
    font-size: 26px;
    color: var(--midnight);
    font-weight: 700;
    margin-bottom: 4px;
  }

  .abs-subtitle {
    color: var(--soft-steel);
    font-size: 14px;
  }

  .abs-card {
    background: #fff;
    border: 1px solid var(--mist-blue);
    border-radius: var(--radius-lg);
    overflow: hidden;
    margin-bottom: 20px;
  }

  .abs-toolbar {
    padding: 16px 22px;
    border-bottom: 1px solid var(--mist-blue);
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
  }

  .abs-filters {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
  }

  .abs-select, .abs-input {
    border: 1px solid var(--mist-blue);
    background: var(--ice-blue);
    border-radius: var(--radius-md);
    padding: 9px 12px;
    font-size: 13px;
    font-family: var(--font-main);
    color: var(--dark-graphite);
    outline: none;
  }

  .abs-select:focus, .abs-input:focus {
    border-color: var(--corp-orange);
  }

  .abs-table-wrap {
    overflow-x: auto;
  }

  .abs-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
  }

  .abs-table thead th {
    background: var(--deep-blue);
    color: #fff;
    text-align: left;
    padding: 12px 14px;
    font-weight: 600;
  }

  .abs-table tbody tr:nth-child(odd) { background: var(--light-blue); }
  .abs-table tbody tr:nth-child(even) { background: #fff; }
  .abs-table tbody tr:hover { background: var(--light-orange); }

  .abs-table tbody td {
    padding: 12px 14px;
    border-bottom: 1px solid var(--mist-blue);
    color: var(--dark-graphite);
    vertical-align: middle;
  }

  .badge-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
  }

  .badge-status.confirmed {
    background: rgba(90,154,90,0.14);
    color: var(--status-active);
  }

  .badge-status.pending {
    background: rgba(242,139,44,0.18);
    color: var(--deep-orange);
  }

  .badge-count {
    background: var(--deep-blue);
    color: #fff;
    padding: 2px 7px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 700;
  }

  .modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(19,68,116,0.4);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 2000;
  }

  .modal-overlay.show {
    display: flex;
  }

  .modal-card {
    width: 94%;
    max-width: 600px;
    background: #fff;
    border-radius: var(--radius-lg);
    border: 1px solid var(--mist-blue);
    overflow: hidden;
  }

  .modal-h {
    padding: 14px 16px;
    border-bottom: 1px solid var(--mist-blue);
    font-weight: 700;
    color: var(--midnight);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .modal-b {
    padding: 16px;
    font-size: 14px;
    color: var(--dark-graphite);
  }

  .modal-f {
    padding: 12px 16px 16px;
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    border-top: 1px solid var(--mist-blue);
  }

  .class-list {
    display: grid;
    gap: 8px;
    max-height: 250px;
    overflow-y: auto;
    margin-top: 10px;
  }

  .class-item {
    border: 1px solid var(--mist-blue);
    border-radius: var(--radius-md);
    padding: 8px 10px;
    background: var(--ice-blue);
    font-size: 12px;
    line-height: 1.4;
  }

  .class-item b {
    color: var(--midnight);
  }

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px;
    margin-bottom: 20px;
  }

  .stat-card {
    background: #fff;
    border: 1px solid var(--mist-blue);
    border-radius: var(--radius-lg);
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 14px;
  }

  .stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(19, 68, 116, 0.1);
    color: var(--deep-blue);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
  }

  .stat-num {
    font-size: 22px;
    font-weight: 700;
    color: var(--midnight);
  }

  .stat-label {
    font-size: 12px;
    color: var(--soft-steel);
  }

  .loading-overlay {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 40px;
    color: var(--soft-steel);
  }

  .hidden {
    display: none !important;
  }

  .spinner {
    display: inline-block;
    width: 24px;
    height: 24px;
    border: 3px solid var(--mist-blue);
    border-top-color: var(--deep-blue);
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
  }

  @keyframes spin { to { transform: rotate(360deg); } }

  @media (max-width: 1024px) {
    .abs-main { margin-left: 0; }
  }
</style>

<div class="abs-main">
  <div class="abs-header">
    <div>
      <h1 class="abs-title">Gestión de Ausencias</h1>
      <p class="abs-subtitle">Panel para visualizar, filtrar y dar seguimiento a las ausencias de los docentes.</p>
    </div>
  </div>

  <!-- KPIs / Estadísticas -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-user-times"></i></div>
      <div>
        <div class="stat-num" id="statTotalAbsences">0</div>
        <div class="stat-label">Total Ausencias</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
      <div>
        <div class="stat-num" id="statTotalDays">0</div>
        <div class="stat-label">Días Totales Ausente</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-school"></i></div>
      <div>
        <div class="stat-num" id="statClassesAffected">0</div>
        <div class="stat-label">Clases Afectadas</div>
      </div>
    </div>
  </div>

  <section class="abs-card">
    <div class="abs-toolbar">
      <div class="abs-filters">
        <!-- Filtro Docente -->
        <select id="filterDocente" class="abs-select">
          <option value="">Todos los docentes</option>
        </select>
        <!-- Filtro Tipo -->
        <select id="filterTipo" class="abs-select">
          <option value="">Todos los tipos</option>
        </select>
        <!-- Rango de Fechas -->
        <input id="filterInicio" class="abs-input" type="date" placeholder="Desde">
        <input id="filterFin" class="abs-input" type="date" placeholder="Hasta">
        <!-- Botón limpiar -->
        <button id="btnLimpiar" class="btn btn-secondary btn-sm" style="height: 36px;"><i class="fas fa-eraser"></i></button>
      </div>
      <span id="resultsInfo" style="font-size:12px;color:var(--soft-steel);">0 registros</span>
    </div>

    <div id="loader" class="loading-overlay">
      <div class="spinner"></div>
      <span>Cargando información...</span>
    </div>

    <div class="abs-table-wrap">
      <table class="abs-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Docente</th>
            <th>Tipo de Ausencia</th>
            <th>Rango de Fechas</th>
            <th>Clases Afectadas</th>
            <th>Observaciones</th>
            <th>Conflicto</th>
            <th style="width:100px;">Detalles</th>
          </tr>
        </thead>
        <tbody id="absencesBody">
          <tr>
            <td colspan="8" style="text-align:center;padding:24px;color:var(--soft-steel);">Sin registros.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</div>

<!-- Modal Detalle -->
<div class="modal-overlay" id="detailModal">
  <div class="modal-card">
    <div class="modal-h">
      <span>Detalle de Ausencia Docente</span>
      <button class="btn btn-ghost btn-sm" id="btnExitModal" style="padding:0;width:30px;height:30px;"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-b">
      <p style="margin-bottom:12px;"><strong>Docente:</strong> <span id="detDocente"></span></p>
      <p style="margin-bottom:12px;"><strong>Tipo de Ausencia:</strong> <span id="detTipo"></span></p>
      <p style="margin-bottom:12px;"><strong>Período:</strong> <span id="detPeriodo"></span></p>
      <p style="margin-bottom:12px;"><strong>Observaciones:</strong> <span id="detObs"></span></p>
      
      <div style="border-top:1px solid var(--mist-blue);margin-top:14px;padding-top:14px;">
        <strong>Clases Afectadas en el Período:</strong>
        <div class="class-list" id="detClassList"></div>
      </div>
    </div>
    <div class="modal-f">
      <button class="btn btn-primary btn-sm" id="btnCloseModal">Cerrar</button>
    </div>
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const TOKEN = localStorage.getItem('auth_token');
  if (!TOKEN) { window.location.href = '/'; return; }

  const $ = (id) => document.getElementById(id);
  const DIAS = { sunday: 'DOM', monday: 'LUN', tuesday: 'MAR', wednesday: 'MIE', thursday: 'JUE', friday: 'VIE', saturday: 'SAB' };

  let absenceTypes = [];
  let teachers = [];
  let absencesList = [];

  function apiHeaders() {
    return {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + TOKEN
    };
  }

  async function apiGet(url) {
    const res = await fetch(url, { method: 'GET', headers: apiHeaders() });
    if (res.status === 401) {
      localStorage.clear();
      window.location.href = '/';
      throw new Error('No autorizado');
    }
    const data = await res.json();
    if (!res.ok) throw data;
    return data;
  }

  function showToast(title, message, type = 'success') {
    const wrap = $('toastContainer');
    const icon = type === 'success' ? 'check' : 'times';
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
      <div class="toast-icon"><i class="fas fa-${icon}"></i></div>
      <div class="toast-content">
        <div class="toast-title">${title}</div>
        <div class="toast-message">${message}</div>
      </div>
      <button class="toast-close"><i class="fas fa-times"></i></button>`;
    wrap.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 4000);
    toast.querySelector('.toast-close').addEventListener('click', () => { toast.remove(); });
  }

  async function init() {
    try {
      $('loader').classList.remove('hidden');
      
      // 1. Cargar Catálogos
      const typesResp = await apiGet('/api/v1/absence-types');
      absenceTypes = typesResp.data ?? [];
      populateTypeDropdown();

      const teachersResp = await apiGet('/api/v1/sam-identities/teachers');
      teachers = teachersResp.data ?? [];
      populateTeacherDropdown();

      // 2. Cargar ausencias
      await loadAbsences();
    } catch (e) {
      showToast('Error', 'No se pudieron cargar los datos de administración.', 'error');
    } finally {
      $('loader').classList.add('hidden');
    }
  }

  function populateTypeDropdown() {
    const sel = $('filterTipo');
    sel.innerHTML = '<option value="">Todos los tipos</option>';
    absenceTypes.forEach(t => {
      sel.innerHTML += `<option value="${t.id}">${t.name}</option>`;
    });
  }

  function populateTeacherDropdown() {
    const sel = $('filterDocente');
    sel.innerHTML = '<option value="">Todos los docentes</option>';
    teachers.forEach(u => {
      sel.innerHTML += `<option value="${u.externalId}">${u.fullName || u.email || u.externalId}</option>`;
    });
  }

  async function loadAbsences() {
    try {
      const doc = $('filterDocente').value;
      const tipo = $('filterTipo').value;
      const inicio = $('filterInicio').value;
      const fin = $('filterFin').value;

      let url = '/api/v1/teacher-absences?';
      if (doc) url += `teacher_external_id=${encodeURIComponent(doc)}&`;
      if (inicio) url += `start_date=${encodeURIComponent(inicio)}&`;
      if (fin) url += `end_date=${encodeURIComponent(fin)}&`;

      const absResp = await apiGet(url);
      absencesList = absResp.data ?? [];

      // Filtrado por tipo en frontend si no lo hace el backend de forma directa
      if (tipo) {
        absencesList = absencesList.filter(a => String(a.absenceTypeId) === String(tipo));
      }

      renderTable();
      await loadStats(doc);
    } catch (e) {
      showToast('Error', 'No se pudo refrescar el listado de ausencias.', 'error');
    }
  }

  async function loadStats(teacherId) {
    try {
      let url = '/api/v1/teacher-absences/stats?';
      if (teacherId) url += `teacher_external_id=${encodeURIComponent(teacherId)}&`;

      const statsResp = await apiGet(url);
      const s = statsResp.data;

      $('statTotalAbsences').textContent = s.totalAbsences ?? 0;
      $('statTotalDays').textContent = s.totalDaysAbsent ?? 0;

      // Calcular clases totales afectadas a partir de la lista cargada
      let affectedSum = 0;
      absencesList.forEach(a => {
        if (a.classSchedules) affectedSum += a.classSchedules.length;
      });
      $('statClassesAffected').textContent = affectedSum;

    } catch (e) {
      // Ignorar fallo de stats menor
    }
  }

  function renderTable() {
    const body = $('absencesBody');
    $('resultsInfo').textContent = `${absencesList.length} registro(s) encontrado(s)`;

    if (absencesList.length === 0) {
      body.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--soft-steel);">Sin registros que coincidan con los filtros.</td></tr>';
      return;
    }

    body.innerHTML = absencesList.map((a, idx) => {
      const typeObj = absenceTypes.find(t => t.id === a.absenceTypeId);
      const typeName = typeObj ? typeObj.name : `Tipo #${a.absenceTypeId}`;
      const teacherObj = teachers.find(t => t.externalId === a.teacherExternalId);
      const teacherName = teacherObj ? (teacherObj.fullName || teacherObj.email) : a.teacherExternalId;
      const classCount = a.classSchedules ? a.classSchedules.length : 0;

      return `
        <tr>
          <td>${idx + 1}</td>
          <td style="font-weight:600;color:var(--midnight);">${teacherName} (${a.teacherExternalId})</td>
          <td><span style="color:var(--corp-orange);font-weight:600;">${typeName}</span></td>
          <td><b>${a.startDate}</b> al <b>${a.endDate}</b></td>
          <td><span class="badge-count">${classCount} clases</span></td>
          <td>${a.observations || '<span style="color:var(--soft-steel);">Ninguna</span>'}</td>
          <td>
            <span class="badge-status ${a.isConfirmed ? 'confirmed' : 'pending'}">
              ${a.isConfirmed ? 'Confirmado' : 'Sin Conflicto'}
            </span>
          </td>
          <td>
            <button class="btn btn-secondary btn-sm" data-id="${a.id}" style="padding:0;width:34px;height:34px;" title="Ver Detalle"><i class="fas fa-eye"></i></button>
          </td>
        </tr>
      `;
    }).join('');
  }

  function showDetailModal(absenceId) {
    const a = absencesList.find(x => String(x.id) === String(absenceId));
    if (!a) return;

    const teacherObj = teachers.find(t => t.externalId === a.teacherExternalId);
    const teacherName = teacherObj ? `${teacherObj.fullName || teacherObj.email} (${a.teacherExternalId})` : a.teacherExternalId;
    const typeObj = absenceTypes.find(t => t.id === a.absenceTypeId);
    const typeName = typeObj ? typeObj.name : `Tipo #${a.absenceTypeId}`;

    $('detDocente').textContent = teacherName;
    $('detTipo').textContent = typeName;
    $('detPeriodo').textContent = `${a.startDate} al ${a.endDate}`;
    $('detObs').textContent = a.observations || 'Sin notas.';

    const cList = $('detClassList');
    if (!a.classSchedules || a.classSchedules.length === 0) {
      cList.innerHTML = '<div class="class-item">No se vieron clases afectadas en este período.</div>';
    } else {
      cList.innerHTML = a.classSchedules.map(c => {
        const dia = DIAS[c.weekday] ?? c.weekday.toUpperCase();
        return `
          <div class="class-item">
            <b>${c.subjectName}</b> \u00B7 Grupo: ${c.groupName}<br>
            Aula: ${c.classroom ? c.classroom.classroomName : `Aula #${c.classroomId}`} \u00B7 Horario: ${c.startTime}-${c.endTime} (${dia})
          </div>
        `;
      }).join('');
    }

    $('detailModal').classList.add('show');
  }

  function hideDetailModal() {
    $('detailModal').classList.remove('show');
  }

  // Eventos de Filtro
  $('filterDocente').addEventListener('change', loadAbsences);
  $('filterTipo').addEventListener('change', loadAbsences);
  $('filterInicio').addEventListener('change', loadAbsences);
  $('filterFin').addEventListener('change', loadAbsences);

  $('btnLimpiar').addEventListener('click', function () {
    $('filterDocente').value = '';
    $('filterTipo').value = '';
    $('filterInicio').value = '';
    $('filterFin').value = '';
    loadAbsences();
  });

  $('absencesBody').addEventListener('click', function (e) {
    const btn = e.target.closest('[data-id]');
    if (btn) showDetailModal(btn.dataset.id);
  });

  $('btnCloseModal').addEventListener('click', hideDetailModal);
  $('btnExitModal').addEventListener('click', hideDetailModal);
  $('detailModal').addEventListener('click', function (e) {
    if (e.target.id === 'detailModal') hideDetailModal();
  });

  init();
});
</script>
@endsection
