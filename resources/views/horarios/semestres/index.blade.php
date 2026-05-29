{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Gestión de Semestres - Módulo de Horarios (Polished View)
 * @autor          Antigravity AI
 * @autorizador    Rubén Alejandro Nolasco Ruiz
 * @version        2.0.0
 * @creado         2026-05-25
 * @modificado     2026-05-25
 * @cambios        2026-05-25 - Eliminación de borrado manual, badges de estado robustas, restricción estricta por rol, banners de excepción y calendario pulido.
 */
--}}

@extends('layouts.app')

@section('title', 'Gestión de Semestres')

@push('styles')
<style>
/* Contenido principal */
.sem-main {
    margin-left: var(--sidebar-width, 240px);
    min-height: 100vh;
    background: var(--ice-blue);
    display: flex;
    flex-direction: column;
    transition: margin-left 0.25s ease;
}

.sem-body {
    flex: 1;
    padding: 28px 32px;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* Encabezado de página */
.sem-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 12px;
}

.sem-breadcrumb {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--soft-steel);
    margin-bottom: 4px;
}

.sem-breadcrumb .current {
    color: var(--deep-blue);
    font-weight: 600;
}

.sem-page-title {
    font-size: 26px;
    font-weight: 700;
    color: var(--midnight);
    margin-bottom: 4px;
}

.sem-page-subtitle {
    font-size: 14px;
    color: var(--soft-steel);
}

.btn-nuevo-sem {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 12px 22px;
    background: var(--deep-blue);
    color: white;
    border: none;
    border-radius: var(--radius-md);
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    font-family: var(--font-main);
    transition: background 0.2s, transform 0.2s;
    height: 42px;
    white-space: nowrap;
    flex-shrink: 0;
}

.btn-nuevo-sem:hover {
    background: var(--corp-orange);
    transform: translateY(-2px);
}

/* Tabla */
.sem-card {
    background: white;
    border: 1px solid var(--mist-blue);
    border-radius: var(--radius-lg);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.sem-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 22px;
    border-bottom: 1px solid var(--mist-blue);
    flex-wrap: wrap;
    gap: 10px;
}

.sem-toolbar-left {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.sem-search {
    position: relative;
}

.sem-search i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--soft-steel);
    font-size: 13px;
    pointer-events: none;
}

.sem-search input {
    padding: 9px 14px 9px 36px;
    background: var(--ice-blue);
    border: 1px solid var(--mist-blue);
    border-radius: var(--radius-md);
    font-family: var(--font-main);
    font-size: 13px;
    color: var(--dark-graphite);
    width: 210px;
    outline: none;
    transition: border-color 0.18s, box-shadow 0.18s;
}

.sem-search input:focus {
    border-color: var(--corp-orange);
    box-shadow: 0 0 0 3px rgba(242, 139, 44, 0.12);
}

.sem-count {
    font-size: 13px;
    color: var(--soft-steel);
}

.sem-table-wrap {
    overflow-x: auto;
    flex: 1;
}

.sem-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.sem-table thead th {
    padding: 13px 16px;
    font-weight: 600;
    font-size: 13px;
    background: var(--deep-blue);
    color: white;
    white-space: nowrap;
    text-align: left;
    border-right: 1px solid rgba(255, 255, 255, 0.1);
}

.sem-table thead th:last-child { border-right: none; }
.sem-table thead th.tc { text-align: center; }

.sem-table tbody tr {
    transition: background 0.15s;
}

.sem-table tbody tr:nth-child(even) { background: var(--ice-blue); }
.sem-table tbody tr:nth-child(odd)  { background: var(--light-blue); }
.sem-table tbody tr:hover           { background: var(--light-orange); }

.sem-table tbody td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--mist-blue);
    vertical-align: middle;
    color: var(--dark-graphite);
}

.sem-table tbody td.tc { text-align: center; }

.td-id    { color: var(--soft-steel); font-weight: 500; text-align: center; }
.td-name  { color: var(--midnight);   font-weight: 600; }
.td-hint  { font-size: 11px; color: var(--soft-steel); margin-top: 2px; font-weight: 400; }
.td-empty { color: var(--soft-steel); font-style: italic; }

