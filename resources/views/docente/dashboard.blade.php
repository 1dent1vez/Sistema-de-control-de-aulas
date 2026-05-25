{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Dashboard Docente con KPIs reales del API (clases, ausencias y horarios hoy)
 * @autor          Equipo GAMA
 * @autorizador    Rubén Alejandro Nolasco Ruiz
 * @prueba         Diego Miguel Hernandez Fabela
 * @mantenimiento  Antigravity
 * @version        1.0.0
 * @creado         24/05/2026
 * @modificado     25/05/2026
 */
 --}}

 @extends('layouts.app')

 @section('title', 'Dashboard Docente - GAMA Solutions')

 @section('content')
 <style>
   .dm-main { margin-left: var(--sidebar-width, 240px); min-height: 100vh; background: var(--ice-blue); padding: 28px 32px; }
   .dm-head { display:flex; justify-content:space-between; gap:12px; align-items:center; flex-wrap:wrap; margin-bottom:16px; }
   .dm-title { font-size:26px; font-weight:700; color:var(--midnight); margin-bottom:4px; }
   .dm-sub { color:var(--soft-steel); font-size:14px; }
   .dm-actions { display:flex; gap:8px; flex-wrap:wrap; }
   .dm-kpi-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px,1fr)); gap:14px; margin-bottom:20px; }
   .kpi-card { background:#fff; border:1px solid var(--mist-blue); border-radius:var(--radius-lg); padding:18px 20px; display:flex; gap:14px; align-items:center; }
   .kpi-icon { width:44px; height:44px; border-radius:var(--radius-md); background:rgba(242,139,44,.08); color:var(--corp-orange); display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
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
   
   .banner-welcome {
     background: linear-gradient(135deg, var(--deep-blue) 0%, #1a5a96 100%);
     color: #fff;
     border-radius: var(--radius-lg);
     padding: 24px;
     margin-bottom: 20px;
     box-shadow: 0 4px 12px rgba(19, 68, 116, 0.15);
   }
   .banner-welcome h2 { font-size: 22px; font-weight: 700; margin-bottom: 6px; }
   .banner-welcome p { font-size: 14px; opacity: 0.9; }

   @media (max-width:1024px){ .dm-main{ margin-left:0; } }
 </style>

 <div class="dm-main">
   <!-- Banner de bienvenida interactivo -->
   <div class="banner-welcome">
     <h2 id="welcomeUser">¡Hola, Docente!</h2>
     <p>Bienvenido al Sistema de Control de Aulas. Aquí puedes visualizar tus clases activas y registrar ausencias programadas.</p>
   </div>

   <div class="dm-head">
     <div>
       <h1 class="dm-title">Mi Dashboard</h1>
       <p class="dm-sub">Resumen de mis actividades y clases programadas.</p>
     </div>
     <div class="dm-actions">
       <a href="{{ route('docente.estatus') }}" class="btn btn-primary btn-sm"><i class="fas fa-calendar-alt"></i> Estatus y Ausencias</a>
     </div>
   </div>

   <!-- Alerta de permisos (mensaje flash) -->
   @if (session('error'))
     <div class="alert alert-danger" style="margin-bottom: 20px; border-radius: var(--radius-md); padding: 12px 16px; background: #fee2e2; border: 1px solid #fca5a5; color: #b91c1c; font-size: 14px; display: flex; align-items: center; gap: 8px;">
       <i class="fas fa-exclamation-circle"></i>
       <span>{{ session('error') }}</span>
     </div>
   @endif

   <div class="dm-kpi-grid" id="kpiGrid">
     <article class="kpi-card">
       <div class="kpi-icon" style="background: rgba(19,68,116,.08); color: var(--deep-blue);"><i class="fas fa-book"></i></div>
       <div><div class="kpi-val shimmer" id="kpiClasses"></div><div class="kpi-label">Mis materias</div></div>
     </article>
     <article class="kpi-card">
       <div class="kpi-icon"><i class="fas fa-user-clock"></i></div>
       <div><div class="kpi-val shimmer" id="kpiAbsences"></div><div class="kpi-label">Ausencias registradas</div></div>
     </article>
     <article class="kpi-card">
       <div class="kpi-icon" style="background: rgba(46,125,50,.08); color: #2e7d32;"><i class="fas fa-school"></i></div>
       <div><div class="kpi-val shimmer" id="kpiRooms"></div><div class="kpi-label">Aulas ocupadas hoy</div></div>
     </article>
   </div>

   <section class="dm-card">
     <div class="dm-card-h">
       <h2>Mis clases de hoy</h2>
       <small id="todayLabel"></small>
     </div>
     <div class="dm-table-wrap">
       <table class="dm-table">
         <thead><tr><th>Hora</th><th>Aula</th><th>Materia</th><th>Grupo</th><th>Edificio</th></tr></thead>
         <tbody id="scheduleBody"><tr><td colspan="5" style="text-align:center;color:var(--soft-steel);padding:26px;"><span class="shimmer shimmer-inline"></span> Cargando mis clases...</td></tr></tbody>
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
   $('todayLabel').textContent = now.toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

   var weekdayMap = { 0: 'DOM', 1: 'LUN', 2: 'MAR', 3: 'MIE', 4: 'JUE', 5: 'VIE', 6: 'SAB' };
   var todayWeekday = weekdayMap[now.getDay()];

   function setKpi(id, val) {
     var el = $(id);
     if (el) {
       el.textContent = val;
       el.classList.remove('shimmer');
     }
   }

   function handleUnauthorized(r) { if (r.status === 401) { localStorage.clear(); window.location.href = '/'; throw new Error('No autenticado'); } return r; }

   function fetchJson(url) {
     return fetch(url, { headers: HEADERS }).then(handleUnauthorized).then(function (r) { return r.ok ? r.json() : { data: [] }; });
   }

   // Cargar perfil en Banner
   fetchJson('/api/v1/auth/me').then(function (resp) {
     if (resp && resp.data) {
       var me = resp.data;
       $('welcomeUser').textContent = '¡Hola, ' + (me.fullName || 'Docente') + '!';
       
       // Buscar información con su teacher_external_id real
       var extId = me.externalId || '';
       loadTeacherData(extId);
     }
   })['catch'](function(err) {
     console.error("Error cargando perfil:", err);
   });

   function loadTeacherData(teacherExtId) {
     Promise.all([
       fetchJson('/api/v1/buildings'),
       fetchJson('/api/v1/classrooms'),
       fetchJson('/api/v1/class-schedules?teacher_external_id=' + encodeURIComponent(teacherExtId)),
       fetchJson('/api/v1/teacher-absences')
     ]).then(function (results) {
       var buildingsData = results[0].data || [];
       var classroomsData = results[1].data || [];
       var schedulesData = results[2].data || [];
       var absencesData = results[3].data || [];

       var buildingMap = {};
       buildingsData.forEach(function (b) { buildingMap[b.id] = b.name; });
       var classroomMap = {};
       classroomsData.forEach(function (c) { classroomMap[c.id] = c.classroomName; });

       // Filtrar materias únicas del docente
       var uniqueSubjects = {};
       schedulesData.forEach(function (s) {
         if (s.subjectName) uniqueSubjects[s.subjectName] = true;
       });

       // Filtrar clases de hoy
       var todaySchedules = schedulesData.filter(function (s) { return s.weekday === todayWeekday; });

       // Contar aulas ocupadas hoy
       var uniqueRooms = {};
       todaySchedules.forEach(function (s) {
         uniqueRooms[s.classroomId] = true;
       });

       setKpi('kpiClasses', Object.keys(uniqueSubjects).length);
       setKpi('kpiAbsences', absencesData.length);
       setKpi('kpiRooms', Object.keys(uniqueRooms).length);

       var tbody = $('scheduleBody');
       if (todaySchedules.length === 0) {
         tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--soft-steel);padding:26px;">No tienes clases programadas para hoy.</td></tr>';
         return;
       }

       tbody.innerHTML = todaySchedules.map(function (s) {
         var hora = (s.startTime || '').substring(0, 5) + ' - ' + (s.endTime || '').substring(0, 5);
         var room = classroomsData.find(function (c) { return c.id === s.classroomId; });
         var bldName = room ? getBuildingName(buildingMap, room.buildingId) : 'Desconocido';
         var roomName = room ? room.classroomName : 'Aula #' + s.classroomId;

         return '<tr>' +
           '<td>' + hora + '</td>' +
           '<td>' + roomName + '</td>' +
           '<td>' + (s.subjectName || '--') + '</td>' +
           '<td>' + (s.groupName || '--') + '</td>' +
           '<td>' + bldName + '</td>' +
           '</tr>';
       }).join('');
     })['catch'](function (err) {
       console.error("Error al cargar datos del docente:", err);
       setKpi('kpiClasses', 'Error');
       setKpi('kpiAbsences', 'Error');
       setKpi('kpiRooms', 'Error');
       $('scheduleBody').innerHTML = '<tr><td colspan="5" style="text-align:center;color:#b00000;padding:26px;">Error al cargar datos del servidor</td></tr>';
     });
   }

   function getBuildingName(map, id) {
     return map[id] || 'Desconocido';
   }
 });
 </script>
 @endsection
