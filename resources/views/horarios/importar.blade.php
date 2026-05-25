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
          <option value="">Cargando semestresâ€¦</option>
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
  const $ = (id) => document.getElementById(id);

  /* Auth guard */
  const authToken = localStorage.getItem('auth_token');
  if (!authToken) {
    window.location.href = '/';
    return;
  }

  /* CSRF */
  function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  }

  /* API helper */
  async function apiFetch(url, opts = {}) {
    const res = await fetch(url, {
      headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${authToken}`, 'X-CSRF-TOKEN': getCsrf(), ...(opts.headers ?? {}) },
      ...opts,
    });
    if (res.status === 401) {
      localStorage.clear();
      window.location.href = '/';
      return;
    }
    const json = await res.json();
    if (!res.ok) throw { status: res.status, json };
    return json;
  }



  /* -- Toast -- */
  function toast(title, message, type = 'success') {
    const icon = { success: 'check', error: 'times', warning: 'exclamation' }[type] ?? 'check';
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<div class="toast-icon"><i class="fas fa-${icon}"></i></div>` +
      `<div class="toast-content"><div class="toast-title">${title}</div><div class="toast-message">${message}</div></div>` +
      `<button class="toast-close"><i class="fas fa-times"></i></button>`;
    $('toastContainer').appendChild(t);
    setTimeout(() => t.classList.add('show'), 10);
    const rm = () => { t.classList.remove('show'); setTimeout(() => t.remove(), 260); };
    const timer = setTimeout(rm, 4500);
    t.querySelector('.toast-close').addEventListener('click', () => { clearTimeout(timer); rm(); });
  }

  function showMsg(type, text) {
    $('msgBox').innerHTML = `<div class="msg ${type}">${text}</div>`;
  }

  /* -- Cargar semestres desde la API -- */
  async function loadSemesters() {
    const sel = $('semestreDestino');
    try {
      // Primero obtener el semestre activo/vigente
      let currentSemester = null;
      try {
        const curRes = await apiFetch('/api/v1/semesters/current');
        currentSemester = curRes.data ?? null;
      } catch (e) {
        // Si no hay semestre activo o da 404, se queda null
      }

      const res = await apiFetch('/api/v1/semesters');
      const semesters = res.data ?? [];
      
      if (!semesters.length) {
        sel.innerHTML = '<option value="">Sin semestres registrados</option>';
        showMsg('error', 'No hay semestres registrados en el sistema. Cree un semestre primero.');
        $('btnProcesar').disabled = true;
        $('dropzone').style.pointerEvents = 'none';
        $('dropzone').style.opacity = '0.5';
        return;
      }

      sel.innerHTML = '<option value="">Selecciona semestre destino...</option>' +
        semesters.map(s => {
          const isCurrent = currentSemester && s.id === currentSemester.id;
          return `<option value="${s.id}" ${isCurrent ? 'selected' : ''}>${s.name}${isCurrent ? ' (Vigente)' : ''}</option>`;
        }).join('');

      if (!currentSemester) {
        showMsg('error', 'Atención: No existe ningún semestre vigente en el sistema. Debe crear o activar un semestre antes de poder registrar o importar horarios.');
        $('btnProcesar').disabled = true;
        $('dropzone').style.pointerEvents = 'none';
        $('dropzone').style.opacity = '0.5';
      } else {
        sel.value = currentSemester.id;
      }
    } catch (e) {
      sel.innerHTML = '<option value="">Error al cargar semestres</option>';
      showMsg('error', 'No se pudieron cargar los semestres desde la API.');
    }
  }

  /* -- Drag and Drop / seleccion de archivo -- */
  let selectedFile = null;
  const dz = $('dropzone');
  const fi = $('fileInput');
  const fileLabel = $('fileLabel');

  dz.addEventListener('click', () => fi.click());
  fi.addEventListener('change', (e) => setFile(e.target.files[0] || null));
  dz.addEventListener('dragover', (e) => { e.preventDefault(); dz.classList.add('drag-over'); });
  dz.addEventListener('dragleave', () => dz.classList.remove('drag-over'));
  dz.addEventListener('drop', (e) => {
    e.preventDefault();
    dz.classList.remove('drag-over');
    setFile(e.dataTransfer.files[0] || null);
  });

  function setFile(file) {
    selectedFile = file;
    fileLabel.textContent = file
      ? `Archivo seleccionado: ${file.name}`
      : 'Arrastra el archivo CSV/XLSX aqui o haz clic para seleccionarlo';
  }

  /* -- Plantilla descargable (columnas que espera la API) -- */
  $('btnPlantilla').addEventListener('click', () => {
    const csv =
      'classroom_id,teacher_external_id,subject_name,group_name,weekday,start_time,end_time\n' +
      '1,SAM-00123,Matematicas I,1A,monday,08:00,09:30\n';
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'plantilla_horarios.csv';
    a.click();
    URL.revokeObjectURL(a.href);
  });

  /* -- Procesar: envio a la API real -- */
  $('btnProcesar').addEventListener('click', async () => {
    $('reportCard').style.display = 'none';
    $('reportBody').innerHTML = '';
    $('msgBox').innerHTML = '';

    const semId = $('semestreDestino').value;
    if (!selectedFile) { showMsg('error', 'Selecciona un archivo CSV/XLSX antes de procesar.'); return; }
    if (!semId)        { showMsg('error', 'Selecciona el semestre destino antes de procesar.'); return; }

    const procBtn = $('btnProcesar');
    procBtn.disabled = true;
    procBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Procesando...</span>';

    try {
      const formData = new FormData();
      formData.append('file', selectedFile);
      formData.append('semester_id', semId);

      const res = await fetch('/api/v1/class-schedules/import', {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${authToken}`, 'X-CSRF-TOKEN': getCsrf() },
        body: formData,
      });
      const json = await res.json();
      if (!res.ok) throw { status: res.status, json };

      const batchId = json.data?.batchId ?? json.data?.batch_id ?? null;

      if (batchId) {
        let attempts = 0;
        const maxAttempts = 30; // 30 segundos máximo

        const pollReport = async () => {
          try {
            const rptRes = await apiFetch(`/api/v1/class-schedules/import/${batchId}/report`);
            if (rptRes.statusCode === 202) {
              if (attempts < maxAttempts) {
                attempts++;
                setTimeout(pollReport, 1000);
              } else {
                showMsg('error', 'La importación está tardando demasiado. Por favor, consulte los logs más tarde.');
                procBtn.disabled = false;
                procBtn.innerHTML = '<i class="fas fa-cogs"></i><span>Procesar Archivo</span>';
              }
            } else {
              const reportRows = rptRes.data ?? [];
              renderReport(reportRows, json.message ?? 'Importación completada.');
              toast('Importación completada', json.message ?? 'Archivo procesado exitosamente.', 'success');
              procBtn.disabled = false;
              procBtn.innerHTML = '<i class="fas fa-cogs"></i><span>Procesar Archivo</span>';
            }
          } catch (e) {
            const msg = e.json?.message ?? 'Error al recuperar el reporte de importación.';
            showMsg('error', msg);
            toast('Error de importación', msg, 'error');
            procBtn.disabled = false;
            procBtn.innerHTML = '<i class="fas fa-cogs"></i><span>Procesar Archivo</span>';
          }
        };

        setTimeout(pollReport, 1000);
      } else {
        const reportRows = json.data?.rows ?? (Array.isArray(json.data) ? json.data : []);
        renderReport(reportRows, json.message ?? 'Importación completada.');
        toast('Importación completada', json.message ?? 'Archivo procesado exitosamente.', 'success');
        procBtn.disabled = false;
        procBtn.innerHTML = '<i class="fas fa-cogs"></i><span>Procesar Archivo</span>';
      }

    } catch (err) {
      const msg = err.json?.message ?? 'Error al procesar el archivo en el servidor.';
      showMsg('error', msg);
      toast('Error de importación', msg, 'error');
      procBtn.disabled = false;
      procBtn.innerHTML = '<i class="fas fa-cogs"></i><span>Procesar Archivo</span>';
    }
  });

  /* -- Render del reporte -- */
  function renderReport(rows, summaryMsg) {
    const okRows  = rows.filter(r => r.status === 'imported'  || r.ok === true  || r.estado === 'Importada');
    const badRows = rows.filter(r => r.status === 'discarded' || r.ok === false || r.estado === 'Descartada');

    $('reportCard').style.display = 'block';
    $('okCount').textContent  = `Importadas: ${okRows.length}`;
    $('badCount').textContent = `Descartadas: ${badRows.length}`;

    if (!rows.length) {
      $('reportBody').innerHTML =
        '<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--soft-steel);">Sin filas en el reporte.</td></tr>';
      showMsg('ok', summaryMsg);
      return;
    }

    $('reportBody').innerHTML = rows.map((r, idx) => {
      const isOk   = r.status === 'imported' || r.ok === true || r.estado === 'Importada';
      const cls    = isOk ? 'row-ok' : 'row-bad';
      const estado = isOk ? 'Importada' : 'Descartada';
      const line   = r.row ?? r.__line ?? (idx + 2);
      const aula   = r.classroomName     ?? r.classroom_name     ?? r.aula    ?? '-';
      const doc    = r.teacherExternalId ?? r.teacher_external_id ?? r.docente ?? '-';
      const mat    = r.subjectName       ?? r.subject_name       ?? r.materia ?? '-';
      const dia    = r.weekday ?? r.weekdays ?? r.dias ?? '-';
      const hora   = r.startTime && r.endTime
                      ? `${r.startTime} - ${r.endTime}`
                      : (r.hora_inicio ? `${r.hora_inicio} - ${r.hora_fin}` : '-');
      const det    = r.error ?? r.detail ?? r.detalle ?? (isOk ? 'Importado correctamente' : '-');
      return `<tr class="${cls}">
        <td>${line}</td><td>${estado}</td><td>${aula}</td><td>${doc}</td>
        <td>${mat}</td><td>${dia}</td><td>${hora}</td><td>${det}</td>
      </tr>`;
    }).join('');

    showMsg('ok', summaryMsg);
  }

  /* -- Arranque -- */
  loadSemesters();
});
</script>
@endsection
