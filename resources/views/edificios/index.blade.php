{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Vista de Gestión de Edificios - PANT-03
 * @autor          Rubén Alejandro Nolasco Ruiz
 * @autorizador    Rubén Alejandro Nolasco Ruiz
 * @prueba         Diego Miguel Hernandez Fabela
 * @mantenimiento  Ghael Garcia Manjarrez
 * @version        2.0.0
 * @creado         11/04/2026
 * @modificado     23/04/2026
 *
 * @cambios
 * Fecha       | Autor             | Descripción
 * ------------|-------------------|------------------------------------------
 * 11/04/2026  | Rubén Alejandro   | Implementación inicial (React standalone).
 * 23/04/2026  | Rubén Alejandro   | Migración a Blade + Vanilla JS con layout unificado.
 */
--}}

@extends('layouts.app')

@section('title', 'Gestión de Edificios')

@push('styles')
<style>
/* ============================================================
   EDIFICIOS — Estilos específicos de la vista
   Depende de variables definidas en gama-dashboard.css
============================================================ */

/* â”€â”€ Contenido principal â”€â”€ */
.edif-main {
    margin-left: var(--sidebar-width, 240px);
    min-height: 100vh;
    background: var(--ice-blue);
    display: flex;
    flex-direction: column;
    transition: margin-left 0.25s ease;
}

.edif-body {
    flex: 1;
    padding: 28px 32px;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* â”€â”€ Encabezado de página â”€â”€ */
.edif-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 12px;
}

.edif-breadcrumb {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--soft-steel);
    margin-bottom: 4px;
}

.edif-breadcrumb .current {
    color: var(--deep-blue);
    font-weight: 600;
}

.edif-page-title {
    font-size: 26px;
    font-weight: 700;
    color: var(--midnight);
    margin-bottom: 4px;
}

.edif-page-subtitle {
    font-size: 14px;
    color: var(--soft-steel);
}

.btn-nuevo-edif {
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

.btn-nuevo-edif:hover {
    background: var(--corp-orange);
    transform: translateY(-2px);
}

/* â”€â”€ Tabla â”€â”€ */
.edif-card {
    background: white;
    border: 1px solid var(--mist-blue);
    border-radius: var(--radius-lg);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.edif-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 22px;
    border-bottom: 1px solid var(--mist-blue);
    flex-wrap: wrap;
    gap: 10px;
}

.edif-toolbar-left {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.edif-search {
    position: relative;
}

.edif-search i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--soft-steel);
    font-size: 13px;
    pointer-events: none;
}

.edif-search input {
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

.edif-search input:focus {
    border-color: var(--corp-orange);
    box-shadow: 0 0 0 3px rgba(242, 139, 44, 0.12);
}

.edif-select {
    padding: 9px 14px;
    background: var(--ice-blue);
    border: 1px solid var(--mist-blue);
    border-radius: var(--radius-md);
    font-family: var(--font-main);
    font-size: 13px;
    color: var(--dark-graphite);
    cursor: pointer;
    outline: none;
    transition: border-color 0.18s;
}

.edif-select:focus {
    border-color: var(--corp-orange);
}

.edif-count {
    font-size: 13px;
    color: var(--soft-steel);
}

.edif-table-wrap {
    overflow-x: auto;
    flex: 1;
}

.edif-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.edif-table thead th {
    padding: 13px 16px;
    font-weight: 600;
    font-size: 13px;
    background: var(--deep-blue);
    color: white;
    white-space: nowrap;
    text-align: left;
    border-right: 1px solid rgba(255, 255, 255, 0.1);
}

.edif-table thead th:last-child { border-right: none; }
.edif-table thead th.tc { text-align: center; }

.edif-table tbody tr {
    transition: background 0.15s;
}

.edif-table tbody tr:nth-child(even) { background: var(--ice-blue); }
.edif-table tbody tr:nth-child(odd)  { background: var(--light-blue); }
.edif-table tbody tr:hover           { background: var(--light-orange); }

.edif-table tbody td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--mist-blue);
    vertical-align: middle;
    color: var(--dark-graphite);
}

.edif-table tbody td.tc { text-align: center; }

