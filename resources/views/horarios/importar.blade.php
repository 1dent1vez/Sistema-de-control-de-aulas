{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Carga Masiva de Horarios - Diseño Minimalista con Previsualización y Reporte
 * @autor          Rubén Alejandro Nolasco Ruiz
 * @autorizador    Rubén Alejandro Nolasco Ruiz
 * @prueba         Diego Miguel Hernandez Fabela
 * @mantenimiento  Ghael Garcia Manjarrez
 * @version        2.0.0
 * @creado         07/05/2026
 * @modificado     2026-05-25
 * @cambios        2026-05-25 - Rediseño completo: eliminación de instrucciones largas, drag & drop de alta fidelidad, previsualización client-side CSV y card de resultados de importación.
 */
--}}

@extends('layouts.app')

@section('title', 'Carga Masiva de Horarios - GAMA')

@section('content')
<style>
  .imp-main {
    min-height: calc(100vh - 120px);
    padding: 30px var(--spacing-xl);
    margin-left: var(--sidebar-width, 260px);
    transition: margin-left var(--transition-normal);
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xl);
  }
  
  .sidebar.collapsed ~ .dashboard .imp-main {
    margin-left: var(--sidebar-collapsed-width, 70px);
  }

  .imp-header-block {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: var(--spacing-md);
  }
  
  .imp-title-group h1 {
    font-size: 1.85rem;
    font-weight: 700;
    color: var(--gama-azul-profundo);
    margin-bottom: 6px;
  }
  
  .imp-title-group p {
    color: var(--gama-gris-500);
    font-size: 0.95rem;
  }

  /* Card Principal */
  .imp-glass-card {
    background: var(--gama-blanco);
    border: 1px solid var(--gama-gris-200);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-md);
    padding: 28px;
    display: flex;
    flex-direction: column;
    gap: 24px;
    position: relative;
    overflow: hidden;
  }

  /* Configuración Inicial */
  .imp-config-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    align-items: center;
  }

  .imp-field-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .imp-field-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--gama-gris-700);
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .imp-select-field {
    width: 100%;
    padding: 12px 16px;
    font-size: 0.95rem;
    font-family: var(--font-family);
    border: 1px solid var(--gama-gris-300);
    border-radius: var(--border-radius-md);
    background-color: var(--gama-gris-100);
    color: var(--gama-negro);
    cursor: pointer;
    transition: all var(--transition-fast);
  }

  .imp-select-field:hover {
    border-color: var(--gama-azul-intermedio);
  }

  .imp-select-field:focus {
    outline: none;
    border-color: var(--gama-azul-profundo);
    box-shadow: 0 0 0 3px rgba(19, 68, 116, 0.12);
  }

  /* Área Drag & Drop */
  .imp-drag-zone {
    border: 2px dashed var(--gama-azul-intermedio);
    border-radius: var(--border-radius-lg);
    background: rgba(30, 90, 138, 0.02);
    padding: 45px 30px;
    text-align: center;
    cursor: pointer;
    transition: all var(--transition-normal);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--spacing-md);
  }

  .imp-drag-zone:hover {
    background: rgba(30, 90, 138, 0.05);
    border-color: var(--gama-azul-profundo);
  }

  .imp-drag-zone.drag-active {
    border-color: var(--gama-naranja);
    background: rgba(242, 139, 44, 0.05);
    transform: scale(0.995);
  }

  .imp-drag-icon {
    font-size: 2.75rem;
    color: var(--gama-azul-intermedio);
    transition: transform var(--transition-normal);
  }

  .imp-drag-zone:hover .imp-drag-icon {
    transform: translateY(-4px);
  }

  .imp-drag-zone.drag-active .imp-drag-icon {
    color: var(--gama-naranja);
  }

  .imp-drag-text h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--gama-azul-profundo);
    margin-bottom: 4px;
  }

  .imp-drag-text p {
    font-size: 0.875rem;
    color: var(--gama-gris-500);
  }

  /* Sleek File Card */
  .imp-file-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: var(--gama-azul-claro);
    border: 1px solid rgba(30, 90, 138, 0.2);
    border-radius: var(--border-radius-md);
    animation: fadeIn var(--transition-normal);
  }

  .imp-file-info {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .imp-file-icon {
    font-size: 1.85rem;
    color: var(--gama-azul-profundo);
  }

  .imp-file-details h4 {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--gama-azul-profundo);
  }

  .imp-file-details span {
    font-size: 0.8rem;
    color: var(--gama-gris-500);
  }

  .imp-btn-remove {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: var(--gama-gris-500);
    cursor: pointer;
    padding: var(--spacing-xs);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-fast);
  }

  .imp-btn-remove:hover {
    background: rgba(220, 53, 69, 0.1);
    color: var(--gama-error);
  }

  /* Previsualización Preview */
  .imp-preview-wrapper {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
    animation: fadeIn var(--transition-normal);
  }

  .imp-preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .imp-badge-count {
    background: var(--gama-azul-claro);
    color: var(--gama-azul-profundo);
    font-size: 0.8rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
    border: 1px solid rgba(19, 68, 116, 0.15);
  }

  .imp-preview-scroll {
    overflow-x: auto;
    border: 1px solid var(--gama-gris-300);
    border-radius: var(--border-radius-md);
  }

  .imp-preview-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
    background: var(--gama-blanco);
  }

  .imp-preview-table th {
    background: var(--gama-gris-100);
    color: var(--gama-gris-700);
    font-weight: 600;
    text-align: left;
    padding: 10px 14px;
    border-bottom: 1px solid var(--gama-gris-300);
  }

  .imp-preview-table td {
    padding: 10px 14px;
    border-bottom: 1px solid var(--gama-gris-200);
    color: var(--gama-negro);
  }

  .imp-preview-table tr:last-child td {
    border-bottom: none;
  }

  /* Contenedores de Estado Post-Carga */
  .imp-progress-overlay {
    padding: 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;
    animation: fadeIn var(--transition-normal);
  }

  .imp-progress-spinner {
    font-size: 3rem;
    color: var(--gama-azul-profundo);
    animation: spin 1s infinite linear;
  }

  /* Card de Resultados */
  .imp-result-card {
    display: flex;
    flex-direction: column;
    gap: 20px;
    animation: fadeIn var(--transition-normal);
  }

  .imp-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
  }

  .imp-stat-item {
    padding: 18px;
    border-radius: var(--border-radius-md);
    border: 1px solid var(--gama-gris-300);
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .imp-stat-item.ok {
    background: rgba(40, 167, 69, 0.05);
    border-color: rgba(40, 167, 69, 0.2);
  }

  .imp-stat-item.bad {
    background: rgba(220, 53, 69, 0.05);
    border-color: rgba(220, 53, 69, 0.2);
  }

  .imp-stat-icon {
    font-size: 2.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .imp-stat-item.ok .imp-stat-icon { color: var(--gama-exito); }
  .imp-stat-item.bad .imp-stat-icon { color: var(--gama-error); }

  .imp-stat-value {
    font-size: 1.65rem;
    font-weight: 700;
    line-height: 1.2;
  }

  .imp-stat-item.ok .imp-stat-value { color: var(--gama-exito); }
  .imp-stat-item.bad .imp-stat-value { color: var(--gama-error); }

  .imp-stat-label {
    font-size: 0.85rem;
    color: var(--gama-gris-600);
    font-weight: 500;
  }

  /* Acciones de la Tarjeta */
  .imp-action-buttons {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    flex-wrap: wrap;
  }

  .msg-container {
    margin-top: 10px;
  }

  .alert {
    padding: 14px 18px;
    border-radius: var(--border-radius-md);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 12px;
    border: 1px solid transparent;
  }

  .alert-danger {
    background: rgba(220, 53, 69, 0.06);
    border-color: rgba(220, 53, 69, 0.3);
    color: #a81c2b;
  }

  .alert-danger i {
    font-size: 1.2rem;
    color: var(--gama-error);
  }

  /* Animaciones */
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
  }

  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }

  /* Tabla de Reporte Final */
  .imp-report-wrap {
    border: 1px solid var(--gama-gris-300);
    border-radius: var(--border-radius-md);
    max-height: 400px;
    overflow-y: auto;
  }

  .imp-report-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
  }

  .imp-report-table th {
    background: var(--gama-gris-100);
    color: var(--gama-gris-700);
    font-weight: 600;
    padding: 12px 14px;
    text-align: left;
    border-bottom: 1px solid var(--gama-gris-300);
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .imp-report-table td {
    padding: 12px 14px;
    border-bottom: 1px solid var(--gama-gris-200);
  }

  .imp-report-table tr:last-child td {
    border-bottom: none;
  }

  .tr-ok {
    background: rgba(40, 167, 69, 0.03);
  }

  .tr-bad {
    background: rgba(220, 53, 69, 0.03);
  }

  .badge-status {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
  }

  .badge-status.ok {
    background: rgba(40, 167, 69, 0.12);
    color: var(--gama-exito);
  }

  .badge-status.bad {
    background: rgba(220, 53, 69, 0.12);
    color: var(--gama-error);
  }

  @media (max-width: 1024px) {
    .imp-main {
      margin-left: 0;
      padding: 20px 16px;
    }
  }
</style>

<div class="imp-main">
  <!-- Cabecera -->
  <div class="imp-header-block">
    <div class="imp-title-group">
      <h1>Carga Masiva de Horarios</h1>
      <p>Importa horarios institucionales de manera masiva utilizando archivos estructurados.</p>
    </div>
    <button class="btn btn-outline btn-md" id="btnPlantilla">
      <i class="fas fa-download"></i><span>Descargar Plantilla</span>
    </button>
  </div>

  <!-- Contenedor Principal -->
  <div class="imp-glass-card">
    
    <!-- Configuración Semestre -->
    <div class="imp-config-grid" id="configSection">
      <div class="imp-field-group">
        <label class="imp-field-label" for="semestreDestino">Semestre Académico Destino</label>
        <select class="imp-select-field" id="semestreDestino">
          <option value="">Cargando semestres...</option>
        </select>
      </div>
      <div id="semestreStatusBlock"></div>
    </div>

    <!-- 1. Pantalla de Selección de Archivo -->
    <div id="uploadContainer">
      <label class="imp-drag-zone" id="dropzone">
        <input type="file" id="fileInput" accept=".csv,.xlsx" style="display:none;">
        <i class="fas fa-cloud-upload-alt imp-drag-icon"></i>
        <div class="imp-drag-text">
          <h3>Arrastra tu archivo aquí</h3>
          <p>Soporta formatos .CSV y .XLSX (máximo 5MB)</p>
        </div>
      </label>
    </div>

    <!-- 2. Archivo Seleccionado y Preview -->
    <div id="selectedContainer" style="display:none; flex-direction:column; gap:24px;">
      <div class="imp-file-card">
        <div class="imp-file-info">
          <i class="fas fa-file-csv imp-file-icon" id="fileIcon"></i>
          <div class="imp-file-details">
            <h4 id="fileName">horario_2026.csv</h4>
            <span id="fileSize">142 KB</span>
          </div>
        </div>
        <button class="imp-btn-remove" id="btnRemoveFile" title="Quitar archivo">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Alertas Estructurales / Validación del Servidor -->
      <div id="msgBox" class="msg-container"></div>

      <!-- Preview de Filas CSV -->
      <div class="imp-preview-wrapper" id="previewWrapper" style="display:none;">
        <div class="imp-preview-header">
          <span class="imp-field-label">Previsualización del Archivo (Primeras 5 filas)</span>
          <span class="imp-badge-count" id="previewCount">5 filas detectadas</span>
        </div>
        <div class="imp-preview-scroll">
          <table class="imp-preview-table">
            <thead>
              <tr>
                <th>Aula</th>
                <th>Docente</th>
                <th>Materia</th>
                <th>Grupo</th>
                <th>Días</th>
                <th>Hora Inicio</th>
                <th>Hora Fin</th>
              </tr>
            </thead>
            <tbody id="previewBody"></tbody>
          </table>
        </div>
      </div>

      <!-- Botones de Acción de Carga -->
      <div class="imp-action-buttons" id="actionSection">
        <button class="btn btn-outline btn-md" id="btnCancel">Cancelar</button>
        <button class="btn btn-primary btn-md" id="btnProcesar">
          <i class="fas fa-cogs"></i><span>Importar Horarios</span>
        </button>
      </div>
    </div>

    <!-- 3. Pantalla de Carga/Spinner de Progreso -->
    <div id="progressContainer" style="display:none;">
      <div class="imp-progress-overlay">
        <i class="fas fa-spinner imp-progress-spinner"></i>
        <div class="imp-title-group" style="text-align:center;">
          <h3 id="progressTitle" style="font-weight:600; color:var(--gama-azul-profundo);">Procesando Validación</h3>
          <p id="progressSubtitle">Validando solapamientos, semestres y existencias. Por favor, espere...</p>
        </div>
      </div>
    </div>

    <!-- 4. Pantalla de Resultados Finales -->
    <div id="resultContainer" style="display:none;" class="imp-result-card">
      <div class="imp-title-group">
        <h3 id="resultTitle" style="font-weight:700; color:var(--gama-azul-profundo);">Previsualización y Validación de Horarios</h3>
        <p id="resultSubtitle">A continuación se detalla el desglose del archivo validado.</p>
      </div>

      <div class="imp-stats-grid">
        <div class="imp-stat-item ok">
          <div class="imp-stat-icon"><i class="fas fa-check-circle"></i></div>
          <div>
            <div class="imp-stat-value" id="statsOk">0</div>
            <div class="imp-stat-label" id="statsOkLabel">Horarios Válidos</div>
          </div>
        </div>
        <div class="imp-stat-item bad">
          <div class="imp-stat-icon"><i class="fas fa-exclamation-circle"></i></div>
          <div>
            <div class="imp-stat-value" id="statsBad">0</div>
            <div class="imp-stat-label" id="statsBadLabel">Horarios Omitidos</div>
          </div>
        </div>
      </div>

      <!-- Alerta final -->
      <div id="finalAlertBox" class="msg-container"></div>

      <!-- Tabla de Reporte Detallado -->
      <div class="imp-preview-wrapper">
        <span class="imp-field-label">Reporte por Fila</span>
        <div class="imp-report-wrap">
          <table class="imp-report-table">
            <thead>
              <tr>
                <th>Fila</th>
                <th>Estado</th>
                <th>Aula</th>
                <th>Docente</th>
                <th>Materia</th>
                <th>Día(s)</th>
                <th>Hora</th>
                <th>Detalle / Error</th>
              </tr>
            </thead>
            <tbody id="resultBody"></tbody>
          </table>
        </div>
      </div>

      <div class="imp-action-buttons">
        <button class="btn btn-outline btn-md" id="btnDownloadReport" style="display:none;">
          <i class="fas fa-file-download"></i><span>Descargar Reporte de Errores</span>
        </button>
        <button class="btn btn-outline btn-md" id="btnCancelConfirm" style="display:none;">
          Cancelar
        </button>
        <button class="btn btn-primary btn-md" id="btnConfirmarImportacion" style="display:none;">
          <i class="fas fa-check"></i><span>Confirmar e Importar</span>
        </button>
        <button class="btn btn-primary btn-md" id="btnRestart" style="display:none;">
          <i class="fas fa-redo"></i><span>Importar otro archivo</span>
        </button>
      </div>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const $ = (id) => document.getElementById(id);

  /* Auth Guard */
  const authToken = localStorage.getItem('auth_token');
  if (!authToken) {
    window.location.href = '/';
    return;
  }

  /* CSRF */
  function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  }

  /* API Helper */
  async function apiFetch(url, opts = {}) {
    const res = await fetch(url, {
      headers: { 
        'Accept': 'application/json', 
        'Authorization': `Bearer ${authToken}`, 
        'X-CSRF-TOKEN': getCsrf(), 
        ...(opts.headers ?? {}) 
      },
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

  /* Cargar semestres desde API */
  async function loadSemesters() {
    const sel = $('semestreDestino');
    try {
      let currentSemester = null;
      try {
        const curRes = await apiFetch('/api/v1/semesters/current');
        currentSemester = curRes.data ?? null;
      } catch (e) {
        // Ignorar si no hay semestre activo
      }

      const res = await apiFetch('/api/v1/semesters');
      const semesters = res.data ?? [];
      
      if (!semesters.length) {
        sel.innerHTML = '<option value="">Sin semestres registrados</option>';
        showGlobalError('No hay semestres registrados en el sistema. Cree un semestre primero.');
        disableUpload();
        return;
      }

      sel.innerHTML = semesters.map(s => {
        const isCurrent = currentSemester && s.id === currentSemester.id;
        return `<option value="${s.id}" ${isCurrent ? 'selected' : ''}>${s.name}${isCurrent ? ' (Vigente)' : ''}</option>`;
      }).join('');

      if (!currentSemester) {
        showGlobalError('Atención: No existe ningún semestre vigente en el sistema. Debe activar un semestre antes de poder registrar o importar horarios.');
        disableUpload();
      } else {
        sel.value = currentSemester.id;
        $('semestreStatusBlock').innerHTML = '';
      }
    } catch (e) {
      sel.innerHTML = '<option value="">Error al cargar semestres</option>';
      showGlobalError('No se pudieron cargar los semestres desde la API.');
    }
  }

  function showGlobalError(text) {
    $('semestreStatusBlock').innerHTML = `
      <div class="alert alert-danger" style="margin-top:0;">
        <i class="fas fa-exclamation-triangle"></i>
        <span>${text}</span>
      </div>
    `;
  }

  function disableUpload() {
    $('dropzone').style.pointerEvents = 'none';
    $('dropzone').style.opacity = '0.5';
    $('btnProcesar').disabled = true;
  }

  /* Drag & Drop */
  const dz = $('dropzone');
  const fi = $('fileInput');
  let selectedFile = null;

  dz.addEventListener('click', () => fi.click());
  fi.addEventListener('change', (e) => handleFileSelection(e.target.files[0] || null));
  
  dz.addEventListener('dragover', (e) => { 
    e.preventDefault(); 
    dz.classList.add('drag-active'); 
  });
  dz.addEventListener('dragleave', () => dz.classList.remove('drag-active'));
  dz.addEventListener('drop', (e) => {
    e.preventDefault();
    dz.classList.remove('drag-active');
    handleFileSelection(e.dataTransfer.files[0] || null);
  });

  function handleFileSelection(file) {
    if (!file) return;

    selectedFile = file;
    $('fileName').textContent = file.name;
    $('fileSize').textContent = `${(file.size / 1024).toFixed(1)} KB`;

    // Cambiar icono según extensión
    const ext = file.name.split('.').pop().toLowerCase();
    const icon = $('fileIcon');
    if (ext === 'xlsx') {
      icon.className = 'fas fa-file-excel imp-file-icon';
      icon.style.color = '#107C41';
    } else {
      icon.className = 'fas fa-file-csv imp-file-icon';
      icon.style.color = '#134474';
    }

    $('uploadContainer').style.display = 'none';
    $('selectedContainer').style.display = 'flex';
    $('msgBox').innerHTML = '';

    // Validar extensión localmente
    if (!['csv', 'xlsx'].includes(ext)) {
      showErrorAlert('Solo se aceptan archivos con extensión .csv o .xlsx');
      $('btnProcesar').disabled = true;
      $('previewWrapper').style.display = 'none';
      return;
    } else {
      $('btnProcesar').disabled = false;
    }

    // Si es un CSV, intentamos mostrar previsualización de las primeras 5 filas
    if (ext === 'csv') {
      const reader = new FileReader();
      reader.onload = function (e) {
        const text = e.target.result;
        const rows = parseCSV(text);
        renderPreview(rows);
      };
      reader.readAsText(file);
    } else {
      // Para XLSX mostramos que está listo
      $('previewWrapper').style.display = 'none';
    }
  }

  /* CSV Parser minimalista y robusto */
  function parseCSV(text) {
    let p = '', r = [];
    let q = false;
    let row = [''];
    for (let i = 0; i < text.length; i++) {
        let c = text[i];
        let next = text[i+1];
        if (c === '"') {
            if (q && next === '"') { row[row.length - 1] += '"'; i++; }
            else { q = !q; }
        } else if (c === ',' && !q) {
            row.push('');
        } else if ((c === '\r' || c === '\n') && !q) {
            if (c === '\r' && next === '\n') { i++; }
            r.push(row);
            row = [''];
        } else {
            row[row.length - 1] += c;
        }
    }
    if (row.length > 1 || row[0] !== '') {
        r.push(row);
    }
    return r;
  }

  function renderPreview(rows) {
    const body = $('previewBody');
    body.innerHTML = '';
    
    if (rows.length <= 1) {
      $('previewWrapper').style.display = 'none';
      return;
    }

    const headers = rows[0].map(h => h.trim().toLowerCase());
    const expected = ['aula', 'docente', 'materia', 'grupo', 'dias', 'hora_inicio', 'hora_fin'];
    const missing = expected.filter(e => !headers.includes(e));

    if (missing.length) {
      showErrorAlert(`Estructura incorrecta. Columnas faltantes: [${missing.join(', ')}]`);
      $('btnProcesar').disabled = true;
      $('previewWrapper').style.display = 'none';
      return;
    }

    // Filtrar filas vacías
    const dataRows = rows.slice(1).filter(r => r.join('').trim() !== '');
    $('previewCount').textContent = `${dataRows.length} fila(s) detectada(s)`;

    // Mapear encabezados a índices
    const map = {};
    headers.forEach((h, idx) => map[h] = idx);

    // Tomar las primeras 5 filas para mostrar
    const previewData = dataRows.slice(0, 5);
    body.innerHTML = previewData.map(r => `
      <tr>
        <td>${r[map['aula']] ?? '-'}</td>
        <td>${r[map['docente']] ?? '-'}</td>
        <td>${r[map['materia']] ?? '-'}</td>
        <td>${r[map['grupo']] ?? '-'}</td>
        <td>${r[map['dias']] ?? '-'}</td>
        <td>${r[map['hora_inicio']] ?? '-'}</td>
        <td>${r[map['hora_fin']] ?? '-'}</td>
      </tr>
    `).join('');

    $('previewWrapper').style.display = 'flex';
  }

  function showErrorAlert(text) {
    $('msgBox').innerHTML = `
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <span>${text}</span>
      </div>
    `;
  }

  /* Quitar Archivo */
  $('btnRemoveFile').addEventListener('click', resetSelection);
  $('btnCancel').addEventListener('click', resetSelection);

  function resetSelection() {
    selectedFile = null;
    fi.value = '';
    $('uploadContainer').style.display = 'block';
    $('selectedContainer').style.display = 'none';
    $('previewWrapper').style.display = 'none';
    $('msgBox').innerHTML = '';
    isSubmitting = false;
    $('btnProcesar').disabled = false;
  }

  /* Descarga de plantilla */
  $('btnPlantilla').addEventListener('click', () => {
    const csv = "\uFEFF" +
      "aula,docente,materia,grupo,dias,hora_inicio,hora_fin\n" +
      "A-101,SAM-00123,Matemáticas I,1A,\"Lunes, Miércoles\",08:00,09:30\n";
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'plantilla_horarios.csv';
    a.click();
    URL.revokeObjectURL(a.href);
  });

  /* Procesar / Importar */
  let errorRowsLog = [];
  let currentBatchId = null;
  let isSubmitting = false;

  $('btnProcesar').addEventListener('click', async () => {
    if (isSubmitting) return;
    const semId = $('semestreDestino').value;
    if (!selectedFile) { showErrorAlert('Selecciona un archivo CSV/XLSX antes de procesar.'); return; }
    if (!semId)        { showErrorAlert('Selecciona el semestre destino antes de procesar.'); return; }

    isSubmitting = true;
    $('btnProcesar').disabled = true;

    $('selectedContainer').style.display = 'none';
    $('configSection').style.display = 'none';
    
    // Configurar loader para Validación
    $('progressTitle').textContent = 'Procesando Validación';
    $('progressSubtitle').textContent = 'Validando solapamientos, semestres y existencias. Por favor, espere...';
    $('progressContainer').style.display = 'block';

    try {
      const formData = new FormData();
      formData.append('file', selectedFile);
      formData.append('semester_id', semId);

      const res = await fetch('/api/v1/class-schedules/import', {
        method: 'POST',
        headers: { 
          'Accept': 'application/json', 
          'Authorization': `Bearer ${authToken}`, 
          'X-CSRF-TOKEN': getCsrf() 
        },
        body: formData,
      });
      const json = await res.json();
      if (!res.ok) throw { status: res.status, json };

      const batchId = json.data?.batchId ?? json.data?.batch_id ?? null;
      currentBatchId = batchId;

      if (batchId) {
        let attempts = 0;
        const maxAttempts = 15; // 30 segundos (15 intentos x 2s)

        const pollReport = async () => {
          try {
            const rptRes = await apiFetch(`/api/v1/class-schedules/import/${batchId}/report`);
            if (rptRes.statusCode === 202) {
              if (attempts < maxAttempts) {
                attempts++;
                setTimeout(pollReport, 2000); // Polling cada 2 segundos
              } else {
                showFinalError('La importación está tardando demasiado. Verifique la base de datos.');
              }
            } else {
              isSubmitting = false;
              $('btnProcesar').disabled = false;
              renderResults(rptRes.data ?? [], false); // false = Preview Mode
            }
          } catch (e) {
            showFinalError(e.json?.message ?? 'Error al obtener el reporte de importación.');
          }
        };

        setTimeout(pollReport, 2000); // Polling cada 2 segundos
      } else {
        isSubmitting = false;
        $('btnProcesar').disabled = false;
        renderResults(json.data?.rows ?? (Array.isArray(json.data) ? json.data : []), false);
      }
    } catch (err) {
      showFinalError(err.json?.message ?? 'Error al procesar el archivo en el servidor.');
    }
  });

  function showFinalError(msg) {
    isSubmitting = false;
    $('btnProcesar').disabled = false;
    $('progressContainer').style.display = 'none';
    $('configSection').style.display = 'flex';
    $('selectedContainer').style.display = 'flex';
    showErrorAlert(msg);
  }

  /* Render de Resultados */
  function renderResults(rows, isFinal = false) {
    $('progressContainer').style.display = 'none';
    $('resultContainer').style.display = 'flex';

    const okRows = rows.filter(r => r.status === 'imported' || r.ok === true || r.estado === 'Importada');
    const badRows = rows.filter(r => r.status === 'discarded' || r.ok === false || r.estado === 'Descartada');

    // Cambiar etiquetas dinámicamente según la fase
    if (isFinal) {
      $('resultTitle').textContent = 'Importación Completada';
      $('resultSubtitle').textContent = 'Los horarios válidos se han guardado con éxito en la base de datos.';
      $('statsOkLabel').textContent = 'Horarios Importados';
      $('statsBadLabel').textContent = 'Horarios Omitidos';
      
      $('btnCancelConfirm').style.display = 'none';
      $('btnConfirmarImportacion').style.display = 'none';
      $('btnRestart').style.display = 'inline-flex';
    } else {
      $('resultTitle').textContent = 'Previsualización y Validación de Horarios';
      $('resultSubtitle').textContent = 'Por favor, revise el reporte de validación antes de confirmar la importación.';
      $('statsOkLabel').textContent = 'Horarios Válidos';
      $('statsBadLabel').textContent = 'Horarios Omitidos';

      $('btnCancelConfirm').style.display = 'inline-flex';
      if (okRows.length > 0) {
        $('btnConfirmarImportacion').style.display = 'inline-flex';
      } else {
        $('btnConfirmarImportacion').style.display = 'none';
      }
      $('btnRestart').style.display = 'none';
    }

    $('statsOk').textContent = okRows.length;
    $('statsBad').textContent = badRows.length;

    errorRowsLog = badRows;
    if (badRows.length > 0) {
      $('btnDownloadReport').style.display = 'inline-flex';
      $('finalAlertBox').innerHTML = `
        <div class="alert alert-danger" style="margin-top:0;">
          <i class="fas fa-exclamation-triangle"></i>
          <span>Se detectaron ${badRows.length} fila(s) omitida(s) con errores de negocio o solapamiento.</span>
        </div>
      `;
    } else {
      $('btnDownloadReport').style.display = 'none';
      if (isFinal) {
        $('finalAlertBox').innerHTML = `
          <div class="alert alert-success" style="background:rgba(40,167,69,0.06); border-color:rgba(40,167,69,0.3); color:#1a692c; display:flex; align-items:center; gap:12px;">
            <i class="fas fa-check-circle" style="color:var(--gama-exito); font-size:1.2rem;"></i>
            <span>Todos los horarios se han importado exitosamente y sin ningún error.</span>
          </div>
        `;
      } else {
        $('finalAlertBox').innerHTML = `
          <div class="alert alert-success" style="background:rgba(40,167,69,0.06); border-color:rgba(40,167,69,0.3); color:#1a692c; display:flex; align-items:center; gap:12px;">
            <i class="fas fa-info-circle" style="color:var(--gama-azul-profundo); font-size:1.2rem;"></i>
            <span>Todos los horarios del archivo son válidos. Presione 'Confirmar e Importar' para guardarlos en la base de datos.</span>
          </div>
        `;
      }
    }

    const tbody = $('resultBody');
    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--gama-gris-500);">Sin filas procesadas.</td></tr>';
      return;
    }

    tbody.innerHTML = rows.map((r, idx) => {
      const isOk = r.status === 'imported' || r.ok === true || r.estado === 'Importada';
      const trCls = isOk ? 'tr-ok' : 'tr-bad';
      const badgeCls = isOk ? 'ok' : 'bad';
      
      let estado = 'Descartada';
      if (isOk) {
        estado = isFinal ? 'Importada' : 'Válida';
      }

      const line = r.row ?? (idx + 2);
      const aula = r.classroomName ?? r.classroom_name ?? r.aula ?? '-';
      const doc = r.teacherExternalId ?? r.teacher_external_id ?? r.docente ?? '-';
      const mat = r.subjectName ?? r.subject_name ?? r.materia ?? '-';
      const dia = r.weekday ?? r.dias ?? '-';
      const hora = r.startTime && r.endTime
                    ? `${r.startTime} - ${r.endTime}`
                    : (r.hora_inicio ? `${r.hora_inicio} - ${r.hora_fin}` : '-');
      const det = r.error ?? (isOk ? 'Validado exitosamente' : '-');

      return `
        <tr class="${trCls}">
          <td><strong>${line}</strong></td>
          <td><span class="badge-status ${badgeCls}">${estado}</span></td>
          <td>${aula}</td>
          <td>${doc}</td>
          <td>${mat}</td>
          <td>${dia}</td>
          <td>${hora}</td>
          <td style="${!isOk ? 'color:var(--gama-error); font-weight:500;' : ''}">${det}</td>
        </tr>
      `;
    }).join('');
  }

  /* Cancelar Confirmación (Limpiar sin guardar) */
  $('btnCancelConfirm').addEventListener('click', () => {
    resetSelection();
    $('resultContainer').style.display = 'none';
    $('configSection').style.display = 'grid';
    $('uploadContainer').style.display = 'block';
  });

  /* Confirmar e Importar Realmente */
  let isConfirming = false;
  $('btnConfirmarImportacion').addEventListener('click', async () => {
    if (isConfirming) return;
    if (!currentBatchId) {
      showErrorAlert('ID de lote no disponible.');
      return;
    }

    isConfirming = true;
    $('btnConfirmarImportacion').disabled = true;

    $('resultContainer').style.display = 'none';
    
    // Configurar loader para Confirmación/Escritura
    $('progressTitle').textContent = 'Guardando Horarios';
    $('progressSubtitle').textContent = 'Confirmando importación y guardando registros de forma segura en la base de datos...';
    $('progressContainer').style.display = 'block';

    try {
      const res = await fetch('/api/v1/class-schedules/import/confirm', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json', 
          'Authorization': `Bearer ${authToken}`, 
          'X-CSRF-TOKEN': getCsrf() 
        },
        body: JSON.stringify({ batch_id: currentBatchId }),
      });
      const json = await res.json();
      if (!res.ok) throw { status: res.status, json };

      // Recuperar el reporte original para mostrar como final
      const rptRes = await apiFetch(`/api/v1/class-schedules/import/${currentBatchId}/report`);
      isConfirming = false;
      $('btnConfirmarImportacion').disabled = false;
      renderResults(rptRes.data ?? [], true); // true = Final Success Mode
    } catch (err) {
      isConfirming = false;
      $('btnConfirmarImportacion').disabled = false;
      $('progressContainer').style.display = 'none';
      $('resultContainer').style.display = 'flex';
      showErrorAlert(err.json?.message ?? 'Error al guardar los horarios confirmados.');
    }
  });

  /* Descarga del reporte de errores */
  $('btnDownloadReport').addEventListener('click', () => {
    if (!errorRowsLog.length) return;

    let csvContent = "\uFEFF" + "Fila,Aula,Docente,Materia,Dias,Hora Inicio,Hora Fin,Error Encontrado\n";
    errorRowsLog.forEach(r => {
      const line = r.row ?? '-';
      const aula = r.classroomName ?? r.classroom_name ?? r.aula ?? '-';
      const doc = r.teacherExternalId ?? r.teacher_external_id ?? r.docente ?? '-';
      const mat = r.subjectName ?? r.subject_name ?? r.materia ?? '-';
      const dia = r.weekday ?? r.dias ?? '-';
      const start = r.startTime ?? r.hora_inicio ?? '-';
      const end = r.endTime ?? r.hora_fin ?? '-';
      const err = (r.error ?? r.detail ?? '').replace(/"/g, '""');

      csvContent += `${line},"${aula}","${doc}","${mat}","${dia}","${start}","${end}","${err}"\n`;
    });

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `reporte_errores_importacion_${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
    URL.revokeObjectURL(a.href);
  });

  /* Reiniciar Importador */
  $('btnRestart').addEventListener('click', () => {
    resetSelection();
    $('resultContainer').style.display = 'none';
    $('configSection').style.display = 'grid';
    $('uploadContainer').style.display = 'block';
  });

  /* Arranque inicial */
  loadSemesters();
});
</script>
@endsection
