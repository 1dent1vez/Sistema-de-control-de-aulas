{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Configuración Institucional conectada a API REST
 * @autor          Rubén Alejandro Nolasco Ruiz, Equipo GAMA
 * @autorizador    Rubén Alejandro Nolasco Ruiz
 * @prueba         Diego Miguel Hernandez Fabela
 * @mantenimiento  Ghael Garcia Manjarrez
 * @version        1.1.0
 * @creado         07/05/2026
 * @modificado     19/05/2026
 * @cambios        19/05/2026 - Conexión a API REST, eliminación de datos simulados
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
  .spinner { display:inline-block; width:20px; height:20px; border:3px solid var(--mist-blue); border-top-color:var(--deep-blue); border-radius:50%; animation:spin 0.7s linear infinite; vertical-align:middle; margin-right:6px; }
  @keyframes spin { to { transform:rotate(360deg); } }
  .loading-overlay { display:flex; align-items:center; justify-content:center; gap:10px; padding:40px; color:var(--soft-steel); }
  .hidden { display:none !important; }
  @media (max-width: 1100px) { .cfg-main { margin-left: 0; } .cfg-grid { grid-template-columns: 1fr; } }
</style>

<div class="cfg-main">
  <div class="cfg-head">
    <h1 class="cfg-title">Configuración Institucional</h1>
    <p class="cfg-sub">Personalización visual por institución activa (RNF-04). Cambios aislados por institución (RN-04).</p>
  </div>

  <div id="mainLoader" class="loading-overlay">
    <div class="spinner"></div>
    <span>Cargando configuración...</span>
  </div>

  <div id="mainContent" class="hidden">
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
            </div>
          </div>
          <div class="note info">
            Los cambios solo se aplican a la institución activa y no impactan otras instituciones (RN-04).
          </div>
        </div>
      </section>
    </div>
  </div>

  <div class="toast-container" id="toastContainer"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var TOKEN = localStorage.getItem('auth_token');
  if (!TOKEN) { window.location.href = '/'; return; }

  var HEX_RE = /^#[0-9A-Fa-f]{6}$/;
  var MAX_LOGO = 2 * 1024 * 1024;
  var $ = function (id) { return document.getElementById(id); };

  var state = {
    logoFile: null,
    nombre: '',
    palette: '',
    hex: '',
    institutionId: null,
    institutionCode: ''
  };

  function apiHeaders() {
    return { 'Accept': 'application/json', 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + TOKEN };
  }

  function apiGet(url) {
    return fetch(url, { method: 'GET', headers: apiHeaders() }).then(function (r) {
      if (r.status === 401) { localStorage.clear(); window.location.href = '/'; throw new Error('No autenticado'); }
      return r.json().then(function (d) { if (!r.ok) throw d; return d; });
    });
  }

  function apiPut(url, body) {
    return fetch(url, { method: 'PUT', headers: apiHeaders(), body: JSON.stringify(body) }).then(function (r) {
      if (r.status === 401) { localStorage.clear(); window.location.href = '/'; throw new Error('No autenticado'); }
      return r.json().then(function (d) { if (!r.ok) throw d; return d; });
    });
  }

  function setErr(id, msg) { var e = $(id); e.textContent = msg; e.classList.add('show'); }
  function clearErr(id) { var e = $(id); e.textContent = ''; e.classList.remove('show'); }
  function clearAllErr() { ['eLogo','eNombre','eHex'].forEach(clearErr); $('fHex').classList.remove('err-border'); }

  function primaryColor() {
    if (state.palette && state.palette !== 'custom') return state.palette;
    return state.hex || '#134474';
  }

  function refreshPreview() {
    $('liveBar').style.backgroundColor = primaryColor();
    $('liveInstName').textContent = state.nombre || 'Institución Activa';
  }

  function showToast(title, message) {
    var t = document.createElement('div');
    t.className = 'toast success';
    t.innerHTML = '<div class="toast-icon"><i class="fas fa-check"></i></div><div class="toast-content"><div class="toast-title">' + title + '</div><div class="toast-message">' + message + '</div></div><button class="toast-close"><i class="fas fa-times"></i></button>';
    $('toastContainer').appendChild(t);
    setTimeout(function () { t.classList.add('show'); }, 10);
    var rm = function () { t.classList.remove('show'); setTimeout(function () { t.remove(); }, 260); };
    var tm = setTimeout(rm, 4500);
    t.querySelector('.toast-close').addEventListener('click', function () { clearTimeout(tm); rm(); });
  }

  function validate() {
    clearAllErr();
    var ok = true;
    if (!state.nombre.trim()) { setErr('eNombre', 'El nombre institucional es obligatorio.'); ok = false; }
    if (state.nombre.length > 100) { setErr('eNombre', 'Máximo 100 caracteres.'); ok = false; }
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

  function init() {
    apiGet('/api/v1/institutions').then(function (instResp) {
      var raw = (instResp && instResp.data) ? instResp.data : [];
      var institutions = Array.isArray(raw) ? raw : (raw.data ? raw.data : []);

      if (institutions.length > 0) {
        // Buscar la institución activa utilizando la clave camelCase isActive
        var inst = institutions.find(function (i) { return i.isActive === true; }) || institutions[0];
        state.institutionId = inst.id;
        state.institutionCode = inst.code || '';
        state.nombre = inst.name || '';
        $('fNombre').value = state.nombre;
        $('nameCount').textContent = String(state.nombre.length);
      } else {
        // Si no hay ninguna institución en absoluto, deshabilitar formulario y botón
        $('fNombre').disabled = true;
        $('fPalette').disabled = true;
        $('fHex').disabled = true;
        $('fLogo').disabled = true;
        $('btnGuardar').disabled = true;
        showToast('Error', 'No hay instituciones registradas en el sistema. Registre una institución primero.');
      }

      $('mainLoader').classList.add('hidden');
      $('mainContent').classList.remove('hidden');
      refreshPreview();
    })['catch'](function (err) {
      showToast('Error', err && err.message ? err.message : 'No se pudo cargar la configuración.');
      $('mainLoader').classList.add('hidden');
      $('mainContent').classList.remove('hidden');
    });
  }

  $('fLogo').addEventListener('change', function (e) {
    clearErr('eLogo');
    var file = e.target.files[0] || null;
    if (!file) { state.logoFile = null; return; }
    var validType = /image\/(png|jpeg)/.test(file.type);
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
    var reader = new FileReader();
    reader.onload = function () {
      $('logoPreview').src = reader.result;
      $('logoPreview').style.display = 'block';
    };
    reader.readAsDataURL(file);
  });

  $('fNombre').addEventListener('input', function (e) {
    state.nombre = e.target.value;
    $('nameCount').textContent = String(state.nombre.length);
    refreshPreview();
  });

  $('fPalette').addEventListener('change', function (e) {
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

  $('fHex').addEventListener('input', function (e) {
    state.hex = e.target.value.trim();
    if (state.hex) state.palette = 'custom';
    refreshPreview();
  });

  $('btnGuardar').addEventListener('click', function () {
    if (!validate()) return;

    if (!state.institutionId) {
      showToast('Error', 'No hay institución activa para guardar.');
      return;
    }

    var btn = $('btnGuardar');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner" style="width:16px;height:16px;border-width:2px;margin:0 auto;"></div>';

    var payload = {
      name: state.nombre.trim(),
      code: state.institutionCode || state.nombre.trim().substring(0, 10).toUpperCase().replace(/\s+/g, '_'),
      is_active: true
    };

    apiPut('/api/v1/institutions/' + state.institutionId, payload).then(function () {
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-save"></i><span>Guardar Configuración</span>';
      showToast('Configuración guardada', 'Cambios aplicados correctamente a la institución activa.');
    })['catch'](function (err) {
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-save"></i><span>Guardar Configuración</span>';
      if (err && err.message) showToast('Error', err.message);
      else showToast('Error', 'No se pudo guardar la configuración.');
    });
  });

  init();
});
</script>
@endsection
