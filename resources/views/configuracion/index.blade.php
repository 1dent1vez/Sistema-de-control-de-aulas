{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Configuración Institucional - Proyecto B: Sistema de Control de Aulas
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

@section('title', 'Configuración Institucional - GAMA Solutions')

@section('content')
<style>
  .cfg-main { margin-left: var(--sidebar-width, 240px); min-height: 100vh; background: var(--ice-blue); padding: 28px 32px; }
  .cfg-head { margin-bottom: 14px; }
  .cfg-title { font-size: 26px; font-weight: 700; color: var(--midnight); margin-bottom: 4px; }
  .cfg-sub { color: var(--soft-steel); font-size: 14px; }
  .cfg-grid { display: grid; grid-template-columns: 1.1fr .9fr; gap: 14px; align-items: start; }
  .cfg-card { background: #fff; border: 1px solid var(--mist-blue); border-radius: var(--radius-lg); overflow: hidden; }
  .cfg-card-h { padding: 12px 16px; border-bottom: 1px solid var(--mist-blue); font-size: 14px; font-weight: 700; color: var(--deep-blue); }
  .cfg-card-b { padding: 16px; }
  .field { margin-bottom: 12px; }
  .field label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: var(--midnight); }
  .field input, .field select {
    width: 100%; border: 1px solid var(--mist-blue); background: var(--ice-blue); border-radius: var(--radius-md);
    padding: 10px 12px; font-family: var(--font-main); font-size: 14px; outline: none;
  }
  .field input:focus, .field select:focus { border-color: var(--corp-orange); }
  .field input.err-border { border-color: #c62828; box-shadow: 0 0 0 3px rgba(198,40,40,.12); }
  .hint { margin-top: 4px; font-size: 11px; color: var(--soft-steel); text-align: right; }
  .err { margin-top: 5px; color: #b00000; font-size: 12px; display: none; }
  .err.show { display: block; }
  .palette-row { display: grid; grid-template-columns: 1fr 130px; gap: 8px; }
  .logo-box {
    border: 1px dashed var(--mist-blue); border-radius: var(--radius-md); background: #fff; padding: 12px;
    text-align: center; color: var(--soft-steel);
  }
  .logo-preview {
    width: 100%; max-height: 120px; object-fit: contain; display: none; margin-top: 10px;
    border: 1px solid var(--mist-blue); border-radius: 8px; background: #fff;
  }
  .live-preview {
    border: 1px solid var(--mist-blue); border-radius: var(--radius-md); overflow: hidden;
  }
  .live-topbar {
    height: 44px; background: #134474; color: #fff; display: flex; align-items: center; justify-content: space-between;
    padding: 0 12px; transition: background-color .2s ease;
  }
  .live-body { padding: 12px; background: #fff; }
  .live-chip { display: inline-block; padding: 4px 10px; border-radius: 14px; background: var(--ice-blue); color: var(--deep-blue); font-size: 12px; }
  .note { margin-top: 8px; padding: 10px 12px; border-radius: var(--radius-md); font-size: 13px; }
  .note.info { background: rgba(19,68,116,.08); border:1px solid rgba(19,68,116,.2); color: var(--deep-blue); }
  @media (max-width: 1100px) { .cfg-main { margin-left: 0; } .cfg-grid { grid-template-columns: 1fr; } }
</style>

<div class="cfg-main">
  <div class="cfg-head">
    <h1 class="cfg-title">Configuración Institucional</h1>
    <p class="cfg-sub">Personalización visual por institución activa (RNF-04). Cambios aislados por institución (RN-04).</p>
  </div>

  <div class="cfg-grid">
    <section class="cfg-card">
      <div class="cfg-card-h">Formulario de configuración</div>
      <div class="cfg-card-b">
        <div class="field">
          <label>Logotipo institucional (PNG/JPG, máx 2MB)</label>
          <div class="logo-box">
            <input id="fLogo" type="file" accept=".png,.jpg,.jpeg">
            <img id="logoPreview" class="logo-preview" alt="Preview logo">
          </div>
          <div id="eLogo" class="err"></div>
        </div>

        <div class="field">
          <label>Nombre institucional *</label>
          <input id="fNombre" type="text" maxlength="100" placeholder="Ej. Instituto Tecnológico GAMA">
          <div class="hint"><span id="nameCount">0</span>/100</div>
          <div id="eNombre" class="err"></div>
        </div>

        <div class="field">
          <label>Paleta principal *</label>
          <div class="palette-row">
            <select id="fPalette">
              <option value="">Catálogo predefinido...</option>
              <option value="#134474">Deep Corporate Blue</option>
              <option value="#1E5A8A">Royal Blue</option>
              <option value="#5F86A6">Soft Steel Blue</option>
              <option value="custom">Personalizado</option>
            </select>
            <input id="fHex" type="text" placeholder="#RRGGBB">
          </div>
          <div id="eHex" class="err"></div>
        </div>

        <div class="field">
          <label>Semestre activo *</label>
          <select id="fSemestre">
            <option value="">Selecciona...</option>
            <option value="2026-A">2026-A</option>
            <option value="2026-B">2026-B</option>
            <option value="2027-A">2027-A</option>
          </select>
          <div id="eSemestre" class="err"></div>
        </div>

        <button id="btnGuardar" class="btn btn-primary btn-md" style="width:100%; margin-top: 6px;">
          <i class="fas fa-save"></i><span>Guardar Configuración</span>
        </button>
      </div>
    </section>

    <section class="cfg-card">
      <div class="cfg-card-h">Preview live</div>
      <div class="cfg-card-b">
        <div class="live-preview">
          <div class="live-topbar" id="liveBar">
            <strong id="liveInstName">Institución Activa</strong>
            <span>Portal Institucional</span>
          </div>
          <div class="live-body">
            <p style="font-size:13px;color:var(--dark-graphite);margin-bottom:8px;">Vista previa de navegación con color primario aplicado en tiempo real.</p>
            <span class="live-chip" id="liveSem">Semestre: --</span>
          </div>
        </div>
        <div class="note info">
          Los cambios solo se aplican a la institución activa y no impactan otras instituciones (RN-04).
        </div>
      </div>
    </section>
  </div>

  <div class="toast-container" id="toastContainer"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const HEX_RE = /^#[0-9A-Fa-f]{6}$/;
  const MAX_LOGO = 2 * 1024 * 1024;
  const $ = (id) => document.getElementById(id);

  let state = {
    logoFile: null,
    nombre: '',
    palette: '',
    hex: '',
    semestre: '',
    institucionId: 'inst-001'
  };

  function setErr(id, msg) { const e = $(id); e.textContent = msg; e.classList.add('show'); }
  function clearErr(id) { const e = $(id); e.textContent = ''; e.classList.remove('show'); }
  function clearAllErr() {
    ['eLogo','eNombre','eHex','eSemestre'].forEach(clearErr);
    $('fHex').classList.remove('err-border');
  }

  function primaryColor() {
    if (state.palette && state.palette !== 'custom') return state.palette;
    return state.hex || '#134474';
  }

  function refreshPreview() {
    $('liveBar').style.backgroundColor = primaryColor();
    $('liveInstName').textContent = state.nombre || 'Institución Activa';
    $('liveSem').textContent = `Semestre: ${state.semestre || '--'}`;
  }

  function showToast(title, message) {
    const t = document.createElement('div');
    t.className = 'toast success';
    t.innerHTML = `<div class="toast-icon"><i class="fas fa-check"></i></div><div class="toast-content"><div class="toast-title">${title}</div><div class="toast-message">${message}</div></div><button class="toast-close"><i class="fas fa-times"></i></button>`;
    $('toastContainer').appendChild(t);
    setTimeout(() => t.classList.add('show'), 10);
    const rm = () => { t.classList.remove('show'); setTimeout(() => t.remove(), 260); };
    const tm = setTimeout(rm, 4500);
    t.querySelector('.toast-close').addEventListener('click', () => { clearTimeout(tm); rm(); });
  }

  function validate() {
    clearAllErr();
    let ok = true;
    if (!state.nombre.trim()) { setErr('eNombre', 'El nombre institucional es obligatorio.'); ok = false; }
    if (state.nombre.length > 100) { setErr('eNombre', 'Máximo 100 caracteres.'); ok = false; }
    if (!state.semestre) { setErr('eSemestre', 'Selecciona un semestre activo.'); ok = false; }
    if (state.palette === 'custom' || (!state.palette && state.hex)) {
      if (!HEX_RE.test(state.hex)) {
        setErr('eHex', 'Formato HEX inválido. Usa #RRGGBB.');
        $('fHex').classList.add('err-border');
        ok = false;
      }
    }
    if (!state.palette && !state.hex) {
      setErr('eHex', 'Selecciona una paleta del catálogo o ingresa HEX personalizado.');
      ok = false;
    }
    return ok;
  }

  $('fLogo').addEventListener('change', (e) => {
    clearErr('eLogo');
    const file = e.target.files[0] || null;
    if (!file) { state.logoFile = null; return; }
    const validType = /image\/(png|jpeg)/.test(file.type);
    if (!validType) {
      e.target.value = '';
      state.logoFile = null;
      setErr('eLogo', 'Tipo de archivo no permitido. Solo PNG/JPG.');
      return;
    }
    if (file.size > MAX_LOGO) {
      e.target.value = '';
      state.logoFile = null;
      $('logoPreview').style.display = 'none';
      setErr('eLogo', 'El logotipo supera 2MB. Carga un archivo más pequeño.');
      return;
    }
    state.logoFile = file;
    const reader = new FileReader();
    reader.onload = function () {
      $('logoPreview').src = reader.result;
      $('logoPreview').style.display = 'block';
    };
    reader.readAsDataURL(file);
  });

  $('fNombre').addEventListener('input', (e) => {
    state.nombre = e.target.value;
    $('nameCount').textContent = String(state.nombre.length);
    refreshPreview();
  });

  $('fPalette').addEventListener('change', (e) => {
    state.palette = e.target.value;
    if (state.palette && state.palette !== 'custom') {
      state.hex = state.palette;
      $('fHex').value = state.hex;
    } else if (state.palette === 'custom') {
      $('fHex').focus();
      state.hex = $('fHex').value.trim();
    }
    refreshPreview();
  });

  $('fHex').addEventListener('input', (e) => {
    state.hex = e.target.value.trim();
    if (state.hex) state.palette = 'custom';
    refreshPreview();
  });

  $('fSemestre').addEventListener('change', (e) => {
    state.semestre = e.target.value;
    refreshPreview();
  });

  $('btnGuardar').addEventListener('click', () => {
    if (!validate()) return;

    // Simulación de guardado transaccional por institución activa (RN-04).
    const payload = {
      institucion_id: state.institucionId,
      nombre: state.nombre.trim(),
      color_primario: primaryColor(),
      semestre_activo: state.semestre,
      logo: state.logoFile ? state.logoFile.name : null
    };
    void payload;

    showToast('Configuración guardada', 'Cambios aplicados correctamente a la institución activa.');
  });

  refreshPreview();
});
</script>
@endsection
