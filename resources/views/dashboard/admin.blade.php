{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Dashboard Administrativo completamente responsive con paleta de colores y estilos oficiales de GAMA.
 * @autor          Equipo GAMA
 * @autorizador    Rubén Alejandro Nolasco Ruiz
 * @prueba         Diego Miguel Hernandez Fabela
 * @mantenimiento  Ghael Garcia Manjarrez
 * @version        1.2.0
 * @creado         19/05/2026
 * @modificado     26/05/2026
 */
--}}

@extends('layouts.app')

@section('title', 'Dashboard Administrativo - GAMA Solutions')

@section('content')
<div class="main-content">
  <!-- HEADER DE BIENVENIDA -->
  <div class="page-header">
    <div class="header-text">
      <h1>Panel de Control</h1>
      <p>Bienvenido, <strong id="welcomeAdminName">Administrador</strong></p>
    </div>
    <div class="quick-actions">
      <span class="status status-active" id="todayLabel">...</span>
    </div>
  </div>

  <!-- STATS CARDS -->
  <div class="kpi-grid">
    <!-- Edificios -->
    <article class="kpi-card admin-kpi-card">
      <div class="kpi-icon"><i class="fas fa-building"></i></div>
      <div class="kpi-content">
        <span class="kpi-value shimmer" id="kpiBuildings"></span>
        <span class="kpi-label">Edificios</span>
      </div>
    </article>

    <!-- Aulas -->
    <article class="kpi-card admin-kpi-card">
      <div class="kpi-icon"><i class="fas fa-school"></i></div>
      <div class="kpi-content">
        <span class="kpi-value shimmer" id="kpiClassrooms"></span>
        <span class="kpi-label">Total Aulas</span>
      </div>
    </article>

    <!-- Clases Activas -->
    <article class="kpi-card admin-kpi-card">
      <div class="kpi-icon"><i class="fas fa-book-open"></i></div>
      <div class="kpi-content">
        <span class="kpi-value shimmer" id="kpiSchedules"></span>
        <span class="kpi-label">Clases Activas</span>
      </div>
    </article>

    <!-- Horarios de Hoy -->
    <article class="kpi-card admin-kpi-card">
      <div class="kpi-icon"><i class="fas fa-clock"></i></div>
      <div class="kpi-content">
        <span class="kpi-value shimmer" id="kpiTodaySchedules"></span>
        <span class="kpi-label">Clases Hoy</span>
      </div>
    </article>

    <!-- Semestre Activo -->
    <article class="kpi-card admin-kpi-card">
      <div class="kpi-icon"><i class="fas fa-graduation-cap"></i></div>
      <div class="kpi-content">
        <span class="kpi-value shimmer" id="kpiSemester"></span>
        <span class="kpi-label">Semestre Activo</span>
      </div>
    </article>

    <!-- Ausencias -->
    <article class="kpi-card admin-kpi-card">
      <div class="kpi-icon"><i class="fas fa-user-clock"></i></div>
      <div class="kpi-content">
        <span class="kpi-value shimmer" id="kpiAbsences"></span>
        <span class="kpi-label">Ausencias (Mes)</span>
      </div>
    </article>
  </div>

  <!-- MIDDLE GRID: PROGRESO SEMESTRE + ACCESOS DIRECTOS -->
  <div class="charts-grid">
    <!-- Estado del Semestre -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title"><i class="fas fa-tasks"></i> Estado del Semestre</h2>
        <span class="status status-active" id="semesterStatusBadge">Vigente</span>
      </div>
      <div class="card-body">
        <!-- Si hay semestre vigente -->
        <div id="semesterProgressSection" style="display: none; background-color: var(--ice-blue); padding: 20px; border-radius: var(--radius-md);">
          <div style="display: flex; justify-content: space-between; font-weight: 600; margin-bottom: 12px; color: var(--midnight);">
            <span id="semesterName">Semestre 2026-I</span>
            <span id="semesterPercentText">0%</span>
          </div>
          <div style="width: 100%; height: 12px; background: var(--mist-blue); border-radius: 10px; overflow: hidden; margin-bottom: 12px;">
            <div id="semesterProgressBar" style="height: 100%; background: var(--corp-orange); border-radius: 10px; width: 0%; transition: width 1s ease;"></div>
          </div>
          <div style="display: flex; justify-content: space-between; font-size: 13px; color: var(--soft-steel); font-weight: 500;">
            <span id="semesterDateRange">-- al --</span>
            <span id="semesterElapsed">0 de 0 días</span>
          </div>
        </div>

        <!-- Si no hay semestre vigente -->
        <div id="noSemesterAlert" style="display: none; background-color: rgba(201, 168, 120, 0.15); border: 1px solid rgba(201, 168, 120, 0.3); border-radius: var(--radius-md); padding: 16px 20px; color: var(--status-pending); font-weight: 500;">
          <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
          Sin semestre vigente. Registre o active uno para habilitar los horarios. 
          <a href="{{ route('horarios.semestres.index') }}" style="color: var(--corp-orange); font-weight: 600; text-decoration: underline;">Registrar Semestre</a>
        </div>
      </div>
    </div>

    <!-- Accesos Directos -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title"><i class="fas fa-compass"></i> Accesos Directos</h2>
      </div>
      <div class="card-body">
        <div class="quick-actions" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px; width: 100%;">
          <a href="{{ route('aulas') }}" class="btn btn-secondary" style="height: auto; padding: 16px 12px; flex-direction: column; gap: 8px; border-radius: var(--radius-md);">
            <i class="fas fa-school" style="font-size: 20px;"></i>
            <span style="font-size: 13px;">Ver Aulas</span>
          </a>
          <a href="{{ route('codigosqr') }}" class="btn btn-secondary" style="height: auto; padding: 16px 12px; flex-direction: column; gap: 8px; border-radius: var(--radius-md);">
            <i class="fas fa-qrcode" style="font-size: 20px;"></i>
            <span style="font-size: 13px;">Generar QR</span>
          </a>
          <a href="{{ route('edificios') }}" class="btn btn-secondary" style="height: auto; padding: 16px 12px; flex-direction: column; gap: 8px; border-radius: var(--radius-md);">
            <i class="fas fa-building" style="font-size: 20px;"></i>
            <span style="font-size: 13px;">Edificios</span>
          </a>
          <a href="{{ route('horarios.manual') }}" class="btn btn-secondary" style="height: auto; padding: 16px 12px; flex-direction: column; gap: 8px; border-radius: var(--radius-md);">
            <i class="fas fa-calendar-alt" style="font-size: 20px;"></i>
            <span style="font-size: 13px;">Horarios</span>
          </a>
          <a href="{{ route('horarios.importar') }}" class="btn btn-secondary" style="height: auto; padding: 16px 12px; flex-direction: column; gap: 8px; border-radius: var(--radius-md);">
            <i class="fas fa-file-import" style="font-size: 20px;"></i>
            <span style="font-size: 13px;">Importar</span>
          </a>
          <a href="{{ route('horarios.semestres.index') }}" class="btn btn-secondary" style="height: auto; padding: 16px 12px; flex-direction: column; gap: 8px; border-radius: var(--radius-md);">
            <i class="fas fa-calendar-check" style="font-size: 20px;"></i>
            <span style="font-size: 13px;">Semestres</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- SECCIÓN TABLA DE HORARIOS -->
  <section class="card table-card">
    <div class="card-header">
      <h2 class="card-title"><i class="fas fa-calendar-day"></i> Horarios de Hoy</h2>
    </div>
    
    <div class="table-container">
      <table class="dynamic-table admin-schedule-table">
        <thead>
          <tr>
            <th>Hora</th>
            <th>Aula</th>
            <th>Materia</th>
            <th>Docente</th>
            <th>Grupo</th>
            <th>Edificio</th>
          </tr>
        </thead>
        <tbody id="scheduleBody">
          <tr>
            <td colspan="6" style="text-align: center; color: var(--soft-steel); padding: 26px;">
              <i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i> Cargando horarios...
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>

  <!-- FOOTER -->
  <div style="margin-top: 40px; text-align: center; padding-top: 20px; border-top: 1px solid var(--mist-blue); font-size: 12px; color: var(--soft-steel); font-weight: 500;">
    <p>G.A.M.A. Solutions S.A. de C.V. — Sistema de Control de Aulas v1.2.0</p>
  </div>
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
    var q = Object.keys(params).filter(function (k) { 
      return params[k] !== undefined && params[k] !== null && params[k] !== ''; 
    }).map(function (k) { 
      return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]); 
    }).join('&');
    return base + (q ? '?' + q : '');
  }

  function handleUnauthorized(r) { 
    if (r.status === 401) { 
      localStorage.clear(); 
      window.location.href = '/'; 
      throw new Error('No autenticado'); 
    } 
    return r; 
  }

  function fetchJson(url) {
    return fetch(url, { headers: HEADERS })
      .then(handleUnauthorized)
      .then(function (r) { 
        return r.ok ? r.json() : { data: [] }; 
      });
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
    fetchJson(buildUrl('/api/v1/teacher-absences', { start_date: monthStart, end_date: monthEnd })),
    fetchJson('/api/v1/auth/me')
  ]).then(function (results) {
    var buildingsData = results[0].data || [];
    var classroomsData = results[1].data || [];
    var schedulesData = results[2].data || [];
    var semesterData = results[3].data || null;
    var absencesData = results[4].data || [];
    var meData = results[5].data || null;

    var buildingMap = {};
    buildingsData.forEach(function (b) { buildingMap[b.id] = b.name; });
    var classroomMap = {};
    classroomsData.forEach(function (c) { classroomMap[c.id] = c.classroomName; });

    // Cargar Nombre de Admin en Header de Bienvenida
    if (meData) {
      $('welcomeAdminName').textContent = meData.fullName || meData.externalId || 'Administrador';
    }

    // Set KPIs
    var todaySchedules = schedulesData.filter(function (s) { return s.weekday === todayWeekday; });
    setKpi('kpiBuildings', buildingsData.length);
    setKpi('kpiClassrooms', classroomsData.length);
    setKpi('kpiSchedules', schedulesData.length);
    setKpi('kpiTodaySchedules', todaySchedules.length);
    setKpi('kpiSemester', semesterData ? semesterData.name : 'N/A');
    setKpi('kpiAbsences', absencesData.length);

    // Calcular y renderizar progreso de semestre
    if (semesterData && semesterData.startDate && semesterData.endDate) {
      var start = new Date(semesterData.startDate + 'T00:00:00');
      var end = new Date(semesterData.endDate + 'T00:00:00');
      var today = new Date();
      today.setHours(0,0,0,0);
      
      var totalDays = Math.round((end - start) / (1000 * 60 * 60 * 24)) + 1;
      var elapsedDays = Math.round((today - start) / (1000 * 60 * 60 * 24)) + 1;
      
      if (elapsedDays < 0) elapsedDays = 0;
      if (elapsedDays > totalDays) elapsedDays = totalDays;
      
      var percent = totalDays > 0 ? Math.round((elapsedDays / totalDays) * 100) : 0;
      
      $('semesterName').textContent = semesterData.name;
      $('semesterDateRange').textContent = semesterData.startDate + ' al ' + semesterData.endDate;
      $('semesterElapsed').textContent = elapsedDays + ' de ' + totalDays + ' días transcurridos';
      $('semesterPercentText').textContent = percent + '%';
      
      // Activar transiciones después del render
      setTimeout(function() {
        $('semesterProgressBar').style.width = percent + '%';
      }, 100);
      
      $('semesterProgressSection').style.display = 'block';
      $('semesterStatusBadge').style.display = 'inline-block';
      $('noSemesterAlert').style.display = 'none';
    } else {
      $('semesterProgressSection').style.display = 'none';
      $('semesterStatusBadge').style.display = 'none';
      $('noSemesterAlert').style.display = 'block';
    }

    // Renderizar Horarios de Hoy
    var tbody = $('scheduleBody');
    if (todaySchedules.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:26px;">No hay clases programadas para hoy.</td></tr>';
      return;
    }

    tbody.innerHTML = todaySchedules.map(function (s) {
      var hora = (s.startTime || '').substring(0, 5) + ' - ' + (s.endTime || '').substring(0, 5);
      var roomInfo = classroomsData.find(function (c) { return c.id === s.classroomId; });
      var roomName = roomInfo ? roomInfo.classroomName : 'Desconocido';
      var bldId = roomInfo ? roomInfo.buildingId : null;
      var bldName = getBuildingName(buildingMap, bldId);
      
      return '<tr class="schedule-row" style="cursor:pointer;" onclick="window.location.href=\'/aulas?aula=' + encodeURIComponent(roomName) + '\'">' +
        '<td>' + hora + '</td>' +
        '<td>' + roomName + '</td>' +
        '<td>' + (s.subjectName || '--') + '</td>' +
        '<td>' + (s.teacherExternalId || '--') + '</td>' +
        '<td>' + (s.groupName || '--') + '</td>' +
        '<td>' + bldName + '</td>' +
        '</tr>';
    }).join('');
  })['catch'](function (err) {
    console.error("Error al cargar los datos:", err);
    setKpi('kpiBuildings', 'Error');
    setKpi('kpiClassrooms', 'Error');
    setKpi('kpiSchedules', 'Error');
    setKpi('kpiTodaySchedules', 'Error');
    setKpi('kpiSemester', 'Error');
    setKpi('kpiAbsences', 'Error');
    $('scheduleBody').innerHTML = '<tr><td colspan="6" style="text-align:center;color:#ef4444;padding:26px;"><i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i> Error al cargar datos del servidor</td></tr>';
  });
});
</script>
@endsection
