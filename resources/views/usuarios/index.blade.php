{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Usuarios y Roles - Proyecto B: Sistema de Control de Aulas
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
  .role-lab { background: rgba(95,134,166,.18); color: #5F86A6; }
  .role-none { background: rgba(108,117,125,.15); color: #6c757d; }
  .usr-actions { display:flex; gap: 6px; align-items:center; }
  .usr-msg { margin-top: 10px; font-size: 13px; padding: 10px 12px; border-radius: var(--radius-md); display: none; }
  .usr-msg.error { display:block; background: rgba(255,0,0,.08); border:1px solid rgba(255,0,0,.35); color:#b00000; }
  .usr-empty { padding: 24px; text-align: center; color: var(--soft-steel); }
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
          <option value="Administrador">Administrador</option>
          <option value="Docente">Docente</option>
          <option value="Laboratorista">Laboratorista</option>
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
  const currentUserRole = 'Administrador';
  const SAM_USERS = [
    { sam_id: 'u001', nombre: 'Laura Mendez', correo: 'laura.mendez@instituto.edu', activo: true },
    { sam_id: 'u002', nombre: 'Jose Rivera', correo: 'jose.rivera@instituto.edu', activo: true },
    { sam_id: 'u003', nombre: 'Daniel Rojas', correo: 'daniel.rojas@instituto.edu', activo: true },
    { sam_id: 'u004', nombre: 'Carla Ortega', correo: 'carla.ortega@instituto.edu', activo: false },
    { sam_id: 'u005', nombre: 'Mariana Ponce', correo: 'mariana.ponce@instituto.edu', activo: true }
  ];

  const roleAssignments = new Map([
    ['u001', 'Docente'],
    ['u002', 'Administrador'],
    ['u003', 'Laboratorista']
  ]);

  const $ = (id) => document.getElementById(id);
  const body = $('usersBody');
  const search = $('searchSam');
  const roleSelect = $('roleSelect');
  const msgBox = $('msgBox');
  let selectedSamId = null;
  let currentRows = [];
  let timer = null;

  function showError(message) {
    msgBox.className = 'usr-msg error';
    msgBox.textContent = message;
  }
  function clearError() {
    msgBox.className = 'usr-msg';
    msgBox.textContent = '';
  }
  function roleBadge(role) {
    if (role === 'Administrador') return '<span class="role-badge role-admin">Administrador</span>';
    if (role === 'Docente') return '<span class="role-badge role-docente">Docente</span>';
    if (role === 'Laboratorista') return '<span class="role-badge role-lab">Laboratorista</span>';
    return '<span class="role-badge role-none">Sin rol</span>';
  }
  function toast(title, message) {
    const t = document.createElement('div');
    t.className = 'toast success';
    t.innerHTML = `<div class="toast-icon"><i class="fas fa-check"></i></div><div class="toast-content"><div class="toast-title">${title}</div><div class="toast-message">${message}</div></div><button class="toast-close"><i class="fas fa-times"></i></button>`;
    $('toastContainer').appendChild(t);
    setTimeout(() => t.classList.add('show'), 10);
    const rm = () => { t.classList.remove('show'); setTimeout(() => t.remove(), 260); };
    const tm = setTimeout(rm, 4500);
    t.querySelector('.toast-close').addEventListener('click', () => { clearTimeout(tm); rm(); });
  }

  function samSearch(query) {
    const q = query.trim().toLowerCase();
    if (!q) return [];
    return SAM_USERS.filter(u =>
      u.nombre.toLowerCase().includes(q) || u.correo.toLowerCase().includes(q)
    );
  }

  function isSamUserActive(samId) {
    const user = SAM_USERS.find(u => u.sam_id === samId);
    return !!(user && user.activo);
  }

  function renderRows(rows) {
    currentRows = rows;
    if (!rows.length) {
      body.innerHTML = '<tr><td colspan="4" class="usr-empty">Sin resultados en SAM</td></tr>';
      return;
    }
    body.innerHTML = rows.map(u => {
      const role = roleAssignments.get(u.sam_id) || '';
      return `
        <tr data-select="${u.sam_id}" class="${selectedSamId === u.sam_id ? 'selected' : ''}">
          <td>${u.nombre}</td>
          <td>${u.correo}</td>
          <td>${roleBadge(role)}</td>
          <td>
            <div class="usr-actions">
              <button class="btn btn-secondary btn-sm" data-revoke="${u.sam_id}" ${!role ? 'disabled' : ''}>
                <i class="fas fa-user-minus"></i>
              </button>
            </div>
          </td>
        </tr>`;
    }).join('');
  }

  function doSearch() {
    clearError();
    const rows = samSearch(search.value);
    if (!rows.some(r => r.sam_id === selectedSamId)) selectedSamId = null;
    renderRows(rows);
  }

  search.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(doSearch, 300);
  });

  body.addEventListener('click', (e) => {
    const row = e.target.closest('[data-select]');
    if (row) {
      selectedSamId = row.dataset.select;
      renderRows(currentRows);
      clearError();
      return;
    }
    const revoke = e.target.closest('[data-revoke]');
    if (revoke) {
      if (currentUserRole !== 'Administrador') {
        showError('Solo el rol Administrador puede asignar o revocar roles.');
        return;
      }
      const target = revoke.dataset.revoke;
      const user = SAM_USERS.find(u => u.sam_id === target);
      const ok = window.confirm(`Confirma revocar rol de ${user ? user.nombre : 'usuario'}?`);
      if (!ok) return;
      roleAssignments.delete(target);
      renderRows(currentRows);
      toast('Rol revocado', 'La asignación de rol fue eliminada correctamente.');
    }
  });

  $('btnAsignar').addEventListener('click', () => {
    clearError();
    if (currentUserRole !== 'Administrador') {
      showError('Solo el rol Administrador puede asignar o revocar roles.');
      return;
    }
    if (!selectedSamId) {
      showError('Selecciona un usuario de la tabla para asignar rol.');
      return;
    }
    if (!roleSelect.value) {
      showError('Selecciona un rol antes de asignar.');
      return;
    }
    if (!isSamUserActive(selectedSamId)) {
      showError('SAM indica que el usuario no está activo. No se puede asignar rol.');
      return;
    }
    roleAssignments.set(selectedSamId, roleSelect.value);
    renderRows(currentRows);
    toast('Rol asignado', 'Asignación confirmada. Permisos aplicados correctamente.');
  });

  renderRows([]);
});
</script>
@endsection