/* Badges de estatus */
.st-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.st-activo   { background: rgba(90,154,90,0.14);  color: var(--status-active);   }
.st-futuro   { background: rgba(30,144,255,0.12);  color: #1e90ff;               }
.st-caducado { background: rgba(242,139,44,0.16);  color: var(--deep-orange);    }

/* Botones de acciones */
.sem-actions { display: flex; gap: 6px; align-items: center; }

.btn-editar {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    background: var(--royal-blue);
    color: white;
    border: none;
    border-radius: var(--radius-sm);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    font-family: var(--font-main);
    transition: background 0.18s;
    white-space: nowrap;
    min-height: 34px;
}

.btn-editar:hover { background: var(--corp-orange); }

/* Fila vacía */
.sem-empty td {
    padding: 48px;
    text-align: center;
    color: var(--soft-steel);
}

.sem-empty i {
    font-size: 30px;
    opacity: 0.3;
    display: block;
    margin-bottom: 10px;
}

/* Paginación */
.sem-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 22px;
    border-top: 1px solid var(--mist-blue);
    flex-wrap: wrap;
    gap: 8px;
}

.sem-pag-info { font-size: 12px; color: var(--soft-steel); }

.sem-pag-btns { display: flex; gap: 6px; align-items: center; }

.pag-btn {
    width: 34px;
    height: 34px;
    border: 1px solid var(--mist-blue);
    background: white;
    color: var(--dark-graphite);
    border-radius: var(--radius-sm);
    cursor: pointer;
    font-size: 13px;
    font-family: var(--font-main);
    transition: all 0.18s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.pag-btn:hover:not([disabled]):not(.pag-active) {
    border-color: var(--deep-blue);
    color: var(--deep-blue);
}

.pag-btn.pag-active {
    background: var(--deep-blue);
    border-color: var(--deep-blue);
    color: white;
    font-weight: 600;
}

.pag-btn[disabled] {
    background: var(--ice-blue);
    color: var(--soft-steel);
    cursor: not-allowed;
    opacity: 0.5;
}

/* Panel lateral */
.panel-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(19, 68, 116, 0.3);
    z-index: 1499;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.25s, visibility 0.25s;
}

.panel-backdrop.active {
    opacity: 1;
    visibility: visible;
}

.side-panel {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: 420px;
    background: white;
    border-left: 1px solid var(--mist-blue);
    z-index: 1500;
    display: flex;
    flex-direction: column;
    transform: translateX(100%);
    transition: transform 0.25s ease;
    box-shadow: -4px 0 24px rgba(19, 68, 116, 0.12);
}

.side-panel.open { transform: translateX(0); }

.panel-header {
    padding: 20px 22px 16px;
    border-bottom: 1px solid var(--mist-blue);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-shrink: 0;
}

.panel-title    { font-size: 16px; font-weight: 600; color: var(--midnight); margin-bottom: 3px; }
.panel-subtitle { font-size: 12px; color: var(--soft-steel); }

.panel-close-btn {
    background: none;
    border: none;
    color: var(--soft-steel);
    cursor: pointer;
    font-size: 16px;
    padding: 4px 6px;
    border-radius: var(--radius-sm);
    line-height: 1;
    transition: background 0.18s, color 0.18s;
}

.panel-close-btn:hover {
    background: var(--light-orange);
    color: var(--corp-orange);
}

.panel-body {
    flex: 1;
    overflow-y: auto;
    padding: 22px;
}

.form-field { margin-bottom: 18px; }

.form-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--midnight);
    margin-bottom: 6px;
}

.form-label .req { color: var(--status-inactive); margin-left: 3px; }

.form-input,
.form-select {
    width: 100%;
    padding: 11px 14px;
    background: var(--ice-blue);
    border: 1px solid var(--mist-blue);
    border-radius: var(--radius-md);
    font-family: var(--font-main);
    font-size: 14px;
    color: var(--midnight);
    outline: none;
    transition: border-color 0.18s, box-shadow 0.18s;
    box-sizing: border-box;
}

.form-input:focus,
.form-select:focus {
    border-color: var(--corp-orange);
    box-shadow: 0 0 0 3px rgba(242, 139, 44, 0.15);
}

.form-input.has-error,
.form-select.has-error {
    border-color: var(--status-inactive);
    box-shadow: 0 0 0 3px rgba(194, 120, 120, 0.12);
}

.form-error {
    margin-top: 5px;
    font-size: 12px;
    color: var(--status-inactive);
    display: flex;
    align-items: center;
    gap: 5px;
}

.form-error.hidden { display: none; }

