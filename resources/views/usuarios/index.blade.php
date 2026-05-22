{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Usuarios y Roles conectado a API SAM identities
 * @autor          Rubén Alejandro Nolasco Ruiz, Equipo GAMA
 * @autorizador    Rubén Alejandro Nolasco Ruiz
 * @prueba         Diego Miguel Hernandez Fabela
 * @mantenimiento  Ghael Garcia Manjarrez
 * @version        1.1.0
 * @creado         07/05/2026
 * @modificado     19/05/2026
 * @cambios        19/05/2026 - Conexión a API REST, eliminación de datos mock
 */
--}}

@extends('layouts.app')

@section('title', 'Usuarios y Roles - GAMA Solutions')

@section('content')
<style>
  .usr-main { margin-left: var(--sidebar-width, 240px); min-height: 100vh; background: var(--ice-blue); padding: 28px 32px; }
  .usr-head { margin-bottom: 16px; }
  .usr-title { font-size: 26px; font-weight: 700; color: var(--midnight); margin-bottom: 4px; }
  .usr-sub { color: var(--soft-steel); font-size: 14px; }
  .usr-card { background:#fff; border:1px solid var(--mist-blue); border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 14px; }
  .usr-body { padding: 16px; }
  .usr-row { display: grid; grid-template-columns: minmax(260px, 1fr) 220px auto; gap: 10px; align-items: center; }
  .usr-input, .usr-select {
    width: 100%;
    border: 1px solid var(--mist-blue);
    background: var(--ice-blue);
    border-radius: var(--radius-md);
    padding: 10px 12px;
    font-size: 14px;
    font-family: var(--font-main);
    outline: none;
  }
  .usr-input:focus, .usr-select:focus { border-color: var(--corp-orange); }
  .usr-hint { font-size: 12px; color: var(--soft-steel); margin-top: 8px; }
  .usr-table-wrap { overflow-x: auto; }
  .usr-table { width: 100%; border-collapse: collapse; font-size: 13px; }
  .usr-table thead th { background: var(--deep-blue); color:#fff; text-align:left; padding: 11px 12px; }
  .usr-table tbody tr:nth-child(odd) { background: var(--light-blue); }
  .usr-table tbody tr:nth-child(even) { background: #fff; }
  .usr-table tbody tr:hover { background: var(--light-orange); }
  .usr-table td { padding: 11px 12px; border-bottom: 1px solid var(--mist-blue); vertical-align: middle; }
  .usr-table tr.selected { outline: 2px solid var(--corp-orange); outline-offset: -2px; }
  .role-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
  .role-admin { background: rgba(19,68,116,.14); color: #134474; }
  .role-docente { background: rgba(30,90,138,.14); color: #1E5A8A; }
  .role-none { background: rgba(108,117,125,.15); color: #6c757d; }
  .usr-actions { display:flex; gap: 6px; align-items:center; }
  .usr-msg { margin-top: 10px; font-size: 13px; padding: 10px 12px; border-radius: var(--radius-md); display: none; }
  .usr-msg.error { display:block; background: rgba(255,0,0,.08); border:1px solid rgba(255,0,0,.35); color:#b00000; }
  .usr-empty { padding: 24px; text-align: center; color: var(--soft-steel); }
  .spinner { display:inline-block; width:20px; height:20px; border:3px solid var(--mist-blue); border-top-color:var(--deep-blue); border-radius:50%; animation:spin 0.7s linear infinite; vertical-align:middle; margin-right:6px; }
  @keyframes spin { to { transform:rotate(360deg); } }
  .loading-row td { text-align:center; padding:20px; color:var(--soft-steel); }
  @media (max-width: 1024px) { .usr-main { margin-left: 0; } }
  @media (max-width: 860px) { .usr-row { grid-template-columns: 1fr; } .usr-row .btn { width: 100%; justify-content: center; } }
</style>

<div class="usr-main">
  <div class="usr-head">
    <h1 class="usr-title">Usuarios y Roles</h1>
    <p class="usr-sub">Fuente única SAM: búsqueda, asignación y revocación de roles con control administrativo.</p>
  </div>

  <section class="usr-card">
    <div class="usr-body">
      <div class="usr-row">
        <input id="searchSam" class="usr-input" placeholder="Buscar usuario en SAM (nombre o correo institucional)...">
        <select id="roleSelect" class="usr-select">
          <option value="">Selecciona rol...</option>
          <option value="admin">Administrador</option>
          <option value="teacher">Docente</option>
        </select>
        <button id="btnAsignar" class="btn btn-primary btn-md"><i class="fas fa-user-shield"></i><span>Asignar Rol</span></button>
      </div>
      <div class="usr-hint">
        RN-04: El rol <strong>Alumno</strong> no se asigna manualmente; se determina automáticamente por SAM.
      </div>
      <div id="msgBox" class="usr-msg"></div>
    </div>
  </section>

  <section class="usr-card">
    <div class="usr-table-wrap">
      <table class="usr-table">
        <thead>
          <tr>
            <th>Nombre completo</th>
            <th>Correo institucional</th>
            <th>Rol actual</th>
            <th style="width:130px;">Acciones</th>
          </tr>
        </thead>
        <tbody id="usersBody"></tbody>
      </table>
    </div>
  </section>

  <div class="toast-container" id="toastContainer"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var TOKEN = localStorage.getItem('auth_token');
  if (!TOKEN) { window.location.href = '/'; return; }

  var roleLabels = { admin: 'Administrador', teacher: 'Docente' };
  var allIdentities = [];
  var displayedRows = [];
  var selectedExternalId = null;
  var searchTimer = null;

  var $ = function (id) { return document.getElementById(id); };
  var body = $('usersBody');
  var search = $('searchSam');
  var roleSelect = $('roleSelect');
  var msgBox = $('msgBox');

  function apiHeaders() {
    return { 'Accept': 'application/json', 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + TOKEN };
  }

  function apiGet(url) {
    return fetch(url, { method: 'GET', headers: apiHeaders() }).then(function (r) {
      if (r.status === 401) { localStorage.clear(); window.location.href = '/'; throw new Error('Unauthenticated'); }
      return r.json().then(function (d) { if (!r.ok) throw d; return d; });
    });
  }

  function apiPost(url, body) {
    return fetch(url, { method: 'POST', headers: apiHeaders(), body: JSON.stringify(body) }).then(function (r) {
      if (r.status === 401) { localStorage.clear(); window.location.href = '/'; throw new Error('Unauthenticated'); }
      return r.json().then(function (d) { if (!r.ok) throw d; return d; });
    });
  }

  function showError(message) {
    msgBox.className = 'usr-msg error';
    msgBox.textContent = message;
  }
  function clearError() {
    msgBox.className = 'usr-msg';
    msgBox.textContent = '';
  }

  function roleBadge(role) {
    if (role === 'admin') return '<span class="role-badge role-admin">Administrador</span>';
    if (role === 'teacher') return '<span class="role-badge role-docente">Docente</span>';
    return '<span class="role-badge role-none">Sin rol</span>';
  }

  function toast(title, message) {
    var t = document.createElement('div');
    t.className = 'toast success';
    t.innerHTML = '<div class="toast-icon"><i class="fas fa-check"></i></div><div class="toast-content"><div class="toast-title">' + title + '</div><div class="toast-message">' + message + '</div></div><button class="toast-close"><i class="fas fa-times"></i></button>';
    $('toastContainer').appendChild(t);
    setTimeout(function () { t.classList.add('show'); }, 10);
    var rm = function () { t.classList.remove('show'); setTimeout(function () { t.remove(); }, 260); };
    var tm = setTimeout(rm, 4500);
    t.querySelector('.toast-close').addEventListener('click', function () { clearTimeout(tm); rm(); });
  }

  function normalizeIdentity(item) {
    return {
      externalId: item.externalId || item.external_id || '',
      fullName: item.fullName || item.full_name || 'Sin nombre',
      email: item.email || '',
      role: item.role || null
    };
  }

  function renderRows(rows) {
    displayedRows = rows;
    if (!rows.length) {
      body.innerHTML = '<tr><td colspan="4" class="usr-empty">Sin resultados en SAM</td></tr>';
      return;
    }
    body.innerHTML = rows.map(function (u) {
      var hasRole = u.role ? true : false;
      return '<tr data-select="' + u.externalId + '" class="' + (selectedExternalId === u.externalId ? 'selected' : '') + '">' +
        '<td>' + u.fullName + '</td>' +
        '<td>' + u.email + '</td>' +
        '<td>' + roleBadge(u.role) + '</td>' +
        '<td><div class="usr-actions">' +
        '<button class="btn btn-secondary btn-sm" data-revoke="' + u.externalId + '" ' + (!hasRole ? 'disabled' : '') + '>' +
        '<i class="fas fa-user-minus"></i></button></div></td></tr>';
    }).join('');
  }

  function loadAll() {
    body.innerHTML = '<tr class="loading-row"><td colspan="4"><span class="spinner"></span>Cargando usuarios...</td></tr>';
    apiGet('/api/v1/sam-identities').then(function (resp) {
      var raw = (resp.data && resp.data.data) ? resp.data.data : (Array.isArray(resp.data) ? resp.data : []);
      allIdentities = raw.map(normalizeIdentity);
      renderRows(allIdentities);
    })['catch'](function (err) {
      body.innerHTML = '<tr><td colspan="4" class="usr-empty">Error al cargar usuarios.</td></tr>';
      if (err && err.message) showError(err.message);
    });
  }

  function doSearch() {
    clearError();
    var q = search.value.trim();
    if (!q) {
      renderRows(allIdentities);
      return;
    }
    body.innerHTML = '<tr class="loading-row"><td colspan="4"><span class="spinner"></span>Buscando...</td></tr>';
    apiGet('/api/v1/sam-identities/search?q=' + encodeURIComponent(q)).then(function (resp) {
      var raw = Array.isArray(resp.data) ? resp.data : [];
      var results = raw.map(normalizeIdentity);
      if (!results.some(function (r) { return r.externalId === selectedExternalId; })) selectedExternalId = null;
      renderRows(results);
    })['catch'](function (err) {
      body.innerHTML = '<tr><td colspan="4" class="usr-empty">Error en la búsqueda.</td></tr>';
      if (err && err.message) showError(err.message);
    });
  }

  search.addEventListener('input', function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(doSearch, 300);
  });

  body.addEventListener('click', function (e) {
    var row = e.target.closest('[data-select]');
    if (row) {
      selectedExternalId = row.dataset.select;
      renderRows(displayedRows);
      clearError();
      return;
    }
    var revoke = e.target.closest('[data-revoke]');
    if (revoke) {
      var target = revoke.dataset.revoke;
      var user = displayedRows.find(function (u) { return u.externalId === target; });
      if (!window.confirm('Confirma revocar rol de ' + (user ? user.fullName : 'usuario') + '?')) return;
      apiPost('/api/v1/sam-identities/' + encodeURIComponent(target) + '/assign-role', { role: 'teacher' }).then(function () {
        if (user) user.role = 'teacher';
        renderRows(displayedRows);
        toast('Rol revocado', 'El rol se ha restablecido a Docente.');
      })['catch'](function (err) {
        if (err && err.message) showError(err.message);
      });
    }
  });

  $('btnAsignar').addEventListener('click', function () {
    clearError();
    if (!selectedExternalId) {
      showError('Selecciona un usuario de la tabla para asignar rol.');
      return;
    }
    if (!roleSelect.value) {
      showError('Selecciona un rol antes de asignar.');
      return;
    }
    apiPost('/api/v1/sam-identities/' + encodeURIComponent(selectedExternalId) + '/assign-role', { role: roleSelect.value }).then(function () {
      var user = displayedRows.find(function (u) { return u.externalId === selectedExternalId; });
      if (user) user.role = roleSelect.value;
      renderRows(displayedRows);
      toast('Rol asignado', 'Rol ' + (roleLabels[roleSelect.value] || roleSelect.value) + ' asignado correctamente.');
    })['catch'](function (err) {
      if (err && err.message) showError(err.message);
    });
  });

  loadAll();
});
</script>
@endsection