.td-id    { color: var(--soft-steel); font-weight: 500; text-align: center; }
.td-name  { color: var(--midnight);   font-weight: 600; }
.td-hint  { font-size: 11px; color: var(--soft-steel); margin-top: 2px; font-weight: 400; }
.td-empty { color: var(--soft-steel); font-style: italic; }
.td-desc-cell {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 200px;
    display: block;
}

/* â”€â”€ Badges de estatus â”€â”€ */
.st-badge {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}

.st-activo   { background: rgba(90,154,90,0.13);  color: var(--status-active);   }
.st-inactivo { background: rgba(194,120,120,0.13); color: var(--status-inactive); }

/* â”€â”€ Botones de acciones â”€â”€ */
.edif-actions { display: flex; gap: 6px; align-items: center; }

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

.btn-eliminar-red {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    background: transparent;
    color: var(--status-inactive);
    border: 1px solid rgba(194, 120, 120, 0.4);
    border-radius: var(--radius-sm);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    font-family: var(--font-main);
    transition: all 0.18s;
    white-space: nowrap;
    min-height: 34px;
}

.btn-eliminar-red:hover:not([disabled]) {
    color: white;
    border-color: var(--status-inactive);
    background: var(--status-inactive);
}

.btn-eliminar-red[disabled] {
    opacity: 0.45;
    cursor: not-allowed;
}

.btn-spinner {
    width: 38px;
    height: 38px;
    border: 1px solid var(--mist-blue);
    background: var(--ice-blue);
    border-radius: var(--radius-md);
    font-weight: bold;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    transition: background 0.15s, border-color 0.15s;
    color: var(--midnight);
    outline: none;
}

.btn-spinner:hover:not([disabled]) {
    background: var(--light-orange);
    border-color: var(--corp-orange);
    color: var(--corp-orange);
}

.btn-spinner:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* â”€â”€ Fila vacía â”€â”€ */
.edif-empty td {
    padding: 48px;
    text-align: center;
    color: var(--soft-steel);
}

.edif-empty i {
    font-size: 30px;
    opacity: 0.3;
    display: block;
    margin-bottom: 10px;
}

/* â”€â”€ Paginación â”€â”€ */
.edif-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 22px;
    border-top: 1px solid var(--mist-blue);
    flex-wrap: wrap;
    gap: 8px;
}

.edif-pag-info { font-size: 12px; color: var(--soft-steel); }

.edif-pag-btns { display: flex; gap: 6px; align-items: center; }

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

