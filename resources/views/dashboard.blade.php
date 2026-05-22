{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
  * "El factor de cambio en tu tecnología"
   *
    * @descripcion    Vista principal del Dashboard con KPIs, gráficas y filtrado dinámico.
     * @autor          Rubén Alejandro Nolasco Ruiz
      * @autorizador    Rubén Alejandro Nolasco Ruiz
       * @prueba         Diego Miguel Hernandez Fabela  
        * @mantenimiento  Ghael Garcia Manjarrez 
         * @version        1.0.0
          * @creado         11/04/2026
           * @modificado     11/04/2026
            *
             * @cambios
              * Fecha       | Autor             | Descripción
               * ------------|-------------------|------------------------------------------
                * 11/04/2026  | Rubén Alejandro   | Implementación inicial de Dashboard: KPIs, Gráficas y Filtros.
                 * 11/04/2026  | Rubén Alejandro   | Estandarización de prólogo según manual GAMA-MPL-03.
                  */
                  --}}
            
@extends('layouts.app')

@section('title', 'Dashboard Administrativo - GAMA Solutions')

@section('content')
<div class="main-content">
  <div class="page-header">
    <div class="header-text">
      <h1>Dashboard Administrativo</h1>
      <p>Punto central de operación del Sistema de Control de Aulas.</p>
    </div>

    <div class="quick-actions">
      <a href="{{ route('edificios') }}" class="btn btn-secondary btn-sm quick-action-btn"><i class="fas fa-building"></i><span>Edificios</span></a>
      <a href="{{ route('aulas') }}" class="btn btn-secondary btn-sm quick-action-btn"><i class="fas fa-school"></i><span>Aulas</span></a>
      <a href="{{ route('horarios.manual') }}" class="btn btn-secondary btn-sm quick-action-btn"><i class="fas fa-calendar-alt"></i><span>Horarios</span></a>
      <a href="{{ route('codigosqr') }}" class="btn btn-secondary btn-sm quick-action-btn"><i class="fas fa-qrcode"></i><span>QR</span></a>
    </div>
  </div>

  <div class="kpi-grid">
    <article class="kpi-card admin-kpi-card">
      <div class="kpi-icon"><i class="fas fa-building"></i></div>
      <div class="kpi-content">
        <span class="kpi-value buildings">...</span>
        <span class="kpi-label">Total edificios</span>
      </div>
    </article>
    <article class="kpi-card admin-kpi-card">
      <div class="kpi-icon"><i class="fas fa-school"></i></div>
      <div class="kpi-content">
        <span class="kpi-value classrooms">...</span>
        <span class="kpi-label">Total aulas</span>
      </div>
    </article>
    <article class="kpi-card admin-kpi-card">
      <div class="kpi-icon"><i class="fa-solid fa-book-open-reader"></i></div>
      <div class="kpi-content">
        <span class="kpi-value schedules">...</span>
        <span class="kpi-label">Clases activas</span>
      </div>
    </article>
    <article class="kpi-card admin-kpi-card">
      <div class="kpi-icon"><i class="fas fa-graduation-cap"></i></div>
      <div class="kpi-content">
        <span class="kpi-value semester">...</span>
        <span class="kpi-label">Semestre activo</span>
      </div>
    </article>
  </div>

  <section class="card table-card">
    <header class="card-header">
      <h2 class="card-title">Horarios de hoy</h2>
      <small>{{ now()->translatedFormat('l, d \\d\\e F \\d\\e Y') }}</small>
    </header>

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
        <tbody>
          <tr><td colspan="6" style="text-align: center;">Cargando horarios...</td></tr>
        </tbody>
      </table>
    </div>
  </section>
</div>

<script>
  document.addEventListener("DOMContentLoaded", async () => {
    const token = localStorage.getItem('auth_token');
    if (!token) {
      window.location.href = '/';
      return;
    }
    const headers = { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` };

    try {
      const [buildingsRes, classroomsRes, schedulesRes, semesterRes] = await Promise.all([
        fetch('/api/v1/buildings', { headers }),
        fetch('/api/v1/classrooms', { headers }),
        fetch('/api/v1/class-schedules', { headers }),
        fetch('/api/v1/semesters/current', { headers })
      ]);

      const buildings = buildingsRes.ok ? await buildingsRes.json() : { data: [] };
      const classrooms = classroomsRes.ok ? await classroomsRes.json() : { data: [] };
      const schedules = schedulesRes.ok ? await schedulesRes.json() : { data: [] };
      const semester = semesterRes.ok ? await semesterRes.json() : null;

      if (buildingsRes.status === 401 || classroomsRes.status === 401 ||
          schedulesRes.status === 401 || semesterRes.status === 401) {
        localStorage.clear();
        window.location.href = '/';
        return;
      }

      document.querySelector('.kpi-value.buildings').textContent = buildings.data ? buildings.data.length : 0;
      document.querySelector('.kpi-value.classrooms').textContent = classrooms.data ? classrooms.data.length : 0;
      document.querySelector('.kpi-value.schedules').textContent = schedules.data ? schedules.data.length : 0;
      document.querySelector('.kpi-value.semester').textContent = (semester && semester.data) ? semester.data.name : 'N/A';

      // Actualizar Tabla de Horarios
      const tbody = document.querySelector('.admin-schedule-table tbody');
      tbody.innerHTML = '';

      if (!schedules.data || schedules.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No hay horarios registrados.</td></tr>';
      } else {
        const getClassroomInfo = (classroomId) => {
          const room = classrooms.data.find(c => c.id === classroomId);
          if (!room) return { roomName: 'Desconocido', bldName: 'Desconocido' };
          const bld = buildings.data.find(b => b.id === room.buildingId);
          return { roomName: room.name, bldName: bld ? bld.name : 'Desconocido' };
        };

        // Mostrar primeros 10 horarios como muestra en el dashboard
        schedules.data.slice(0, 10).forEach(sch => {
          const info = getClassroomInfo(sch.classroomId);
          const tr = document.createElement('tr');
          tr.className = 'schedule-row';
          tr.style.cursor = 'pointer';
          tr.dataset.url = `/aulas?aula=${info.roomName}`;
          tr.innerHTML = `
            <td>${sch.startTime.substring(0,5)} - ${sch.endTime.substring(0,5)}</td>
            <td>${info.roomName}</td>
            <td>${sch.subjectName}</td>
            <td>${sch.teacherExternalId}</td>
            <td>${sch.groupName}</td>
            <td>${info.bldName}</td>
          `;
          tr.addEventListener("click", () => {
            window.location.href = tr.dataset.url;
          });
          tbody.appendChild(tr);
        });
      }
    } catch (error) {
      console.error("Error conectando con la API:", error);
      document.querySelector('.admin-schedule-table tbody').innerHTML = '<tr><td colspan="6" style="text-align: center; color: red;">Error al cargar datos del servidor</td></tr>';
    }
  });
</script>
@endsection
