{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Carga Masiva de Horarios - Proyecto B: Sistema de Control de Aulas
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

@section('title', 'Carga Masiva de Horarios - GAMA Solutions')

@section('content')
<style>
  .imp-main {
    min-height: 100vh;
    background: var(--ice-blue);
    padding: 24px 28px;
    margin-left: var(--sidebar-width, 240px);
    width: calc(100% - var(--sidebar-width, 240px));
    box-sizing: border-box;
  }
  .imp-title { font-size: 26px; font-weight: 700; color: var(--midnight); margin-bottom: 4px; }
  .imp-sub { color: var(--soft-steel); font-size: 14px; margin-bottom: 18px; }
  .imp-card {
    background:#fff;
    border:1px solid var(--mist-blue);
    border-radius: var(--radius-lg);
    overflow: hidden;
    margin: 0 auto 16px;
    width: 100%;
    max-width: 1180px;
    box-shadow: 0 2px 10px rgba(19, 68, 116, 0.06);
  }
  .imp-body { padding: 18px; }
  .imp-section-title {
    font-size: 17px;
    font-weight: 700;
    color: var(--midnight);
    margin-bottom: 12px;
  }
  .imp-help {
    background: var(--ice-blue);
    border: 1px solid var(--mist-blue);
    border-radius: var(--radius-md);
    padding: 12px 14px;
    margin-bottom: 14px;
  }
  .imp-help ol {
    margin: 0;
    padding-left: 18px;
    color: var(--soft-steel);
    line-height: 1.65;
    font-size: 13px;
  }
  .imp-help li + li { margin-top: 2px; }
  .imp-row {
    display: grid;
    grid-template-columns: auto minmax(220px, 1fr) auto;
    gap: 10px;
    align-items: center;
    margin-bottom: 12px;
  }
  .dropzone {
    border: 2px dashed #1E5A8A; border-radius: 12px; background: #fff; padding: 22px; text-align: center;
    color: var(--soft-steel); transition: all .2s ease; cursor: pointer;
  }
  .dropzone.drag-over { border-style: solid; border-color: var(--corp-orange); background: rgba(242,139,44,0.07); color: var(--deep-orange); }
  .imp-select, .imp-file { border:1px solid var(--mist-blue); background:var(--ice-blue); border-radius:var(--radius-md); padding:10px 12px; font-family:var(--font-main); font-size:14px; }
  .msg { padding: 10px 12px; border-radius: var(--radius-md); font-size: 13px; margin-top: 10px; }
  .msg.error { background: rgba(255,0,0,.08); border:1px solid rgba(255,0,0,.34); color:#B00000; }
  .msg.ok { background: rgba(90,154,90,.12); border:1px solid rgba(90,154,90,.34); color: var(--status-active); }
  .rpt-tabs { display:flex; gap:10px; padding: 12px 16px 0; }
  .rpt-chip { padding:6px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
  .rpt-chip.ok { background: rgba(90,154,90,.15); color: var(--status-active); }
  .rpt-chip.bad { background: rgba(255,0,0,.12); color: #B00000; }
  .imp-table-wrap { overflow-x:auto; padding: 12px 16px 16px; max-height: 58vh; }
  .imp-table { width:100%; border-collapse: collapse; font-size: 13px; }
  .imp-table th { background: var(--deep-blue); color:#fff; text-align:left; padding: 10px 12px; position: sticky; top: 0; z-index: 2; }
  .imp-table td { padding: 10px 12px; border-bottom:1px solid var(--mist-blue); }
  .imp-table tr:last-child td { border-bottom: none; }
  .row-ok { background: rgba(90,154,90,.08); }
  .row-bad { background: rgba(255,0,0,.08); }
  .imp-card-head {
    padding: 12px 16px;
    border-bottom: 1px solid var(--mist-blue);
    background: #fff;
    font-size: 14px;
    font-weight: 700;
    color: var(--deep-blue);
  }
  @media (max-width: 900px) {
    .imp-main {
      margin-left: 0;
      width: 100%;
      padding: 16px;
    }
    .imp-row { grid-template-columns: 1fr; }
    .imp-row .btn { width: 100%; justify-content: center; }
    .imp-select { width: 100%; }
  }
</style>

<div class="imp-main">
  <h1 class="imp-title">Carga Masiva de Horarios</h1>
  <p class="imp-sub">Drag & Drop CSV/XLSX + validación estructural y reporte de filas.</p>

  <section class="imp-card">
    <div class="imp-body">
      <h3 class="imp-section-title">Instrucciones de uso</h3>
      <div class="imp-help">
        <ol>
          <li>Descarga la plantilla oficial con el botón <strong>Descargar Plantilla</strong>.</li>
          <li>Llena columnas obligatorias: <strong>aula, docente, materia, grupo, dias, hora_inicio, hora_fin</strong>.</li>
          <li>Selecciona semestre destino y carga el archivo por Drag & Drop o selección manual.</li>
          <li>Presiona <strong>Procesar Archivo</strong> para ejecutar validación estructural y validación por fila.</li>
          <li>Revisa el reporte: filas en verde importadas y filas en rojo descartadas con motivo.</li>
        </ol>
      </div>
      <div class="imp-row">
        <button class="btn btn-outline btn-md" id="btnPlantilla"><i class="fas fa-download"></i><span>Descargar Plantilla</span></button>
        <select class="imp-select" id="semestreDestino">
          <option value="">Selecciona semestre destino...</option>
          <option value="2026-A">2026-A</option>
          <option value="2026-B">2026-B</option>
        </select>
        <button class="btn btn-primary btn-md" id="btnProcesar"><i class="fas fa-cogs"></i><span>Procesar Archivo</span></button>
      </div>

      <label class="dropzone" id="dropzone">
        <input class="imp-file" id="fileInput" type="file" accept=".csv,.xlsx" style="display:none;">
        <div><i class="fas fa-file-upload" style="font-size:28px;margin-bottom:8px;color:var(--deep-blue);"></i></div>
        <strong id="fileLabel">Arrastra el archivo CSV/XLSX aquí o haz clic para seleccionarlo</strong>
      </label>

      <div id="msgBox"></div>
    </div>
  </section>

  <section class="imp-card" id="reportCard" style="display:none;">
    <div class="imp-card-head">Reporte de validación e importación</div>
    <div class="rpt-tabs">
      <span class="rpt-chip ok" id="okCount">Importadas: 0</span>
      <span class="rpt-chip bad" id="badCount">Descartadas: 0</span>
    </div>
    <div class="imp-table-wrap">
      <table class="imp-table">
        <thead>
          <tr>
            <th>Fila</th><th>Estado</th><th>Aula</th><th>Docente</th><th>Materia</th><th>Día</th><th>Hora</th><th>Detalle</th>
          </tr>
        </thead>
        <tbody id="reportBody"></tbody>
      </table>
    </div>
  </section>

  <div class="toast-container" id="toastContainer"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const REQUIRED = ['aula','docente','materia','grupo','dias','hora_inicio','hora_fin'];
  const EXISTING_AULAS = ['Aula 101', 'Aula 201', 'Lab C-1'];
  const EXISTING_DOCENTES = ['Laura Mendez', 'Jose Rivera', 'Daniel Rojas'];
  const ACTIVE = [
    { aula:'Aula 101', dia:'LUN', inicio:'08:00', fin:'09:30' },
    { aula:'Aula 101', dia:'MIE', inicio:'10:00', fin:'11:30' }
  ];

  let selectedFile = null;
  const $ = (id) => document.getElementById(id);
  const dz = $('dropzone'), fi = $('fileInput'), fileLabel = $('fileLabel');

  dz.addEventListener('click', () => fi.click());
  fi.addEventListener('change', (e) => setFile(e.target.files[0] || null));
  dz.addEventListener('dragover', (e) => { e.preventDefault(); dz.classList.add('drag-over'); });
  dz.addEventListener('dragleave', () => dz.classList.remove('drag-over'));
  dz.addEventListener('drop', (e) => {
    e.preventDefault(); dz.classList.remove('drag-over');
    setFile(e.dataTransfer.files[0] || null);
  });

  function setFile(file) {
    selectedFile = file;
    fileLabel.textContent = file ? `Archivo seleccionado: ${file.name}` : 'Arrastra el archivo CSV/XLSX aquí o haz clic para seleccionarlo';
  }

  $('btnPlantilla').addEventListener('click', () => {
    const csv = 'aula,docente,materia,grupo,dias,hora_inicio,hora_fin\nAula 101,Laura Mendez,Matematicas I,1A,LUN|MIE,08:00,09:30\n';
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob); a.download = 'plantilla_horarios.csv'; a.click();
    URL.revokeObjectURL(a.href);
  });

  function toMin(h) { const [hh, mm] = h.split(':').map(Number); return hh * 60 + mm; }
  function overlap(aS, aE, bS, bE) { return toMin(aS) < toMin(bE) && toMin(aE) > toMin(bS); }
  function showMsg(type, text) { $('msgBox').innerHTML = `<div class="msg ${type}">${text}</div>`; }
  function toast(title, message) {
    const t = document.createElement('div');
    t.className = 'toast success';
    t.innerHTML = `<div class="toast-icon"><i class="fas fa-check"></i></div><div class="toast-content"><div class="toast-title">${title}</div><div class="toast-message">${message}</div></div><button class="toast-close"><i class="fas fa-times"></i></button>`;
    $('toastContainer').appendChild(t); setTimeout(() => t.classList.add('show'), 10);
    const rm = () => { t.classList.remove('show'); setTimeout(() => t.remove(), 260); };
    const timer = setTimeout(rm, 4500); t.querySelector('.toast-close').addEventListener('click', () => { clearTimeout(timer); rm(); });
  }

  function parseCsv(text) {
    const lines = text.split(/\r?\n/).filter(Boolean);
    if (!lines.length) return { headers: [], rows: [] };
    const headers = lines[0].split(',').map(x => x.trim().toLowerCase());
    const rows = lines.slice(1).map((line, idx) => {
      const values = line.split(',').map(v => v.trim());
      const row = { __line: idx + 2 };
      headers.forEach((h, i) => { row[h] = values[i] ?? ''; });
      return row;
    });
    return { headers, rows };
  }

  $('btnProcesar').addEventListener('click', async () => {
    $('reportCard').style.display = 'none';
    $('reportBody').innerHTML = '';
    $('msgBox').innerHTML = '';

    const sem = $('semestreDestino').value;
    if (!selectedFile) { showMsg('error', 'Selecciona un archivo CSV/XLSX antes de procesar.'); return; }
    if (!sem) { showMsg('error', 'Selecciona el semestre destino antes de procesar.'); return; }
    if (!selectedFile.name.toLowerCase().endsWith('.csv')) {
      showMsg('error', 'Para esta demo visual, el procesamiento activo es CSV. XLSX se valida solo como tipo permitido.');
      return;
    }

    const text = await selectedFile.text();
    const { headers, rows } = parseCsv(text);

    // Fase 1: estructura
    const missing = REQUIRED.filter(c => !headers.includes(c));
    if (missing.length > 0) {
      showMsg('error', `Estructura invalida. Faltan columnas obligatorias: ${missing.join(', ')}`);
      return;
    }

    // Fase 2: validacion por fila
    const okRows = [];
    const badRows = [];
    rows.forEach(r => {
      const dias = (r.dias || '').split('|').map(d => d.trim().toUpperCase()).filter(Boolean);
      let err = '';
      if (!EXISTING_AULAS.includes(r.aula)) err = 'El aula no existe en el sistema';
      else if (!EXISTING_DOCENTES.includes(r.docente)) err = 'El docente no existe en el sistema';
      else if (!r.hora_inicio || !r.hora_fin || toMin(r.hora_fin) <= toMin(r.hora_inicio)) err = 'Hora fin debe ser mayor a hora inicio';
      else {
        const choque = ACTIVE.find(a => a.aula === r.aula && dias.includes(a.dia) && overlap(r.hora_inicio, r.hora_fin, a.inicio, a.fin));
        if (choque) err = `Empalme con horario activo ${choque.dia} ${choque.inicio}-${choque.fin}`;
      }
      if (err) badRows.push({ ...r, detalle: err }); else okRows.push({ ...r, detalle: 'Importado correctamente' });
    });

    // Simula importacion parcial solo de validos
    okRows.forEach(r => {
      (r.dias || '').split('|').map(d => d.trim().toUpperCase()).forEach(dia => {
        ACTIVE.push({ aula: r.aula, dia, inicio: r.hora_inicio, fin: r.hora_fin });
      });
    });

    $('reportCard').style.display = 'block';
    $('okCount').textContent = `Importadas: ${okRows.length}`;
    $('badCount').textContent = `Descartadas: ${badRows.length}`;
    const reportRows = [
      ...okRows.map(r => ({...r, estado:'Importada', cls:'row-ok'})),
      ...badRows.map(r => ({...r, estado:'Descartada', cls:'row-bad'}))
    ];
    $('reportBody').innerHTML = reportRows.map(r => `
      <tr class="${r.cls}">
        <td>${r.__line}</td><td>${r.estado}</td><td>${r.aula || '-'}</td><td>${r.docente || '-'}</td><td>${r.materia || '-'}</td>
        <td>${r.dias || '-'}</td><td>${r.hora_inicio || '-'} - ${r.hora_fin || '-'}</td><td>${r.detalle}</td>
      </tr>`).join('');

    showMsg('ok', `Proceso completado. Estructura valida. ${okRows.length} fila(s) importada(s), ${badRows.length} descartada(s).`);
    toast('Importacion completada', `${okRows.length} registro(s) importado(s) exitosamente.`);
  });
});
</script>
@endsection