.panel-footer {
    padding: 14px 22px 20px;
    border-top: 1px solid var(--mist-blue);
    display: flex;
    gap: 10px;
    flex-shrink: 0;
}

.btn-cancel-form {
    flex: 1;
    padding: 10px;
    background: transparent;
    border: 2px solid var(--deep-blue);
    color: var(--deep-blue);
    border-radius: var(--radius-md);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    font-family: var(--font-main);
    transition: border-color 0.18s, color 0.18s;
}

.btn-cancel-form:hover {
    border-color: var(--corp-orange);
    color: var(--corp-orange);
}

.btn-save-form {
    flex: 1;
    padding: 10px;
    background: var(--deep-blue);
    color: white;
    border: none;
    border-radius: var(--radius-md);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    font-family: var(--font-main);
    transition: background 0.18s, transform 0.18s;
}

.btn-save-form:hover {
    background: var(--corp-orange);
    transform: translateY(-1px);
}

/* Calendario UI */
.cal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.cal-month { font-weight: 700; color: var(--midnight); }
.cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; }
.cal-wd { text-align: center; font-size: 11px; color: var(--soft-steel); font-weight: 600; padding: 4px 0; }
.cal-day {
  height: 34px; border: 1px solid var(--mist-blue); border-radius: 8px; background: #fff; cursor: pointer;
  font-size: 12px; color: var(--dark-graphite); display: flex; align-items: center; justify-content: center;
  outline: none; transition: all 0.18s;
}
.cal-day:hover { background: var(--light-orange); border-color: var(--corp-orange); }
.cal-day.muted { opacity: 0.45; cursor: default; background: var(--ice-blue); }
.cal-day.disabled { opacity: 0.45; cursor: not-allowed; background: #f3f5f7; }
.cal-day.start, .cal-day.end { background: var(--deep-blue); color: #fff; border-color: var(--deep-blue); font-weight: 700; }
.cal-day.in-range { background: rgba(19,68,116,0.1); border-color: rgba(19,68,116,0.2); }

.loader-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px;
    color: var(--soft-steel);
    gap: 10px;
}

.spinner {
    display: inline-block;
    width: 24px;
    height: 24px;
    border: 3px solid var(--mist-blue);
    border-top-color: var(--deep-blue);
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* Responsive */
@media (max-width: 1024px) {
    .sem-main { margin-left: 0; }
}

@media (max-width: 640px) {
    .sem-body { padding: 16px; }
    .sem-page-header { flex-direction: column; align-items: stretch; }
    .side-panel { width: 100%; }
}
</style>
@endpush

@php
    $esAdmin = Auth::user()?->role?->value === 'admin';
    $haySemestreVigente = \App\Models\Semester::current()->exists();
    $haySemestresCaducados = \App\Models\Semester::whereDate('end_date', '<', now())->exists();
@endphp

@section('content')
<div class="sem-main">
    <div class="sem-body">
        {{-- Encabezado --}}
        <div class="sem-page-header">
            <div>
                <nav class="sem-breadcrumb">
                    <a href="{{ route('dashboard') }}" style="color:inherit;text-decoration:none;">Inicio</a>
                    <i class="fas fa-chevron-right" style="font-size:10px;"></i>
                    <span>Gestión Académica</span>
                    <i class="fas fa-chevron-right" style="font-size:10px;"></i>
                    <span class="current">Gestión de Semestres</span>
                </nav>
                <h1 class="sem-page-title">Gestión de Semestres</h1>
                <p class="sem-page-subtitle">Registrar, consultar, editar y dar seguimiento a los semestres escolares</p>
            </div>
            @if ($esAdmin)
                <button class="btn-nuevo-sem" id="btnNuevo">
                    <i class="fas fa-plus"></i>
                    Nuevo Semestre
                </button>
            @endif
        </div>

        {{-- Banners de Excepciones y Avisos de Negocio --}}
        @if (!$haySemestreVigente)
            <div class="note error" style="margin: 0; padding: 14px 18px; border-left: 4px solid #B00000; background: rgba(255,0,0,0.08); border-radius: var(--radius-md); font-size: 13px;">
                <div style="display: flex; align-items: start;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 16px; margin-right: 10px; margin-top: 2px; color: #B00000;"></i>
                    <div>
                        <strong style="font-size: 14px; display: block; margin-bottom: 3px; color: #B00000;">No existe semestre vigente</strong>
                        <span>El registro y modificación de horarios está deshabilitado temporalmente hasta que se registre o configure un semestre vigente en el sistema.</span>
                    </div>
                </div>
            </div>
        @endif

        @if ($haySemestresCaducados)
            <div class="note warning" style="margin: 0; padding: 14px 18px; border-left: 4px solid var(--gama-naranja); background: rgba(242,139,44,0.08); border-radius: var(--radius-md); font-size: 13px;">
                <div style="display: flex; align-items: start;">
                    <i class="fas fa-info-circle" style="font-size: 16px; margin-right: 10px; margin-top: 2px; color: var(--gama-naranja);"></i>
                    <div>
                        <strong style="font-size: 14px; display: block; margin-bottom: 3px; color: var(--gama-naranja);">Semestres caducados detectados</strong>
                        <span>Existen semestres caducados en el sistema que serán eliminados de forma automática por el scheduled task diario junto con todos sus horarios asociados.</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Tabla --}}
        <div class="sem-card">
            {{-- Barra de herramientas --}}
            <div class="sem-toolbar">
                <div class="sem-toolbar-left">
                    <div class="sem-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Buscar semestres" autocomplete="off">
                    </div>
                </div>
                <span class="sem-count" id="resultsCount">0 semestres</span>
            </div>

            {{-- Tabla --}}
            <div class="sem-table-wrap">
                <table class="sem-table">
                    <thead>
                        <tr>
                            <th class="tc" style="width:50px;">#</th>
                            <th>Semestre</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Estado</th>
                            @if ($esAdmin)
                                <th style="width:120px;">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr>
                            <td colspan="{{ $esAdmin ? 6 : 5 }}" class="sem-empty">
                                <div class="loader-container">
                                    <div class="spinner"></div>
                                    <span>Cargando semestres...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="sem-pagination">
                <span class="sem-pag-info" id="paginationInfo"></span>
                <div class="sem-pag-btns" id="paginationBtns"></div>
            </div>
        </div>
    </div>
