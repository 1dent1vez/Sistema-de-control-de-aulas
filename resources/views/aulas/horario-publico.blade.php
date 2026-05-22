{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Horario Público QR - Vista conectada a API REST
 * @autor          Equipo GAMA
 * @autorizador    Rubén Alejandro Nolasco Ruiz
 * @prueba         Diego Miguel Hernandez Fabela
 * @mantenimiento  Ghael Garcia Manjarrez
 * @version        1.1.0
 * @creado         07/05/2026
 * @modificado     19/05/2026
 * @cambios        19/05/2026 - Conexión a API REST, eliminación de datos hardcodeados
 */
--}}

@extends('layouts.app')

@section('title', 'Horario Público QR - GAMA Solutions')

@section('content')
<style>
  .hp-main { margin-left: var(--sidebar-width, 240px); min-height: 100vh; background: var(--ice-blue); padding: 28px 32px; }
  .hp-head { display:flex; justify-content:space-between; gap:12px; align-items:flex-end; flex-wrap:wrap; margin-bottom:16px; }
  .hp-title { font-size:26px; font-weight:700; color:var(--midnight); margin-bottom:4px; }
  .hp-sub { color:var(--soft-steel); font-size:14px; }
  .hp-card { background:#fff; border:1px solid var(--mist-blue); border-radius: var(--radius-lg); overflow:hidden; }
  .hp-toolbar { padding:14px 16px; border-bottom:1px solid var(--mist-blue); display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
  .hp-toolbar select, .hp-toolbar input { border:1px solid var(--mist-blue); background:var(--ice-blue); border-radius:var(--radius-md); padding:9px 12px; font-size:13px; font-family:var(--font-main); }
  .hp-table-wrap { overflow-x:auto; }
  .hp-table { width:100%; border-collapse:collapse; font-size:13px; }
  .hp-table thead th { background:var(--deep-blue); color:#fff; text-align:left; padding:12px 14px; }
  .hp-table tbody tr:nth-child(odd){ background:var(--light-blue);} .hp-table tbody tr:nth-child(even){background:#fff;}
  .hp-table tbody tr:hover{ background:var(--light-orange);}
  .hp-table td{ padding:12px 14px; border-bottom:1px solid var(--mist-blue); }
  .spinner { display:inline-block; width:20px; height:20px; border:3px solid var(--mist-blue); border-top-color:var(--deep-blue); border-radius:50%; animation:spin 0.7s linear infinite; vertical-align:middle; margin-right:6px; }
  @keyframes spin { to { transform:rotate(360deg); } }
  .loading-overlay { display:flex; align-items:center; justify-content:center; gap:10px; padding:40px; color:var(--soft-steel); }
  @media (max-width:1024px){ .hp-main{ margin-left:0; } }
</style>

<div class="hp-main">
  <div class="hp-head">
    <div>
      <h1 class="hp-title">Horario Público QR</h1>
      <p class="hp-sub">Consulta rápida de clases del aula seleccionada por QR.</p>
    </div>
  </div>

  <section class="hp-card">
    <div class="hp-toolbar">
      <select id="fAula"><option value="">Cargando aulas...</option></select>
      <input id="fDia" placeholder="Filtrar por día (LUN, MAR...)">
      <span id="hpCount" style="margin-left:auto;font-size:12px;color:var(--soft-steel);"></span>
    </div>
    <div id="hpLoader" class="loading-overlay hidden">
      <div class="spinner"></div>
      <span>Cargando horario...</span>
    </div>
    <div class="hp-table-wrap">
      <table class="hp-table">
        <thead>
          <tr><th>Hora</th><th>Día</th><th>Materia</th><th>Docente</th><th>Grupo</th><th>Aula</th></tr>
        </thead>
        <tbody id="hpBody"></tbody>
      </table>
    </div>
  </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var API_BASE = '/api/v1';
  var $ = function (id) { return document.getElementById(id); };
  var fAula = $('fAula'), fDia = $('fDia'), body = $('hpBody'), cnt = $('hpCount'), loader = $('hpLoader');

  var state = { classroomId: '', schedules: [], classroomMap: {} };

  function loadClassrooms() {
    fetch(API_BASE + '/classrooms', { headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        var classrooms = (res && res.data && Array.isArray(res.data)) ? res.data : [];
        state.classroomMap = {};
        fAula.innerHTML = '<option value="">Selecciona un aula</option>';
        classrooms.forEach(function (c) {
          state.classroomMap[c.id] = c.classroomName;
          var opt = document.createElement('option');
          opt.value = c.id;
          opt.textContent = c.classroomName + (c.buildingName ? ' (' + c.buildingName + ')' : '');
          fAula.appendChild(opt);
        });
      })
      ['catch'](function () {
        fAula.innerHTML = '<option value="">Error al cargar aulas</option>';
      });
  }

  function loadSchedules(classroomId) {
    if (!classroomId) {
      state.schedules = [];
      render();
      return;
    }
    loader.classList.remove('hidden');
    state.classroomId = classroomId;
    fetch(API_BASE + '/class-schedules?classroom_id=' + classroomId, { headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        state.schedules = (res && res.data && Array.isArray(res.data)) ? res.data : [];
        loader.classList.add('hidden');
        render();
      })
      ['catch'](function () {
        state.schedules = [];
        loader.classList.add('hidden');
        cnt.textContent = 'Error al cargar';
        body.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#b00000;padding:26px;">Error al cargar horario</td></tr>';
      });
  }

  function render() {
    var qDia = fDia.value.trim().toUpperCase();
    var rows = state.schedules.filter(function (s) {
      return !qDia || (s.weekday && s.weekday.toUpperCase().includes(qDia));
    });
    cnt.textContent = rows.length + ' registro(s)';
    if (rows.length === 0) {
      body.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--soft-steel);padding:26px;">' +
        (state.schedules.length > 0 ? 'Sin resultados para el filtro aplicado' : (state.classroomId ? 'No hay horarios registrados para esta aula' : 'Selecciona un aula')) +
        '</td></tr>';
      return;
    }
    body.innerHTML = rows.map(function (s) {
      var hora = (s.startTime ? s.startTime.substring(0, 5) : '--') + '-' + (s.endTime ? s.endTime.substring(0, 5) : '--');
      var aula = state.classroomMap[s.classroomId] || 'Aula #' + s.classroomId;
      return '<tr><td>' + hora + '</td><td>' + (s.weekday || '--') + '</td><td>' + (s.subjectName || '--') + '</td><td>' + (s.teacherExternalId || '--') + '</td><td>' + (s.groupName || '--') + '</td><td>' + aula + '</td></tr>';
    }).join('');
  }

  fAula.addEventListener('change', function () { loadSchedules(fAula.value); });
  fDia.addEventListener('input', render);

  loadClassrooms();
  loadSchedules('');
});
</script>
@endsection
