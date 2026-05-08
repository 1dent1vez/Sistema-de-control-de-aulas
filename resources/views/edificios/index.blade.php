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

/* ── Contenido principal ── */
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

/* ── Encabezado de página ── */
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

/* ── Tabla ── */
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

/* ── Badges de estatus ── */
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

/* ── Botones de acciones ── */
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

.btn-inactivar {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    background: transparent;
    color: var(--soft-steel);
    border: 1px solid var(--mist-blue);
    border-radius: var(--radius-sm);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    font-family: var(--font-main);
    transition: all 0.18s;
    white-space: nowrap;
    min-height: 34px;
}

.btn-inactivar:hover:not([disabled]) {
    color: var(--deep-orange);
    border-color: var(--deep-orange);
    background: rgba(217, 106, 16, 0.08);
}

.btn-inactivar[disabled] {
    opacity: 0.45;
    cursor: not-allowed;
}

.btn-inactivar.blocked {
    opacity: 1;
    color: var(--status-inactive);
    border-color: rgba(194,120,120,0.4);
    background: rgba(194,120,120,0.08);
}

.inact-label {
    font-size: 11px;
    color: var(--soft-steel);
    padding: 5px 10px;
    background: var(--ice-blue);
    border-radius: var(--radius-sm);
}

/* ── Fila vacía ── */
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

/* ── Paginación ── */
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

/* ── Panel lateral ── */
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

/* ── Modal de confirmación (inactivar) ── */
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

.btn-confirm-inactivar {
    padding: 10px 20px;
    background: var(--deep-orange);
    color: white;
    border: none;
    border-radius: var(--radius-md);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    font-family: var(--font-main);
    transition: background 0.18s;
}