</div>

@if ($esAdmin)
{{-- Panel lateral — Nuevo / Editar --}}
<div class="panel-backdrop" id="panelBackdrop"></div>

<aside class="side-panel" id="sidePanel" aria-modal="true" role="dialog">
    <div class="panel-header">
        <div>
            <div class="panel-title" id="panelTitle">Nuevo Semestre</div>
            <div class="panel-subtitle" id="panelSubtitle">Completa los datos del nuevo semestre</div>
        </div>
        <button class="panel-close-btn" id="btnClosePanel" aria-label="Cerrar panel">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="panel-body">
        {{-- Institución --}}
        <div class="form-field">
            <label class="form-label" for="fieldInstitution">
                Institución
            </label>
            <select class="form-select" id="fieldInstitution">
                <option value="">-- Sin institución --</option>
            </select>
            <div class="form-error hidden" id="errorInstitution">
                <i class="fas fa-exclamation-circle"></i>
                <span id="errorInstitutionMsg"></span>
            </div>
        </div>

        {{-- Nombre --}}
        <div class="form-field">
            <label class="form-label" for="fieldNombre">
                Nombre del semestre <span class="req">*</span>
            </label>
            <input type="text" class="form-input" id="fieldNombre" placeholder="Ej. 2026-I">
            <div class="form-error hidden" id="errorNombre">
                <i class="fas fa-exclamation-circle"></i>
                <span id="errorNombreMsg"></span>
            </div>
        </div>

        {{-- Fechas hidden inputs --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom: 14px;">
            <div class="form-field" style="margin-bottom:0;">
                <label class="form-label" for="fieldInicio">Fecha Inicio <span class="req">*</span></label>
                <input type="date" class="form-input" id="fieldInicio">
                <div class="form-error hidden" id="errorInicio">
                    <i class="fas fa-exclamation-circle"></i>
                    <span id="errorInicioMsg"></span>
                </div>
            </div>
            <div class="form-field" style="margin-bottom:0;">
                <label class="form-label" for="fieldFin">Fecha Fin <span class="req">*</span></label>
                <input type="date" class="form-input" id="fieldFin">
                <div class="form-error hidden" id="errorFin">
                    <i class="fas fa-exclamation-circle"></i>
                    <span id="errorFinMsg"></span>
                </div>
            </div>
        </div>

        {{-- Calendario Interactivo --}}
        <div class="form-field">
            <label class="form-label">Asignar fechas con calendario</label>
            <div class="cal-container" style="border: 1px solid var(--mist-blue); border-radius: var(--radius-md); padding: 12px; background: #fff;">
                <div class="cal-header">
                    <button type="button" class="btn btn-outline btn-sm" id="btnPrevMonth" style="padding: 2px 8px; font-size: 11px;"><i class="fas fa-chevron-left"></i></button>
                    <span class="cal-month" id="calMonthLabel"></span>
                    <button type="button" class="btn btn-outline btn-sm" id="btnNextMonth" style="padding: 2px 8px; font-size: 11px;"><i class="fas fa-chevron-right"></i></button>
                </div>
                <div class="cal-grid" id="calWeekDays"></div>
                <div class="cal-grid" id="calDays" style="margin-top: 8px;"></div>
            </div>
        </div>
    </div>

    <div class="panel-footer">
        <button class="btn-cancel-form" id="btnCancelPanel">Cancelar</button>
        <button class="btn-save-form" id="btnSavePanel">
            <i class="fas fa-save" style="margin-right:7px;"></i>Guardar
        </button>
    </div>
</aside>
@endif

<div class="toast-container" id="toastContainer"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const TOKEN = localStorage.getItem('auth_token');
    if (!TOKEN) { window.location.href = '/'; return; }

    const $ = (id) => document.getElementById(id);

    // Permisos del usuario actual
    const ES_ADMIN = {{ $esAdmin ? 'true' : 'false' }};

    // Estado global de la vista
    let semestres = [];
    let instituciones = [];
    let filteredSemestres = [];
    let currentEditId = null;

    // Estado de Paginación
    let currentPage = 1;
    const itemsPerPage = 8;

    // Estado del Calendario Interactivo
    const today = new Date(); today.setHours(0,0,0,0);
    let viewYear = today.getFullYear();
    let viewMonth = today.getMonth();
    let selectedStart = null;
    let selectedEnd = null;

    const DIAS = ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'];

    function apiHeaders() {
        return {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + TOKEN
        };
    }

    async function apiRequest(url, method = 'GET', body = null) {
        const options = {
            method,
            headers: apiHeaders()
        };
        if (body) {
            options.body = JSON.stringify(body);
        }
        const res = await fetch(url, options);
        if (res.status === 401) {
            localStorage.clear();
            window.location.href = '/';
            throw new Error('No autorizado');
        }
        if (res.status === 204) return null;
        const data = await res.json();
        if (!res.ok) throw data;
        return data;
    }

    function showToast(title, message, type = 'success') {
        const wrap = $('toastContainer');
        const icon = type === 'success' ? 'check' : 'times';
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-icon"><i class="fas fa-${icon}"></i></div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close"><i class="fas fa-times"></i></button>`;
        wrap.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 4000);
        toast.querySelector('.toast-close').addEventListener('click', () => { toast.remove(); });
    }

    // Inicialización del Calendario (solo si es administrador)
    if (ES_ADMIN) {
        $('calWeekDays').innerHTML = DIAS.map(w => `<div class="cal-wd">${w}</div>`).join('');
    }

    function toIso(d) {
        if (!d) return '';
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + day;
    }

    function parseIso(str) {
        if (!str) return null;
        const d = new Date(str + 'T00:00:00');
        d.setHours(0,0,0,0);
        return d;
    }

    function sameDate(a, b) { return a && b && a.getTime() === b.getTime(); }
    function inRange(d, a, b) { return a && b && d >= a && d <= b; }

    function renderCalendar() {
        if (!ES_ADMIN) return;
        
        const monthDate = new Date(viewYear, viewMonth, 1);
        const monthName = monthDate.toLocaleDateString('es-MX', { month: 'long', year: 'numeric' });
        $('calMonthLabel').textContent = monthName.charAt(0).toUpperCase() + monthName.slice(1);

        const firstDay = new Date(viewYear, viewMonth, 1);
        const lastDay = new Date(viewYear, viewMonth + 1, 0);
        const totalCells = Math.ceil((firstDay.getDay() + lastDay.getDate()) / 7) * 7;
        let html = '';

        for (let i = 0; i < totalCells; i++) {
            const dayNum = i - firstDay.getDay() + 1;
            const d = new Date(viewYear, viewMonth, dayNum);
            const isCurrent = d.getMonth() === viewMonth;
            const dateIso = toIso(d);
            const cls = [
                'cal-day',
                !isCurrent ? 'muted' : '',
                sameDate(d, selectedStart) ? 'start' : '',
                sameDate(d, selectedEnd) ? 'end' : '',
                inRange(d, selectedStart, selectedEnd) && !sameDate(d, selectedStart) && !sameDate(d, selectedEnd) ? 'in-range' : ''
            ].filter(Boolean).join(' ');
            html += `<button type="button" class="${cls}" data-date="${dateIso}">${d.getDate()}</button>`;
        }
        $('calDays').innerHTML = html;
    }

    function updateRangeFromCalendar(dateIso) {
        const d = parseIso(dateIso);
        if (!selectedStart || (selectedStart && selectedEnd)) {
            selectedStart = d; selectedEnd = null;
        } else if (d < selectedStart) {
            selectedEnd = selectedStart; selectedStart = d;
        } else {
            selectedEnd = d;
        }
        $('fieldInicio').value = selectedStart ? toIso(selectedStart) : '';
        $('fieldFin').value = selectedEnd ? toIso(selectedEnd) : '';
        
        // Validación client-side proactiva de fechas
        if (selectedStart && selectedEnd && selectedStart >= selectedEnd) {
            showValidationError('Fin', 'La fecha de fin debe ser posterior a la fecha de inicio.');
            $('btnSavePanel').disabled = true;
        } else {
            clearValidationErrors();
            $('btnSavePanel').disabled = false;
        }
        
        renderCalendar();
    }

    function refreshFromInputs() {
        selectedStart = $('fieldInicio').value ? parseIso($('fieldInicio').value) : null;
        selectedEnd = $('fieldFin').value ? parseIso($('fieldFin').value) : null;
        
        if (selectedStart && selectedEnd && selectedStart >= selectedEnd) {
            showValidationError('Fin', 'La fecha de fin debe ser posterior a la fecha de inicio.');
            $('btnSavePanel').disabled = true;
        } else {
            clearValidationErrors();
            $('btnSavePanel').disabled = false;
        }

        if (selectedStart) {
            viewYear = selectedStart.getFullYear();
            viewMonth = selectedStart.getMonth();
        }
        renderCalendar();
    }

    if (ES_ADMIN) {
        $('btnPrevMonth').addEventListener('click', (e) => {
            e.preventDefault();
            viewMonth--;
            if (viewMonth < 0) { viewMonth = 11; viewYear--; }
            renderCalendar();
        });
        $('btnNextMonth').addEventListener('click', (e) => {
            e.preventDefault();
            viewMonth++;
            if (viewMonth > 11) { viewMonth = 0; viewYear++; }
            renderCalendar();
        });
        $('calDays').addEventListener('click', (e) => {
            e.preventDefault();
            const btn = e.target.closest('[data-date]');
            if (!btn) return;
            updateRangeFromCalendar(btn.dataset.date);
        });

        $('fieldInicio').addEventListener('change', refreshFromInputs);
        $('fieldFin').addEventListener('change', refreshFromInputs);
    }

    // Cargar Catálogos e inicializar tabla
    async function init() {
        try {
            if (ES_ADMIN) {
                // Cargar instituciones
                const instResp = await apiRequest('/api/v1/institutions');
                instituciones = instResp.data || [];
                
                const selectInst = $('fieldInstitution');
                selectInst.innerHTML = '<option value="">-- Sin institución --</option>';
                instituciones.forEach(inst => {
                    if (inst.isActive) {
                        selectInst.innerHTML += `<option value="${inst.id}">${inst.name}</option>`;
                    }
                });

                if (instituciones.length === 1) {
                    selectInst.value = instituciones[0].id;
                }
            }

            await loadSemestres();
        } catch (e) {
            showToast('Error', 'No se pudieron cargar los catálogos.', 'error');
        }
    }

    async function loadSemestres() {
        try {
            const semResp = await apiRequest('/api/v1/semesters');
            semestres = semResp.data || [];
            filteredSemestres = [...semestres];
            renderTable();
        } catch (e) {
            showToast('Error', 'No se pudieron cargar los semestres.', 'error');
        }
    }

    function renderTable() {
        const body = $('tableBody');
        $('resultsCount').textContent = `${filteredSemestres.length} semestre(s)`;

        if (filteredSemestres.length === 0) {
            body.innerHTML = `<tr><td colspan="${ES_ADMIN ? 6 : 5}" class="sem-empty"><i class="fas fa-calendar-times"></i>No se encontraron semestres.</td></tr>`;
            $('paginationInfo').textContent = '';
            $('paginationBtns').innerHTML = '';
            return;
        }

        const totalPages = Math.ceil(filteredSemestres.length / itemsPerPage);
        if (currentPage > totalPages) currentPage = totalPages || 1;

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const pageItems = filteredSemestres.slice(startIndex, endIndex);

        body.innerHTML = pageItems.map((s, idx) => {
            const displayIdx = startIndex + idx + 1;
            const startDateFormatted = new Date(s.startDate).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', timeZone: 'UTC' });
            const endDateFormatted = new Date(s.endDate).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', timeZone: 'UTC' });

            // RFC-05: Estado Dinámico por Fecha
            const todayStr = toIso(new Date());
            let badgeClass = '';
            let badgeLabel = '';

            if (s.startDate <= todayStr && s.endDate >= todayStr) {
                badgeClass = 'st-activo';
                badgeLabel = 'Vigente';
            } else if (s.startDate > todayStr) {
                badgeClass = 'st-futuro';
                badgeLabel = 'Futuro';
            } else {
                badgeClass = 'st-caducado';
                badgeLabel = 'Caducado';
            }

            let actionsHtml = '';
            if (ES_ADMIN) {
                actionsHtml = `
                    <td>
                        <div class="sem-actions">
                            <button class="btn-editar" data-id="${s.id}"><i class="fas fa-edit"></i>Editar</button>
                        </div>
                    </td>
                `;
            }

            return `
                <tr>
                    <td class="tc td-id">${displayIdx}</td>
                    <td class="td-name">${s.name}</td>
                    <td>${startDateFormatted}</td>
                    <td>${endDateFormatted}</td>
                    <td class="tc"><span class="st-badge ${badgeClass}">${badgeLabel}</span></td>
                    ${actionsHtml}
                </tr>
            `;
        }).join('');

        // Render Paginación
        $('paginationInfo').textContent = `Mostrando del ${startIndex + 1} al ${Math.min(endIndex, filteredSemestres.length)} de ${filteredSemestres.length} registros`;
        
        let pagHtml = `
            <button class="pag-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})"><i class="fas fa-chevron-left"></i></button>
        `;
        for (let i = 1; i <= totalPages; i++) {
            pagHtml += `
                <button class="pag-btn ${currentPage === i ? 'pag-active' : ''}" onclick="changePage(${i})">${i}</button>
            `;
        }
        pagHtml += `
            <button class="pag-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="changePage(${currentPage + 1})"><i class="fas fa-chevron-right"></i></button>
        `;
        $('paginationBtns').innerHTML = pagHtml;
    }

    window.changePage = function (page) {
        currentPage = page;
        renderTable();
    };

    // Búsqueda
    $('searchInput').addEventListener('input', function (e) {
        const q = e.target.value.toLowerCase().trim();
        filteredSemestres = semestres.filter(s => s.name.toLowerCase().includes(q));
        currentPage = 1;
        renderTable();
    });

    if (ES_ADMIN) {
        // Panel Lateral
        function openPanel(title, subtitle, editId = null) {
            currentEditId = editId;
            $('panelTitle').textContent = title;
            $('panelSubtitle').textContent = subtitle;
            clearValidationErrors();

            if (editId) {
                const s = semestres.find(x => x.id === editId);
                if (s) {
                    $('fieldInstitution').value = s.institutionId ?? '';
                    $('fieldNombre').value = s.name;
                    $('fieldInicio').value = s.startDate;
                    $('fieldFin').value = s.endDate;

                    selectedStart = parseIso(s.startDate);
                    selectedEnd = parseIso(s.endDate);
                    if (selectedStart) {
                        viewYear = selectedStart.getFullYear();
                        viewMonth = selectedStart.getMonth();
                    }
                }
            } else {
                $('fieldInstitution').value = instituciones.length === 1 ? instituciones[0].id : '';
                $('fieldNombre').value = '';
                $('fieldInicio').value = '';
                $('fieldFin').value = '';
                selectedStart = null;
                selectedEnd = null;
                viewYear = today.getFullYear();
                viewMonth = today.getMonth();
            }

            renderCalendar();
            $('sidePanel').classList.add('open');
            $('panelBackdrop').classList.add('active');
        }

        function closePanel() {
            $('sidePanel').classList.remove('open');
            $('panelBackdrop').classList.remove('active');
            currentEditId = null;
        }

        function clearValidationErrors() {
            ['Institution', 'Nombre', 'Inicio', 'Fin'].forEach(field => {
                $(`field${field}`).classList.remove('has-error');
                $(`error${field}`).classList.add('hidden');
                $(`error${field}Msg`).textContent = '';
            });
        }

        function showValidationError(field, message) {
            $(`field${field}`).classList.add('has-error');
            $(`error${field}`).classList.remove('hidden');
            $(`error${field}Msg`).textContent = message;
        }

        $('btnNuevo').addEventListener('click', () => openPanel('Nuevo Semestre', 'Completa los datos del nuevo semestre'));
        $('btnClosePanel').addEventListener('click', closePanel);
        $('btnCancelPanel').addEventListener('click', closePanel);
        $('panelBackdrop').addEventListener('click', closePanel);

        // Guardar Semestre
        $('btnSavePanel').addEventListener('click', async () => {
            clearValidationErrors();
            let valid = true;

            const instId = $('fieldInstitution').value;
            const nombre = $('fieldNombre').value.trim();
            const inicio = $('fieldInicio').value;
            const fin = $('fieldFin').value;

            if (!nombre) { showValidationError('Nombre', 'El nombre del semestre es obligatorio.'); valid = false; }
            if (!inicio) { showValidationError('Inicio', 'La fecha de inicio es obligatoria.'); valid = false; }
            if (!fin) { showValidationError('Fin', 'La fecha de fin es obligatoria.'); valid = false; }

            if (inicio && fin && new Date(inicio) >= new Date(fin)) {
                showValidationError('Fin', 'La fecha de fin debe ser posterior a la fecha de inicio.');
                valid = false;
            }

            if (!valid) return;

            $('btnSavePanel').disabled = true;
            $('btnSavePanel').innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:7px;"></i>Guardando...';

            const payload = {
                institution_id: instId ? parseInt(instId, 10) : null,
                name: nombre,
                start_date: inicio,
                end_date: fin
            };

            try {
                if (currentEditId) {
                    await apiRequest(`/api/v1/semesters/${currentEditId}`, 'PUT', payload);
                    showToast('Éxito', 'Semestre actualizado correctamente.', 'success');
                } else {
                    await apiRequest('/api/v1/semesters', 'POST', payload);
                    showToast('Éxito', 'Semestre registrado correctamente.', 'success');
                }
                closePanel();
                await loadSemestres();
                
                // Recargar página para actualizar banners de negocio dinámicos
                setTimeout(() => window.location.reload(), 800);
            } catch (e) {
                // RF-05.1: Mostrar los mensajes exactos de solapamiento y fallos del servidor
                if (e.errors) {
                    if (e.errors.institution_id) showValidationError('Institution', e.errors.institution_id[0]);
                    if (e.errors.name) showValidationError('Nombre', e.errors.name[0]);
                    if (e.errors.start_date) showValidationError('Inicio', e.errors.start_date[0]);
                    if (e.errors.end_date) showValidationError('Fin', e.errors.end_date[0]);
                } else {
                    const errMsg = e.message || 'Ocurrió un error al guardar el semestre.';
                    showToast('Error', errMsg, 'error');
                    
                    if (errMsg === 'El período se solapa con un semestre vigente') {
                        showValidationError('Inicio', errMsg);
                        showValidationError('Fin', errMsg);
                    } else if (errMsg === 'No se pudo determinar el semestre activo') {
                        showToast('Fallo del Servidor', 'No se pudo determinar el semestre activo. Intente más tarde.', 'error');
                    }
                }
            } finally {
                $('btnSavePanel').disabled = false;
                $('btnSavePanel').innerHTML = '<i class="fas fa-save" style="margin-right:7px;"></i>Guardar';
            }
        });
    }

    // Delegación de eventos para botones de la tabla
    $('tableBody').addEventListener('click', (e) => {
        const btnEditar = e.target.closest('.btn-editar');

        if (btnEditar && ES_ADMIN) {
            const id = parseInt(btnEditar.dataset.id, 10);
            openPanel('Editar Semestre', 'Modifica los datos del semestre', id);
        }
    });

    init();
});
</script>
@endsection
