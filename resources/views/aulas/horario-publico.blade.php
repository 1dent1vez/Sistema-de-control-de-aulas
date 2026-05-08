{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Horario Público QR - Proyecto B: Sistema de Control de Aulas
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
      <select id="fAula"></select>
      <input id="fDia" placeholder="Filtrar por día (LUN, MAR...)">
      <span id="hpCount" style="margin-left:auto;font-size:12px;color:var(--soft-steel);"></span>
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
  const aulas = ['Aula 101', 'Aula 201', 'Lab C-1'];
  const horarios = [
    { hora: '08:00-09:30', dia: 'LUN', materia: 'Matematicas I', docente: 'Laura Mendez', grupo: '1A', aula: 'Aula 101' },
    { hora: '10:00-11:30', dia: 'MIE', materia: 'Programacion Web', docente: 'Jose Rivera', grupo: '3B', aula: 'Aula 101' },
    { hora: '09:00-10:30', dia: 'MAR', materia: 'Bases de Datos', docente: 'Daniel Rojas', grupo: '5A', aula: 'Aula 201' }
  ];
  const $ = (id) => document.getElementById(id);
  const fAula = $('fAula'), fDia = $('fDia'), body = $('hpBody'), cnt = $('hpCount');
  fAula.innerHTML = '<option value="">Todas las aulas</option>' + aulas.map(a => `<option value="${a}">${a}</option>`).join('');

  function render() {
    const qDia = fDia.value.trim().toUpperCase();
    const rows = horarios.filter(r => (!fAula.value || r.aula === fAula.value) && (!qDia || r.dia.includes(qDia)));
    cnt.textContent = `${rows.length} registro(s)`;
    body.innerHTML = rows.length ? rows.map(r => `<tr><td>${r.hora}</td><td>${r.dia}</td><td>${r.materia}</td><td>${r.docente}</td><td>${r.grupo}</td><td>${r.aula}</td></tr>`).join('')
      : '<tr><td colspan="6" style="text-align:center;color:var(--soft-steel);padding:26px;">Sin resultados</td></tr>';
  }
  fAula.addEventListener('change', render);
  fDia.addEventListener('input', render);
  render();
});
</script>
@endsection
