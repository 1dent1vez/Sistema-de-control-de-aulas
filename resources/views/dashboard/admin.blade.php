{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Dashboard Administrativo con KPIs reales del API
 * @autor          Equipo GAMA
 * @autorizador    Rubén Alejandro Nolasco Ruiz
 * @prueba         Diego Miguel Hernandez Fabela
 * @mantenimiento  Ghael Garcia Manjarrez
 * @version        1.0.0
 * @creado         19/05/2026
 * @modificado     19/05/2026
 */
--}}

@extends('layouts.app')

@section('title', 'Dashboard Administrativo - GAMA Solutions')

@section('content')
<style>
  .dm-main { margin-left: var(--sidebar-width, 240px); min-height: 100vh; background: var(--ice-blue); padding: 28px 32px; }
  .dm-head { display:flex; justify-content:space-between; gap:12px; align-items:center; flex-wrap:wrap; margin-bottom:16px; }
  .dm-title { font-size:26px; font-weight:700; color:var(--midnight); margin-bottom:4px; }
  .dm-sub { color:var(--soft-steel); font-size:14px; }
  .dm-actions { display:flex; gap:8px; flex-wrap:wrap; }
  .dm-kpi-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px,1fr)); gap:14px; margin-bottom:20px; }
  .kpi-card { background:#fff; border:1px solid var(--mist-blue); border-radius:var(--radius-lg); padding:18px 20px; display:flex; gap:14px; align-items:center; }
  .kpi-icon { width:44px; height:44px; border-radius:var(--radius-md); background:rgba(19,68,116,.08); color:var(--deep-blue); display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
  .kpi-val { font-size:24px; font-weight:800; color:var(--midnight); line-height:1.2; }
  .kpi-val.shimmer { display:inline-block; width:40px; height:24px; background:linear-gradient(90deg,#e0e7ef 25%,#f0f4f8 50%,#e0e7ef 75%); background-size:200% 100%; animation:shimmer 1.2s infinite; border-radius:6px; }
  .kpi-label { font-size:12px; color:var(--soft-steel); }
  .shimmer { background:linear-gradient(90deg,#e0e7ef 25%,#f0f4f8 50%,#e0e7ef 75%); background-size:200% 100%; animation:shimmer 1.2s infinite; border-radius:6px; }
  .shimmer-inline { display:inline-block; width:50px; height:14px; }
  @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
  .dm-card { background:#fff; border:1px solid var(--mist-blue); border-radius:var(--radius-lg); overflow:hidden; }
  .dm-card-h { padding:14px 18px; border-bottom:1px solid var(--mist-blue); display:flex; justify-content:space-between; align-items:center; }
  .dm-card-h h2 { font-size:15px; font-weight:700; color:var(--deep-blue); }
  .dm-card-h small { color:var(--soft-steel); font-size:12px; }
  .dm-table-wrap { overflow-x:auto; }
  .dm-table { width:100%; border-collapse:collapse; font-size:13px; }
  .dm-table thead th { background:var(--deep-blue); color:#fff; text-align:left; padding:11px 14px; font-weight:600; }
  .dm-table tbody tr:nth-child(odd){ background:var(--light-blue);}
  .dm-table tbody tr:nth-child(even){ background:#fff;}
  .dm-table tbody tr:hover{ background:var(--light-orange);}
  .dm-table td{ padding:11px 14px; border-bottom:1px solid var(--mist-blue); }
  @media (max-width:1024px){ .dm-main{ margin-left:0; } }
</style>

<div class="dm-main">
  <div class="dm-head">
    <div>
      <h1 class="dm-title">Dashboard Administrativo</h1>
      <p class="dm-sub">Resumen operativo del Sistema de Control de Aulas.</p>
    </div>
    <div class="dm-actions">
      <a href="{{ route('edificios') }}" class="btn btn-secondary btn-sm"><i class="fas fa-building"></i> Edificios</a>
      <a href="{{ route('aulas') }}" class="btn btn-secondary btn-sm"><i class="fas fa-school"></i> Aulas</a>
      <a href="{{ route('horarios.manual') }}" class="btn btn-secondary btn-sm"><i class="fas fa-calendar-alt"></i> Horarios</a>
      <a href="{{ route('codigosqr') }}" class="btn btn-secondary btn-sm"><i class="fas fa-qrcode"></i> QR</a>
    </div>
  </div>

  <div class="dm-kpi-grid" id="kpiGrid">
    <article class="kpi-card">
      <div class="kpi-icon"><i class="fas fa-building"></i></div>
      <div><div class="kpi-val shimmer" id="kpiBuildings"></div><div class="kpi-label">Total edificios</div></div>
    </article>
    <article class="kpi-card">
      <div class="kpi-icon"><i class="fas fa-school"></i></div>
      <div><div class="kpi-val shimmer" id="kpiClassrooms"></div><div class="kpi-label">Total aulas</div></div>
    </article>
    <article class="kpi-card">
      <div class="kpi-icon"><i class="fa-solid fa-book-open-reader"></i></div>
      <div><div class="kpi-val shimmer" id="kpiSchedules"></div><div class="kpi-label">Clases activas</div></div>
    </article>
    <article class="kpi-card">
      <div class="kpi-icon"><i class="fas fa-graduation-cap"></i></div>
      <div><div class="kpi-val shimmer" id="kpiSemester"></div><div class="kpi-label">Semestre activo</div></div>
    </article>
    <article class="kpi-card">
      <div class="kpi-icon"><i class="fas fa-user-clock"></i></div>
      <div><div class="kpi-val shimmer" id="kpiAbsences"></div><div class="kpi-label">Ausencias (mes actual)</div></div>
    </article>
  </div>

  <section class="dm-card">
    <div class="dm-card-h">
      <h2>Horarios de hoy</h2>
      <small id="todayLabel"></small>
    </div>
    <div class="dm-table-wrap">
      <table class="dm-table">
        <thead><tr><th>Hora</th><th>Aula</th><th>Materia</th><th>Docente</th><th>Grupo</th><th>Edificio</th></tr></thead>
        <tbody id="scheduleBody"><tr><td colspan="6" style="text-align:center;color:var(--soft-steel);padding:26px;"><span class="shimmer shimmer-inline"></span> Cargando...</td></tr></tbody>
      </table>
    </div>
  </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var TOKEN = localStorage.getItem('auth_token');
  if (!TOKEN) { window.location.href = '/'; return; }
  var HEADERS = { 'Accept': 'application/json', 'Authorization': 'Bearer ' + TOKEN };
  var $ = function (id) { return document.getElementById(id); };

  var now = new Date();
  var month = String(now.getMonth() + 1).padStart(2, '0');
  var year = now.getFullYear();
  var monthStart = year + '-' + month + '-01';
  var monthEnd = year + '-' + month + '-' + new Date(year, now.getMonth() + 1, 0).getDate();
  $('todayLabel').textContent = now.toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

  var weekdayMap = { 0: 'DOM', 1: 'LUN', 2: 'MAR', 3: 'MIE', 4: 'JUE', 5: 'VIE', 6: 'SAB' };
  var todayWeekday = weekdayMap[now.getDay()];

  function setKpi(id, val) {
    var el = $(id);
    el.textContent = val;
    el.classList.remove('shimmer');
  }

  function buildUrl(base, params) {
    var q = Object.keys(params).filter(function (k) { return params[k] !== undefined && params[k] !== null && params[k] !== ''; }).map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]); }).join('&');
    return base + (q ? '?' + q : '');
  }

  function handleUnauthorized(r) { if (r.status === 401) { localStorage.clear(); window.location.href = '/'; throw new Error('No autenticado'); } return r; }

  function fetchJson(url) {
    return fetch(url, { headers: HEADERS }).then(handleUnauthorized).then(function (r) { return r.ok ? r.json() : { data: [] }; });
  }

  function getClassroomName(map, id) {
    return map[id] || 'Aula #' + id;
  }

  function getBuildingName(map, id) {
    return map[id] || 'Desconocido';
  }

  Promise.all([
    fetchJson('/api/v1/buildings'),
    fetchJson('/api/v1/classrooms'),
    fetchJson('/api/v1/class-schedules'),
    fetchJson('/api/v1/semesters/current'),
    fetchJson(buildUrl('/api/v1/teacher-absences', { start_date: monthStart, end_date: monthEnd }))
  ]).then(function (results) {
    var buildingsData = results[0].data || [];
    var classroomsData = results[1].data || [];
    var schedulesData = results[2].data || [];
    var semesterData = results[3].data || null;
    var absencesData = results[4].data || [];

    var buildingMap = {};
    buildingsData.forEach(function (b) { buildingMap[b.id] = b.name; });
    var classroomMap = {};
    classroomsData.forEach(function (c) { classroomMap[c.id] = c.classroomName; });

    setKpi('kpiBuildings', buildingsData.length);
    setKpi('kpiClassrooms', classroomsData.length);
    setKpi('kpiSchedules', schedulesData.length);
    setKpi('kpiSemester', semesterData ? semesterData.name : 'N/A');
    setKpi('kpiAbsences', absencesData.length);

    var tbody = $('scheduleBody');
    var todaySchedules = schedulesData.filter(function (s) { return s.weekday === todayWeekday; });

    if (todaySchedules.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--soft-steel);padding:26px;">No hay clases programadas para hoy.</td></tr>';
      return;
    }

    tbody.innerHTML = todaySchedules.map(function (s) {
      var hora = (s.startTime || '').substring(0, 5) + ' - ' + (s.endTime || '').substring(0, 5);
      return '<tr>' +
        '<td>' + hora + '</td>' +
        '<td>' + getClassroomName(classroomMap, s.classroomId) + '</td>' +
        '<td>' + (s.subjectName || '--') + '</td>' +
        '<td>' + (s.teacherExternalId || '--') + '</td>' +
        '<td>' + (s.groupName || '--') + '</td>' +
        '<td>' + getBuildingName(buildingMap, classroomsData.find(function (c) { return c.id === s.classroomId; })?.buildingId) + '</td>' +
        '</tr>';
    }).join('');
  })['catch'](function () {
    setKpi('kpiBuildings', 'Error');
    setKpi('kpiClassrooms', 'Error');
    setKpi('kpiSchedules', 'Error');
    setKpi('kpiSemester', 'Error');
    setKpi('kpiAbsences', 'Error');
    $('scheduleBody').innerHTML = '<tr><td colspan="6" style="text-align:center;color:#b00000;padding:26px;">Error al cargar datos del servidor</td></tr>';
  });
});
</script>
@endsection
