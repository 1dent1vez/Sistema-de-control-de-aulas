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
        <span class="kpi-value">6</span>
        <span class="kpi-label">Total edificios</span>
      </div>
    </article>
    <article class="kpi-card admin-kpi-card">
      <div class="kpi-icon"><i class="fas fa-school"></i></div>
      <div class="kpi-content">
        <span class="kpi-value">84</span>
        <span class="kpi-label">Total aulas</span>
      </div>
    </article>
    <article class="kpi-card admin-kpi-card">
      <div class="kpi-icon"><i class="fa-solid fa-book-open-reader"></i></div>
      <div class="kpi-content">
        <span class="kpi-value">28</span>
        <span class="kpi-label">Clases activas</span>
      </div>
    </article>
    <article class="kpi-card admin-kpi-card">
      <div class="kpi-icon"><i class="fas fa-graduation-cap"></i></div>
      <div class="kpi-content">
        <span class="kpi-value">2026-A</span>
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
          <tr class="schedule-row" data-url="{{ route('aulas') }}?aula=A-101">
            <td>07:00 - 08:30</td>
            <td>A-101</td>
            <td>Matemáticas I</td>
            <td>Mtra. Laura Méndez</td>
            <td>1A</td>
            <td>Edificio A</td>
          </tr>
          <tr class="schedule-row" data-url="{{ route('aulas') }}?aula=B-204">
            <td>08:30 - 10:00</td>
            <td>B-204</td>
            <td>Programación Web</td>
            <td>Ing. José Rivera</td>
            <td>4B</td>
            <td>Edificio B</td>
          </tr>
          <tr class="schedule-row" data-url="{{ route('aulas') }}?aula=C-303">
            <td>10:00 - 11:30</td>
            <td>C-303</td>
            <td>Bases de Datos</td>
            <td>Mtro. Daniel Rojas</td>
            <td>5A</td>
            <td>Edificio C</td>
          </tr>
          <tr class="schedule-row" data-url="{{ route('aulas') }}?aula=A-102">
            <td>11:30 - 13:00</td>
            <td>A-102</td>
            <td>Física Aplicada</td>
            <td>Dra. Patricia Luna</td>
            <td>2C</td>
            <td>Edificio A</td>
          </tr>
          <tr class="schedule-row" data-url="{{ route('aulas') }}?aula=D-405">
            <td>13:00 - 14:30</td>
            <td>D-405</td>
            <td>Redes I</td>
            <td>Ing. Carlos Rangel</td>
            <td>6D</td>
            <td>Edificio D</td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</div>

<script>
  document.querySelectorAll(".schedule-row").forEach((row) => {
    row.addEventListener("click", () => {
      const targetUrl = row.dataset.url;
      if (targetUrl) {
        window.location.href = targetUrl;
      }
    });
  });
</script>
@endsection