.btn-confirm-inactivar:hover { background: #b85a0d; }

/* ── Responsive ── */
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

        {{-- ── Encabezado ── --}}
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
                <p class="edif-page-subtitle">Registrar, consultar, editar e inactivar edificios de la institución</p>
            </div>
            <button class="btn-nuevo-edif" id="btnNuevo">
                <i class="fas fa-plus"></i>
                Nuevo Edificio
            </button>
        </div>

        {{-- ── Tabla ── --}}
        <div class="edif-card">

            {{-- Barra de herramientas --}}
            <div class="edif-toolbar">
                <div class="edif-toolbar-left">
                    <div class="edif-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Buscar edificio…" autocomplete="off">
                    </div>
                    <select class="edif-select" id="filterEstatus">
                        <option value="">Todos los estatus</option>
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
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
                            <th style="width:110px;">Estatus</th>
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

{{-- ══════════════════════════════════════
     Panel lateral — Nuevo / Editar
══════════════════════════════════════ --}}
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
                   placeholder="Ej. Edificio A — Ciencias">
            <div class="form-hint"><span id="nombreCount">0</span>/80</div>
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
            <input type="number" class="form-input" id="fieldNiveles" min="1" step="1" placeholder="Ej. 4">
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
            <div class="form-hint"><span id="descCount">0</span>/200</div>
            <div class="form-error hidden" id="errorDesc">
                <i class="fas fa-exclamation-circle"></i>
                <span id="errorDescMsg"></span>
            </div>
        </div>

        {{-- Estatus (solo lectura) --}}
        <div class="form-field">
            <label class="form-label">Estatus</label>
            <div class="form-static">
                <span class="st-badge" id="panelStatusBadge"></span>
                <span class="form-static-hint" id="panelStatusHint"></span>
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

{{-- ══════════════════════════════════════
     Modal — Confirmar inactivar
══════════════════════════════════════ --}}
<div class="modal-overlay" id="inactivarModal" role="dialog" aria-modal="true">
    <div class="modal">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Inactivar Edificio</h3>
                <p style="font-size:13px;color:var(--soft-steel);margin-top:3px;">
                    Esta acción cambiará el estatus del registro
                </p>
            </div>
            <button class="modal-close" id="btnCloseInactivar" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-warn-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <p class="modal-text" id="inactivarText"></p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="btnCancelInactivar">Cancelar</button>
            <button class="btn-confirm-inactivar" id="btnConfirmInactivar">
                <i class="fas fa-ban" style="margin-right:7px;"></i>Inactivar
            </button>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="toast-container" id="toastContainer"></div>

{{-- ══════════════════════════════════════
     JavaScript — Lógica de la vista
══════════════════════════════════════ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ─── Datos de prueba (reemplazar con datos del controlador cuando esté listo) ─── */
    const SEED = [
        { id:  1, nombre: 'Edificio A — Ciencias',    niveles: 4, descripcion: 'Bloque principal, aulas de ciencias básicas.',    estatus: 'Activo',   aulas_activas: 12 },
        { id:  2, nombre: 'Edificio B — Humanidades', niveles: 3, descripcion: 'Aulas de humanidades y ciencias sociales.',       estatus: 'Activo',   aulas_activas: 8  },
        { id:  3, nombre: 'Edificio C — Tecnología',  niveles: 5, descripcion: 'Laboratorios de cómputo y electrónica.',         estatus: 'Activo',   aulas_activas: 0  },
        { id:  4, nombre: 'Edificio D — Posgrado',    niveles: 2, descripcion: 'Salones exclusivos para programas de maestría.', estatus: 'Inactivo', aulas_activas: 0  },
        { id:  5, nombre: 'Edificio E — Idiomas',     niveles: 2, descripcion: '',                                                estatus: 'Activo',   aulas_activas: 6  },
        { id:  6, nombre: 'Edificio F — Arte',        niveles: 1, descripcion: 'Talleres de arte y diseño.',                     estatus: 'Activo',   aulas_activas: 3  },
        { id:  7, nombre: 'Edificio G — Deportes',    niveles: 1, descripcion: 'Instalaciones deportivas y gimnasio.',           estatus: 'Activo',   aulas_activas: 0  },
        { id:  8, nombre: 'Edificio H — Rectoría',    niveles: 3, descripcion: 'Administración central.',                        estatus: 'Activo',   aulas_activas: 2  },
        { id:  9, nombre: 'Edificio I — Biblioteca',  niveles: 2, descripcion: 'Acervo bibliográfico y salas de estudio.',      estatus: 'Activo',   aulas_activas: 0  },
        { id: 10, nombre: 'Edificio J — Cafetería',   niveles: 1, descripcion: 'Área de servicios generales.',                  estatus: 'Inactivo', aulas_activas: 0  },
        { id: 11, nombre: 'Edificio K — Innovación',  niveles: 6, descripcion: 'Hub de innovación y startups.',                 estatus: 'Activo',   aulas_activas: 4  },
        { id: 12, nombre: 'Edificio L — Medicina',    niveles: 4, descripcion: 'Facultad de ciencias médicas.',                 estatus: 'Activo',   aulas_activas: 9  },
    ];

    const PER_PAGE = 10;
    let nextId     = 13;
    let buildings  = [...SEED];

    /* ─── Estado ─── */
    let page           = 1;
    let search         = '';
    let filterEstatus  = '';
    let panelRecord    = null;   // null=cerrado | {}=nuevo | {id,...}=editar
    let inactivarTarget = null;

    /* ─── Refs DOM ─── */
    const $ = id => document.getElementById(id);
    const searchInput    = $('searchInput');
    const filterEstatusEl = $('filterEstatus');
    const resultsCount   = $('resultsCount');
    const tableBody      = $('tableBody');
    const paginationInfo = $('paginationInfo');
    const paginationBtns = $('paginationBtns');
    const panelBackdrop  = $('panelBackdrop');
    const sidePanel      = $('sidePanel');
    const fieldNombre    = $('fieldNombre');
    const fieldNiveles   = $('fieldNiveles');
    const fieldDesc      = $('fieldDesc');
    const inactivarModal = $('inactivarModal');
    const toastContainer = $('toastContainer');

    /* ─── Utilidades ─── */
    function esc(str) {
        if (!str) return '';
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function badge(estatus) {
        const cls = estatus === 'Activo' ? 'st-activo' : 'st-inactivo';
        return `<span class="st-badge ${cls}">${estatus}</span>`;
    }

    /* ─── Filtrado ─── */
    function getFiltered() {
        const q = search.toLowerCase();
        return buildings.filter(b => {
            const matchQ = !q || b.nombre.toLowerCase().includes(q) || b.descripcion.toLowerCase().includes(q);
            const matchE = !filterEstatus || b.estatus === filterEstatus;
            return matchQ && matchE;
        });
    }

    /* ─── Render principal ─── */
    function render() {
        const filtered   = getFiltered();
        const totalPages = Math.max(1, Math.ceil(filtered.length / PER_PAGE));
        if (page > totalPages) page = totalPages;
        const pageData = filtered.slice((page - 1) * PER_PAGE, page * PER_PAGE);

        /* Contador */
        const n = filtered.length;
        resultsCount.textContent = `${n} edificio${n !== 1 ? 's' : ''} encontrado${n !== 1 ? 's' : ''}`;

        /* Filas */
        if (pageData.length === 0) {
            tableBody.innerHTML = `
                <tr class="edif-empty">
                    <td colspan="6">
                        <i class="fas fa-building"></i>
                        No se encontraron edificios con los filtros aplicados.
                    </td>
                </tr>`;
        } else {
            tableBody.innerHTML = pageData.map(r => `
                <tr>
                    <td class="td-id">${r.id}</td>
                    <td class="td-name">
                        <div>${esc(r.nombre)}</div>
                        ${r.aulas_activas > 0 ? `<div class="td-hint">${r.aulas_activas} aulas activas</div>` : ''}
                    </td>
                    <td class="tc">${r.niveles}</td>
                    <td>
                        ${r.descripcion
                            ? `<span class="td-desc-cell" title="${esc(r.descripcion)}">${esc(r.descripcion)}</span>`
                            : `<span class="td-empty">Sin descripción</span>`}
                    </td>
                    <td>${badge(r.estatus)}</td>
                    <td>
                        <div class="edif-actions">
                            <button class="btn-editar" data-action="editar" data-id="${r.id}">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            ${r.estatus === 'Activo'
                                ? `<button class="btn-inactivar ${r.aulas_activas > 0 ? 'blocked' : ''}" data-action="inactivar" data-id="${r.id}"
                                        title="${r.aulas_activas > 0 ? 'RN-03: No se permite inactivar con aulas asociadas' : 'Inactivar edificio'}">
                                       <i class="fas fa-ban"></i> Inactivar
                                   </button>`
                                : `<span class="inact-label">Inactivo</span>`
                            }
                        </div>
                    </td>
                </tr>`).join('');
        }

        /* Info paginación */
        const from = filtered.length === 0 ? 0 : (page - 1) * PER_PAGE + 1;
        const to   = Math.min(page * PER_PAGE, filtered.length);
        paginationInfo.textContent = filtered.length === 0
            ? 'Sin registros'
            : `Mostrando ${from}–${to} de ${filtered.length} registros · 10 por página`;

        /* Botones paginación */
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

    /* ─── Delegación de eventos en la tabla ─── */
    tableBody.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        const record = buildings.find(b => b.id === +btn.dataset.id);
        if (!record) return;
        if (btn.dataset.action === 'editar')     openPanel(record);
        if (btn.dataset.action === 'inactivar')  {
            if (record.aulas_activas > 0) {
                showToast(
                    'RN-03: Inactivación bloqueada',
                    'No se puede inactivar el edificio porque tiene aulas asociadas.',
                    'warning'
                );
                return;
            }
            openInactivarModal(record);
        }
    });

    /* ─── Búsqueda y filtro ─── */
    let searchTimer;
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => { search = this.value; page = 1; render(); }, 280);
    });

    filterEstatusEl.addEventListener('change', function () {
        filterEstatus = this.value; page = 1; render();
    });

    /* ─── Panel lateral ─── */
    function openPanel(record) {
        panelRecord = record || {};
        const isEdit = !!record?.id;

        $('panelTitle').textContent    = isEdit ? 'Editar Edificio'                : 'Nuevo Edificio';
        $('panelSubtitle').textContent = isEdit ? 'Modifica los datos del edificio' : 'Completa los datos del nuevo edificio';

        fieldNombre.value  = isEdit ? record.nombre      : '';
        fieldNiveles.value = isEdit ? record.niveles     : '';
        fieldDesc.value    = isEdit ? record.descripcion : '';

        $('nombreCount').textContent = fieldNombre.value.length;
        $('descCount').textContent   = fieldDesc.value.length;

        const estatus = isEdit ? record.estatus : 'Activo';
        const badge   = $('panelStatusBadge');
        badge.className   = `st-badge ${estatus === 'Activo' ? 'st-activo' : 'st-inactivo'}`;
        badge.textContent = estatus;
        $('panelStatusHint').textContent = isEdit
            ? '(se gestiona con el botón Inactivar)'
            : 'Por defecto: Activo';

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

    fieldNombre.addEventListener('input', () => $('nombreCount').textContent = fieldNombre.value.length);
    fieldDesc.addEventListener('input',   () => $('descCount').textContent   = fieldDesc.value.length);

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
        const desc    = fieldDesc.value;

        if (!nombre) {
            showError('fieldNombre','errorNombre','errorNombreMsg','El nombre es obligatorio.');
            ok = false;
        } else if (nombre.length > 80) {
            showError('fieldNombre','errorNombre','errorNombreMsg','Máximo 80 caracteres.');
            ok = false;
        } else if (buildings.some(b => b.nombre.toLowerCase() === nombre.toLowerCase() && b.id !== panelRecord?.id)) {
            showError('fieldNombre','errorNombre','errorNombreMsg','Ya existe un edificio con ese nombre');
            ok = false;
        }

        if (!niveles) {
            showError('fieldNiveles','errorNiveles','errorNivelesMsg','El número de niveles es obligatorio.');
            ok = false;
        } else if (!Number.isInteger(+niveles) || +niveles <= 0) {
            showError('fieldNiveles','errorNiveles','errorNivelesMsg','Debe ser un entero positivo mayor a cero.');
            ok = false;
        }

        if (desc.length > 200) {
            showError('fieldDesc','errorDesc','errorDescMsg','Máximo 200 caracteres.');
            ok = false;
        }

        return ok;
    }

    $('btnSavePanel').addEventListener('click', function () {
        if (!validateForm()) return;

        const fields = {
            nombre:      fieldNombre.value.trim(),
            niveles:     parseInt(fieldNiveles.value),
            descripcion: fieldDesc.value.trim(),
        };

        if (panelRecord?.id) {
            const idx = buildings.findIndex(b => b.id === panelRecord.id);
            buildings[idx] = { ...buildings[idx], ...fields };
            showToast('Edificio actualizado', `"${fields.nombre}" se actualizó correctamente.`, 'success');
        } else {
            buildings.push({ id: nextId++, ...fields, estatus: 'Activo', aulas_activas: 0 });
            showToast('Edificio registrado exitosamente', `"${fields.nombre}" fue registrado exitosamente.`, 'success');
        }
        closePanel();
        render();
    });

    /* ─── Modal de inactivar ─── */
    function openInactivarModal(record) {
        inactivarTarget = record;
        $('inactivarText').innerHTML =
            `¿Está seguro de que desea inactivar el edificio
             <strong style="color:var(--midnight);">"${esc(record.nombre)}"</strong>?<br><br>
             El registro no se eliminará físicamente. El estatus cambiará a <strong>Inactivo</strong>.`;
        inactivarModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeInactivarModal() {
        inactivarTarget = null;
        inactivarModal.classList.remove('active');
        document.body.style.overflow = '';
    }

    $('btnCloseInactivar').addEventListener('click',  closeInactivarModal);
    $('btnCancelInactivar').addEventListener('click', closeInactivarModal);
    inactivarModal.addEventListener('click', e => { if (e.target === inactivarModal) closeInactivarModal(); });

    $('btnConfirmInactivar').addEventListener('click', function () {
        if (!inactivarTarget) return;
        const idx = buildings.findIndex(b => b.id === inactivarTarget.id);
        const nombre = inactivarTarget.nombre;
        buildings[idx] = { ...buildings[idx], estatus: 'Inactivo' };
        closeInactivarModal();
        render();
        showToast('Edificio inactivado', `"${nombre}" fue inactivado correctamente.`, 'success');
    });

    /* ─── Escape ─── */
    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        if (sidePanel.classList.contains('open'))       closePanel();
        if (inactivarModal.classList.contains('active')) closeInactivarModal();
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

    /* ─── Arranque ─── */
    render();
});
</script>

@endsection