/* â”€â”€ Panel lateral â”€â”€ */
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
    width: 360px;
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
.form-textarea {
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
.form-textarea:focus {
    border-color: var(--corp-orange);
    box-shadow: 0 0 0 3px rgba(242, 139, 44, 0.15);
}

.form-input.has-error,
.form-textarea.has-error {
    border-color: var(--status-inactive);
    box-shadow: 0 0 0 3px rgba(194, 120, 120, 0.12);
}

.form-textarea { resize: vertical; min-height: 90px; line-height: 1.5; }

.form-hint  { font-size: 11px; color: var(--soft-steel); margin-top: 4px; text-align: right; }

.form-error {
    margin-top: 5px;
    font-size: 12px;
    color: var(--status-inactive);
    display: flex;
    align-items: center;
    gap: 5px;
}

.form-error.hidden { display: none; }

.form-static {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: var(--ice-blue);
    border-radius: var(--radius-md);
    border: 1px solid var(--mist-blue);
}

.form-static-hint { font-size: 12px; color: var(--soft-steel); }

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

/* â”€â”€ Modal de confirmación (inactivar) â”€â”€ */
.modal-warn-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 20px;
    background: rgba(242, 139, 44, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-warn-icon i {
    font-size: 28px;
    color: var(--corp-orange);
}

.btn-confirm-eliminar {
    padding: 10px 20px;
    background: var(--status-inactive);
    color: white;
    border: none;
    border-radius: var(--radius-md);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    font-family: var(--font-main);
    transition: background 0.18s;
}

.btn-confirm-eliminar:hover { background: #a82e2e; }

/* â”€â”€ Responsive â”€â”€ */
@media (max-width: 1024px) {
    .edif-main { margin-left: 0; }
}

@media (max-width: 640px) {
    .edif-body { padding: 16px; }
    .edif-page-header { flex-direction: column; align-items: stretch; }
    .side-panel { width: 100%; }
}
</style>
@endpush

@section('content')

<div class="edif-main">
    <div class="edif-body">

        {{-- â”€â”€ Encabezado â”€â”€ --}}
        <div class="edif-page-header">
            <div>
                <nav class="edif-breadcrumb">
                    <a href="{{ route('dashboard') }}" style="color:inherit;text-decoration:none;">Inicio</a>
                    <i class="fas fa-chevron-right" style="font-size:10px;"></i>
                    <span>Gestión Académica</span>
                    <i class="fas fa-chevron-right" style="font-size:10px;"></i>
                    <span class="current">Gestión de Edificios</span>
                </nav>
                <h1 class="edif-page-title">Gestión de Edificios</h1>
                <p class="edif-page-subtitle">Registrar, consultar, editar y eliminar edificios de la institución</p>
            </div>
            <button class="btn-nuevo-edif" id="btnNuevo">
                <i class="fas fa-plus"></i>
                Nuevo Edificio
            </button>
        </div>

        {{-- â”€â”€ Tabla â”€â”€ --}}
        <div class="edif-card">

            {{-- Barra de herramientas --}}
            <div class="edif-toolbar">
                <div class="edif-toolbar-left">
                    <div class="edif-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Buscar edificios" autocomplete="off">
                    </div>
                </div>
                <span class="edif-count" id="resultsCount"></span>
            </div>

            {{-- Tabla --}}
            <div class="edif-table-wrap">
                <table class="edif-table">
                    <thead>
                        <tr>
                            <th class="tc" style="width:50px;">#</th>
                            <th>Nombre del Edificio</th>
                            <th class="tc" style="width:80px;">Niveles</th>
                            <th>Descripción / Referencia</th>
                            <th style="width:180px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="edif-pagination">
                <span class="edif-pag-info" id="paginationInfo"></span>
                <div class="edif-pag-btns" id="paginationBtns"></div>
            </div>
        </div>

    </div>{{-- /edif-body --}}
</div>{{-- /edif-main --}}

{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     Panel lateral — Nuevo / Editar
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
<div class="panel-backdrop" id="panelBackdrop"></div>

<aside class="side-panel" id="sidePanel" aria-modal="true" role="dialog">
    <div class="panel-header">
        <div>
            <div class="panel-title"   id="panelTitle">Nuevo Edificio</div>
            <div class="panel-subtitle" id="panelSubtitle">Completa los datos del nuevo edificio</div>
        </div>
        <button class="panel-close-btn" id="btnClosePanel" aria-label="Cerrar panel">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="panel-body">
        {{-- Nombre --}}
        <div class="form-field">
            <label class="form-label" for="fieldNombre">
                Nombre del edificio <span class="req">*</span>
            </label>
            <input type="text" class="form-input" id="fieldNombre" maxlength="80"
                   placeholder="Ej.Edificio T">
            <div style="display: flex; justify-content: space-between; margin-top: 4px;">
                <span style="font-size: 11px; color: var(--soft-steel);">Solo letras, números</span>
                <div class="form-hint" style="margin-top: 0;"><span id="nombreCount">0</span>/80</div>
            </div>
            <div class="form-error hidden" id="errorNombre">
                <i class="fas fa-exclamation-circle"></i>
                <span id="errorNombreMsg"></span>
            </div>
        </div>

        {{-- Niveles --}}
        <div class="form-field">
            <label class="form-label" for="fieldNiveles">
                Número de niveles <span class="req">*</span>
            </label>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" class="btn-spinner" id="btnNivelesDec">—</button>
                <input type="text" class="form-input" id="fieldNiveles" min="1" max="5" step="1" placeholder="Ej. 4" style="text-align: center; flex: 1;">
                <button type="button" class="btn-spinner" id="btnNivelesInc">+</button>
            </div>
            <div class="form-error hidden" id="errorNiveles">
                <i class="fas fa-exclamation-circle"></i>
                <span id="errorNivelesMsg"></span>
            </div>
        </div>



        {{-- Descripción --}}
        <div class="form-field">
            <label class="form-label" for="fieldDesc">Descripción / Referencia</label>
            <textarea class="form-textarea" id="fieldDesc" maxlength="200" rows="4"
                      placeholder="Referencia interna opcional…"></textarea>
            <div style="display: flex; justify-content: space-between; margin-top: 4px;">
                <span style="font-size: 11px; color: var(--soft-steel);">Solo letras.</span>
                <div class="form-hint" style="margin-top: 0;"><span id="descCount">0</span>/200</div>
            </div>
            <div class="form-error hidden" id="errorDesc">
                <i class="fas fa-exclamation-circle"></i>
                <span id="errorDescMsg"></span>
            </div>
        </div>
    </div>

    <div class="panel-footer">
        <button class="btn-cancel-form" id="btnCancelPanel">Cancelar</button>
        <button class="btn-save-form"   id="btnSavePanel">
            <i class="fas fa-save" style="margin-right:7px;"></i>Guardar
        </button>
    </div>
</aside>

{{-- ──────────────────────────────────────────────────────────────────
     Modal — Confirmar eliminar
────────────────────────────────────────────────────────────────── --}}
<div class="modal-overlay" id="eliminarModal" role="dialog" aria-modal="true">
    <div class="modal">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Eliminar Edificio</h3>
                <p style="font-size:13px;color:var(--soft-steel);margin-top:3px;">
                    Esta acción eliminará el registro de forma permanente
                </p>
            </div>
            <button class="modal-close" id="btnCloseEliminar" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-warn-icon" style="background: rgba(194, 120, 120, 0.1);">
                <i class="fas fa-exclamation-triangle" style="color: var(--status-inactive);"></i>
            </div>
            <p class="modal-text" id="eliminarText"></p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="btnCancelEliminar">Cancelar</button>
            <button class="btn-confirm-eliminar" id="btnConfirmEliminar">
                <i class="fas fa-trash-alt" style="margin-right:7px;"></i>Eliminar
            </button>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="toast-container" id="toastContainer"></div>

{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     JavaScript — Lógica de la vista
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ─── Estado ─── */
    const PER_PAGE  = 10;
    let buildings   = [];
    let page        = 1;
    let search      = '';
    let panelRecord   = null;
    let eliminarTarget = null;
    let isLoading   = false;

    /* ─── Refs DOM ─── */
    const $ = id => document.getElementById(id);
    const searchInput     = $('searchInput');
    const resultsCount    = $('resultsCount');
    const tableBody       = $('tableBody');
    const paginationInfo  = $('paginationInfo');
    const paginationBtns  = $('paginationBtns');
    const panelBackdrop   = $('panelBackdrop');
    const sidePanel       = $('sidePanel');
    const fieldNombre     = $('fieldNombre');
    const fieldNiveles    = $('fieldNiveles');
    const fieldDesc       = $('fieldDesc');
    const btnNivelesDec   = $('btnNivelesDec');
    const btnNivelesInc   = $('btnNivelesInc');
    const eliminarModal   = $('eliminarModal');
    const toastContainer  = $('toastContainer');

    /* ─── Utilidades ─── */
    function esc(str) {
        if (!str) return '';
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ─── CSRF ─── */
    function getCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    }

    /* ─── API helpers ─── */
    async function apiFetch(url, options = {}) {
        const token = localStorage.getItem('auth_token');
        const res = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrf(),
                ...(token ? { 'Authorization': 'Bearer ' + token } : {}),
                ...options.headers,
            },
            ...options,
        });
        const json = await res.json();
        if (!res.ok) throw { status: res.status, json };
        return json;
    }

    /* ─── Carga de datos desde la API ─── */
    async function loadBuildings() {
        tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--soft-steel);"><i class="fas fa-spinner fa-spin" style="font-size:24px;"></i></td></tr>';
        try {
            const json = await apiFetch('/api/v1/buildings');
            buildings = (json.data ?? []).map(b => {
                // Generar la nomenclatura de niveles desde b.levels si existe, de lo contrario calcularla dinámicamente
                let nivelNomenclaturas = "";
                if (Array.isArray(b.levels) && b.levels.length > 0) {
                    nivelNomenclaturas = b.levels.map(l => l.name).join(', ');
                } else {
                    const count = parseInt(b.levelCount) || 0;
                    const arr = [];
                    for (let i = 0; i < count; i++) {
                        arr.push(i === 0 ? 'PB' : 'P' + i);
                    }
                    nivelNomenclaturas = arr.join(', ');
                }

                return {
                    id:          b.id,
                    nombre:      b.name,
                    niveles:     nivelNomenclaturas,
                    levelCount:  b.levelCount,
                    descripcion: b.description ?? '',
                    estatus:     b.isActive ? 'Activo' : 'Inactivo',
                    isActive:    b.isActive,
                };
            });
            render();
        } catch (e) {
            tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--status-inactive);">Error al cargar edificios. Verifica la conexión con la API.</td></tr>';
            showToast('Error', 'No se pudieron cargar los edificios.', 'error');
        }
    }

    /* ─── Filtrado ─── */
    function getFiltered() {
        const q = search.toLowerCase();
        return buildings.filter(b => {
            return !q || b.nombre.toLowerCase().includes(q) || (b.descripcion || '').toLowerCase().includes(q);
        });
    }

    /* ─── Render principal ─── */
    function render() {
        const filtered   = getFiltered();
        const totalPages = Math.max(1, Math.ceil(filtered.length / PER_PAGE));
        if (page > totalPages) page = totalPages;
        const pageData = filtered.slice((page - 1) * PER_PAGE, page * PER_PAGE);

        const n = filtered.length;
        resultsCount.textContent = `${n} edificio${n !== 1 ? 's' : ''} encontrado${n !== 1 ? 's' : ''}`;

        if (pageData.length === 0) {
            tableBody.innerHTML = `
                <tr class="edif-empty">
                    <td colspan="5">
                        <i class="fas fa-building"></i>
                        No se encontraron edificios con los filtros aplicados.
                    </td>
                </tr>`;
        } else {
            tableBody.innerHTML = pageData.map(r => `
                <tr>
                    <td class="td-id">${r.id}</td>
                    <td class="td-name"><div>${esc(r.nombre)}</div></td>
                    <td class="tc">${esc(r.niveles)}</td>
                    <td>
                        ${r.descripcion
                            ? `<span class="td-desc-cell" title="${esc(r.descripcion)}">${esc(r.descripcion)}</span>`
                            : `<span class="td-empty">Sin descripción</span>`}
                    </td>
                    <td>
                        <div class="edif-actions">
                            <button class="btn-editar" data-action="editar" data-id="${r.id}">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button class="btn-eliminar-red" data-action="eliminar" data-id="${r.id}" title="Eliminar edificio">
                                <i class="fas fa-trash-alt"></i> Eliminar
                            </button>
                        </div>
                    </td>
                </tr>`).join('');
        }

        const from = filtered.length === 0 ? 0 : (page - 1) * PER_PAGE + 1;
        const to   = Math.min(page * PER_PAGE, filtered.length);
        paginationInfo.textContent = filtered.length === 0
            ? 'Sin registros'
            : `Mostrando ${from}–${to} de ${filtered.length} registros · 10 por página`;

        let btns = `<button class="pag-btn" id="btnPrev" ${page === 1 ? 'disabled' : ''}>
                        <i class="fas fa-chevron-left"></i>
                    </button>`;
        for (let i = 1; i <= totalPages; i++) {
            btns += `<button class="pag-btn ${page === i ? 'pag-active' : ''}" data-page="${i}">${i}</button>`;
        }
        btns += `<button class="pag-btn" id="btnNext" ${page === totalPages ? 'disabled' : ''}>
                     <i class="fas fa-chevron-right"></i>
                 </button>`;
        paginationBtns.innerHTML = btns;

        $('btnPrev').addEventListener('click', () => { if (page > 1) { page--; render(); } });
        $('btnNext').addEventListener('click', () => { if (page < totalPages) { page++; render(); } });
        paginationBtns.querySelectorAll('[data-page]').forEach(btn =>
            btn.addEventListener('click', () => { page = +btn.dataset.page; render(); })
        );
    }

    /* ——— Delegación de eventos en la tabla ——— */
    tableBody.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        const record = buildings.find(b => b.id === +btn.dataset.id);
        if (!record) return;
        if (btn.dataset.action === 'editar') openPanel(record);
        if (btn.dataset.action === 'eliminar') openEliminarModal(record);
    });

    /* ——— Búsqueda y filtro ─── */
    let searchTimer;
    searchInput.addEventListener('input', function () {
        // Solo permitir letras (A-Z, a-z) y números (0-9)
        this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => { search = this.value; page = 1; render(); }, 280);
    });

    /* ─── Panel lateral ─── */


    function openPanel(record) {
        panelRecord = record || {};
        const isEdit = !!record?.id;

        $('panelTitle').textContent    = isEdit ? 'Editar Edificio'                 : 'Nuevo Edificio';
        $('panelSubtitle').textContent = isEdit ? 'Modifica los datos del edificio' : 'Completa los datos del nuevo edificio';

        fieldNombre.value  = isEdit ? record.nombre      : '';
        fieldNiveles.value = isEdit ? record.levelCount  : '';
        fieldDesc.value    = isEdit ? record.descripcion : '';

        // Deshabilitar el número de niveles al editar
        fieldNiveles.disabled = isEdit;
        btnNivelesDec.disabled = isEdit;
        btnNivelesInc.disabled = isEdit;

        $('nombreCount').textContent = fieldNombre.value.length;
        $('descCount').textContent   = fieldDesc.value.length;

        clearErrors();
        sidePanel.classList.add('open');
        panelBackdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
        fieldNombre.focus();
    }

    function closePanel() {
        panelRecord = null;
        sidePanel.classList.remove('open');
        panelBackdrop.classList.remove('active');
        document.body.style.overflow = '';
    }

    $('btnNuevo').addEventListener('click',       () => openPanel(null));
    $('btnClosePanel').addEventListener('click',  closePanel);
    $('btnCancelPanel').addEventListener('click', closePanel);
    panelBackdrop.addEventListener('click',       closePanel);

    // Validaciones de teclado en tiempo real
    fieldNombre.addEventListener('input', function () {
        // Solo permitir letras, números y el guion medio
        this.value = this.value.replace(/[^a-zA-Z0-9\-]/g, '');
        $('nombreCount').textContent = this.value.length;
    });

    fieldDesc.addEventListener('input', function () {
        // Solo permitir letras
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        $('descCount').textContent = this.value.length;
    });

    fieldNiveles.addEventListener('input', function () {
        // Bloquear en tiempo real cualquier letra o símbolo (permitir solo dígitos)
        this.value = this.value.replace(/[^0-9]/g, '');
        
        if (this.value !== '') {
            let val = parseInt(this.value);
            if (val > 5) this.value = '5';
        }
    });

    btnNivelesDec.addEventListener('click', () => {
        let val = parseInt(fieldNiveles.value) || 1;
        if (val > 1) {
            fieldNiveles.value = val - 1;
        }
    });

    btnNivelesInc.addEventListener('click', () => {
        let val = parseInt(fieldNiveles.value) || 0;
        if (val < 5) {
            fieldNiveles.value = val + 1;
        }
    });

    /* ─── Validación del formulario ─── */
    function clearErrors() {
        ['errorNombre','errorNiveles','errorDesc'].forEach(id => $(id).classList.add('hidden'));
        ['fieldNombre','fieldNiveles','fieldDesc'].forEach(id => $(id).classList.remove('has-error'));
    }

    function showError(fieldId, errorId, msgId, msg) {
        $(fieldId).classList.add('has-error');
        $(msgId).textContent = msg;
        $(errorId).classList.remove('hidden');
    }

    function validateForm() {
        clearErrors();
        let ok = true;
        const nombre  = fieldNombre.value.trim();
        const niveles = fieldNiveles.value;
        const desc    = fieldDesc.value.trim();

        if (!nombre) {
            showError('fieldNombre','errorNombre','errorNombreMsg','El nombre es obligatorio.');
            ok = false;
        } else if (nombre.length < 3) {
            showError('fieldNombre','errorNombre','errorNombreMsg','El nombre debe tener al menos 3 caracteres.');
            ok = false;
        } else if (nombre.length > 80) {
            showError('fieldNombre','errorNombre','errorNombreMsg','Máximo 80 caracteres.');
            ok = false;
        } else if (!/^[a-zA-Z0-9\-]+$/.test(nombre)) {
            showError('fieldNombre','errorNombre','errorNombreMsg','Solo letras, números y guion (-).');
            ok = false;
        }

        if (!niveles) {
            showError('fieldNiveles','errorNiveles','errorNivelesMsg','El número de niveles es obligatorio.');
            ok = false;
        } else if (!Number.isInteger(+niveles) || +niveles < 1 || +niveles > 5) {
            showError('fieldNiveles','errorNiveles','errorNivelesMsg','Debe ser un entero entre 1 y 5.');
            ok = false;
        }

        if (desc && !/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(desc)) {
            showError('fieldDesc','errorDesc','errorDescMsg','La descripción solo puede contener letras.');
            ok = false;
        } else if (desc.length > 200) {
            showError('fieldDesc','errorDesc','errorDescMsg','Máximo 200 caracteres.');
            ok = false;
        }

        return ok;
    }

    $('btnSavePanel').addEventListener('click', async function () {
        if (!validateForm()) return;

        const payload = {
            name:        fieldNombre.value.trim(),
            level_count: parseInt(fieldNiveles.value),
            description: fieldDesc.value.trim() || null,
        };

        const saveBtn = $('btnSavePanel');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:7px;"></i>Guardando…';

        try {
            if (panelRecord?.id) {
                await apiFetch(`/api/v1/buildings/${panelRecord.id}`, {
                    method: 'PUT',
                    body: JSON.stringify(payload),
                });
                showToast('Edificio actualizado', `"${payload.name}" se actualizó correctamente.`, 'success');
            } else {
                await apiFetch('/api/v1/buildings', {
                    method: 'POST',
                    body: JSON.stringify(payload),
                });
                showToast('Edificio registrado', `"${payload.name}" fue registrado exitosamente.`, 'success');
            }
            closePanel();
            await loadBuildings();
        } catch (err) {
            if (err.json?.errors) {
                const msgs = err.json.errors;
                if (msgs.name)        showError('fieldNombre',  'errorNombre',  'errorNombreMsg',  msgs.name[0]);
                if (msgs.level_count) showError('fieldNiveles', 'errorNiveles', 'errorNivelesMsg', msgs.level_count[0]);
                if (msgs.description) showError('fieldDesc',    'errorDesc',    'errorDescMsg',    msgs.description[0]);
            } else {
                showToast('Error', err.json?.message ?? 'No se pudo guardar el edificio.', 'error');
            }
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save" style="margin-right:7px;"></i>Guardar';
        }
    });

    /* ─── Modal de eliminar ─── */
    function openEliminarModal(record) {
        eliminarTarget = record;
        $('eliminarText').innerHTML =
            `¿Está seguro de que desea eliminar el edificio
             <strong style="color:var(--midnight);">"${esc(record.nombre)}"</strong>?<br><br>
             Esta acción no se puede deshacer de forma sencilla.`;
        eliminarModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeEliminarModal() {
        eliminarTarget = null;
        eliminarModal.classList.remove('active');
        document.body.style.overflow = '';
    }

    $('btnCloseEliminar').addEventListener('click',  closeEliminarModal);
    $('btnCancelEliminar').addEventListener('click', closeEliminarModal);
    eliminarModal.addEventListener('click', e => { if (e.target === eliminarModal) closeEliminarModal(); });

    $('btnConfirmEliminar').addEventListener('click', async function () {
        if (!eliminarTarget) return;
        const nombre = eliminarTarget.nombre;
        const confirmBtn = $('btnConfirmEliminar');
        confirmBtn.disabled = true;

        try {
            await apiFetch(`/api/v1/buildings/${eliminarTarget.id}`, { method: 'DELETE' });
            closeEliminarModal();
            showToast('Edificio eliminado', `"${nombre}" fue eliminado correctamente.`, 'success');
            await loadBuildings();
        } catch (err) {
            showToast('Error', err.json?.message ?? 'No se pudo eliminar el edificio.', 'error');
        } finally {
            confirmBtn.disabled = false;
        }
    });

    /* ─── Escape ─── */
    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        if (sidePanel.classList.contains('open'))       closePanel();
        if (eliminarModal.classList.contains('active')) closeEliminarModal();
    });

    /* ─── Toast ─── */
    function showToast(title, message, type = 'success') {
        const icons = { success: 'check', error: 'times', warning: 'exclamation' };
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-icon"><i class="fas fa-${icons[type] || 'check'}"></i></div>
            <div class="toast-content">
                <div class="toast-title">${esc(title)}</div>
                <div class="toast-message">${esc(message)}</div>
            </div>
            <button class="toast-close" aria-label="Cerrar"><i class="fas fa-times"></i></button>`;
        toastContainer.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 10);
        const t = setTimeout(() => removeToast(toast), 5000);
        toast.querySelector('.toast-close').addEventListener('click', () => { clearTimeout(t); removeToast(toast); });
    }

    function removeToast(toast) {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }

    /* ─── Arranque: carga desde la API ─── */
    loadBuildings();
});
</script>
@endsection
