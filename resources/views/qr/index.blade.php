@extends('layouts.app')

@section('title', 'Codigo QR')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/qr-screen.css') }}">
@endpush

@section('content')

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
                <div class="header-left">
                    <nav class="breadcrumb">
                        <a href="{{ route('dashboard') }}">Administrador</a>
                        <i class="fas fa-chevron-right"></i>
                        <a>Gestion Academica</a>
                        <i class="fas fa-chevron-right"></i>
                        <span class="current">Codigos QR</span>
                    </nav>
                </div>
            <!-- Page Content -->
            <div class="page-content">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="page-title-row">
                        <div>
                            <h1 class="page-title">Generacion de Codigos QR</h1>
                            <p class="page-subtitle">Genere, visualice y descargue codigos QR estaticos por aula para control de asistencia. Los QR generados estan listos para impresion.</p>
                        </div>
                        <div class="header-actions">
                            <button class="btn btn-outline" id="btnPrint" title="Imprimir seleccionados">
                                <i class="fas fa-print"></i>
                                <span>Imprimir</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Toolbar -->
                <div class="toolbar">
                    <div class="toolbar-left">
                        <div class="filter-group">
                            <label class="filter-label" for="filtroEdificio">Edificio:</label>
                            <select class="filter-select" id="filtroEdificio">
                                <option value="">Cargando edificios...</option>
                            </select>
                        </div>
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchAula" placeholder="Buscar aula...">
                        </div>
                    </div>
                    <div class="toolbar-right">
                        <button class="btn btn-outline" id="btnSelectAll">
                            <i class="fas fa-check-square"></i>
                            <span>Seleccionar todo</span>
                        </button>
                    </div>
                </div>

                <!-- Selection Bar -->
                <div class="selection-bar" id="selectionBar">
                    <div class="selection-info">
                        <span class="selection-count" id="selectionCount">0 aulas seleccionadas</span>
                        <button class="btn btn-ghost btn-sm" id="btnClearSelection">
                            <i class="fas fa-times"></i>
                            Limpiar seleccion
                        </button>
                    </div>
                    <div class="selection-actions">
                        <button class="btn btn-primary btn-md" id="btnGenerarQRBulk">
                            <i class="fas fa-qrcode"></i>
                            Generar QR
                        </button>
                        <button class="btn btn-secondary btn-md" id="btnDescargarZIP">
                            <i class="fas fa-download"></i>
                            Descargar ZIP
                        </button>
                    </div>
                </div>

                <!-- QR Gallery (dinamica) -->
                <div class="qr-gallery" id="qrGallery">
                    <div style="text-align:center;padding:48px;color:var(--soft-steel);width:100%;">
                        <i class="fas fa-spinner fa-spin" style="font-size:28px;"></i>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal de Confirmacion - Regenerar QR -->
    <div class="modal-overlay" id="modalRegenerar">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Confirmar regeneracion</h2>
                <button class="modal-close" id="modalRegenerarClose" aria-label="Cerrar modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <p class="modal-text">
                    Esta a punto de regenerar el codigo QR del aula <strong id="modalAulaName"></strong>.
                    El QR anterior dejara de ser valido y sera reemplazado por uno nuevo.
                    <br><br>Desea continuar?
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="btnCancelarRegenerar">Cancelar</button>
                <button class="btn btn-primary" id="btnConfirmarRegenerar">
                    <i class="fas fa-sync-alt"></i>
                    Regenerar QR
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* ---- Estado ---- */
            let allClassrooms = [];   // { id, nombre, edificioId, edificioNombre, hasActiveQr, qrId, qrImagePath }
            let currentRegenTarget = null;

            /* ---- Refs DOM ---- */
            const filtroEdificio  = document.getElementById('filtroEdificio');
            const searchAula      = document.getElementById('searchAula');
            const qrGallery       = document.getElementById('qrGallery');
            const selectionBar    = document.getElementById('selectionBar');
            const selectionCount  = document.getElementById('selectionCount');
            const btnSelectAll    = document.getElementById('btnSelectAll');
            const btnClearSel     = document.getElementById('btnClearSelection');
            const btnGenerarBulk  = document.getElementById('btnGenerarQRBulk');
            const btnDescargarZIP = document.getElementById('btnDescargarZIP');
            const btnPrint        = document.getElementById('btnPrint');
            const modalRegenerar  = document.getElementById('modalRegenerar');
            const modalAulaName   = document.getElementById('modalAulaName');
            const toastContainer  = document.getElementById('toastContainer');

            /* ---- CSRF ---- */
            function getCsrf() {
                return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            }

            /* ---- API helper ---- */
            async function apiFetch(url, opts = {}) {
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrf(), ...(opts.headers ?? {}) },
                    ...opts,
                });
                const json = await res.json();
                if (!res.ok) throw { status: res.status, json };
                return json;
            }

            /* ---- Toast ---- */
            function showToast(title, message, type = 'success') {
                const icon = { success: 'check', error: 'times', warning: 'exclamation' }[type] ?? 'check';
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                toast.innerHTML =
                    `<div class="toast-icon"><i class="fas fa-${icon}"></i></div>` +
                    `<div class="toast-content"><div class="toast-title">${title}</div><div class="toast-message">${message}</div></div>` +
                    `<button class="toast-close" aria-label="Cerrar notificacion"><i class="fas fa-times"></i></button>`;
                toastContainer.appendChild(toast);
                setTimeout(() => toast.classList.add('show'), 10);
                const ac = setTimeout(() => removeToast(toast), 5000);
                toast.querySelector('.toast-close').addEventListener('click', () => { clearTimeout(ac); removeToast(toast); });
            }

            function removeToast(toast) {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }

            /* ---- Carga inicial ---- */
            async function loadData() {
                try {
                    const [bldRes, clsRes] = await Promise.all([
                        apiFetch('/api/v1/buildings'),
                        apiFetch('/api/v1/classrooms'),
                    ]);

                    const buildings = (bldRes.data ?? []).filter(b => b.isActive);
                    const classrooms = clsRes.data ?? [];

                    /* Poblar select de edificios */
                    filtroEdificio.innerHTML = '<option value="">Todos los edificios</option>' +
                        buildings.map(b => `<option value="${b.id}">${b.name}</option>`).join('');

                    /* Mapear aulas con info de QR */
                    allClassrooms = classrooms.map(c => ({
                        id:             c.id,
                        nombre:         c.classroomName,
                        edificioId:     c.buildingId,
                        edificioNombre: c.buildingName ?? '',
                        isActive:       c.isActive,
                        hasActiveQr:    c.hasActiveQr ?? false,
                        qrId:           c.activeQrId ?? null,
                        qrImageUrl:     c.qrImageUrl  ?? null,
                    }));

                    renderGallery();
                } catch (e) {
                    qrGallery.innerHTML =
                        '<div style="text-align:center;padding:48px;color:var(--status-inactive);width:100%;">Error al cargar datos desde la API.</div>';
                    showToast('Error', 'No se pudieron cargar las aulas.', 'error');
                }
            }

            /* ---- Render galeria ---- */
            function getVisible() {
                const edificioId = filtroEdificio.value;
                const q = searchAula.value.trim().toLowerCase();
                return allClassrooms.filter(c => {
                    const okE = !edificioId || String(c.edificioId) === String(edificioId);
                    const okQ = !q || c.nombre.toLowerCase().includes(q) || c.edificioNombre.toLowerCase().includes(q);
                    return okE && okQ;
                });
            }

            function renderGallery() {
                const visible = getVisible();
                if (!visible.length) {
                    qrGallery.innerHTML =
                        '<div style="text-align:center;padding:48px;color:var(--soft-steel);width:100%;">' +
                        '<i class="fas fa-qrcode" style="font-size:32px;opacity:.3;display:block;margin-bottom:10px;"></i>' +
                        'Sin aulas con los filtros aplicados.</div>';
                    return;
                }

                qrGallery.innerHTML = visible.map(c => buildCard(c)).join('');
                updateSelectionCount();
            }

            function qrImgHtml(c) {
                if (c.hasActiveQr) {
                    /* Si la API devuelve una URL de imagen, usarla; si no, generar placeholder */
                    const src = c.qrImageUrl
                        ? `{{ asset('') }}${c.qrImageUrl}`
                        : `/api/v1/qr-codes/${c.qrId}/file`;
                    return `<div class="qr-preview has-qr">
                        <img src="${src}" alt="QR ${esc(c.nombre)}" loading="lazy">
                    </div>`;
                }
                return `<div class="qr-preview">
                    <div class="qr-preview-placeholder">
                        <i class="fas fa-qrcode"></i>
                        <span>Sin QR generado</span>
                    </div>
                </div>`;
            }

            function buildCard(c) {
                const badgeClass = c.hasActiveQr ? 'badge-activo' : 'badge-pendiente';
                const badgeText  = c.hasActiveQr
                    ? '<i class="fas fa-check-circle"></i> Activo'
                    : '<i class="fas fa-clock"></i> Pendiente';

                const actions = c.hasActiveQr
                    ? `<button class="btn btn-outline btn-sm" data-action="regenerar" data-id="${c.id}" title="Regenerar QR">
                           <i class="fas fa-sync-alt"></i> Regenerar
                       </button>
                       <button class="btn btn-secondary btn-sm" data-action="descargar" data-id="${c.id}" title="Descargar QR">
                           <i class="fas fa-download"></i> Descargar
                       </button>`
                    : `<button class="btn btn-primary btn-sm" data-action="generar" data-id="${c.id}" title="Generar QR">
                           <i class="fas fa-qrcode"></i> Generar QR
                       </button>`;

                return `<div class="qr-card" data-classroom-id="${c.id}" data-edificio-id="${c.edificioId}">
                    <div class="qr-card-checkbox">
                        <input type="checkbox" id="check-${c.id}" aria-label="Seleccionar ${esc(c.nombre)}">
                    </div>
                    <div class="qr-card-header">
                        <div>
                            <div class="qr-card-title">${esc(c.nombre)}</div>
                            <div class="qr-card-subtitle">${esc(c.edificioNombre)}</div>
                        </div>
                        <span class="badge ${badgeClass}">${badgeText}</span>
                    </div>
                    <div class="qr-card-body">${qrImgHtml(c)}<div class="qr-label">${esc(c.nombre)}</div></div>
                    <div class="qr-card-actions">${actions}</div>
                </div>`;
            }

            function esc(str) {
                return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
            }

            /* ---- Filtros ---- */
            filtroEdificio.addEventListener('change', renderGallery);
            let searchTimer;
            searchAula.addEventListener('input', () => { clearTimeout(searchTimer); searchTimer = setTimeout(renderGallery, 280); });

            /* ---- Seleccion ---- */
            qrGallery.addEventListener('change', e => {
                if (e.target.type === 'checkbox') {
                    e.target.closest('.qr-card').classList.toggle('selected', e.target.checked);
                    updateSelectionCount();
                }
            });

            function updateSelectionCount() {
                const count = qrGallery.querySelectorAll('input[type="checkbox"]:checked').length;
                selectionCount.textContent = `${count} ${count === 1 ? 'aula seleccionada' : 'aulas seleccionadas'}`;
                selectionBar.classList.toggle('visible', count > 0);
            }

            btnSelectAll.addEventListener('click', () => {
                const visibleCards = qrGallery.querySelectorAll('.qr-card');
                const allChecked = Array.from(visibleCards).every(c => c.querySelector('input[type="checkbox"]').checked);
                visibleCards.forEach(card => {
                    const cb = card.querySelector('input[type="checkbox"]');
                    cb.checked = !allChecked;
                    card.classList.toggle('selected', !allChecked);
                });
                updateSelectionCount();
            });

            btnClearSel.addEventListener('click', () => {
                qrGallery.querySelectorAll('input[type="checkbox"]:checked').forEach(cb => {
                    cb.checked = false;
                    cb.closest('.qr-card').classList.remove('selected');
                });
                updateSelectionCount();
            });

            /* ---- Acciones de tarjeta ---- */
            qrGallery.addEventListener('click', e => {
                const btn = e.target.closest('button[data-action]');
                if (!btn) return;
                const id = Number(btn.dataset.id);
                switch(btn.dataset.action) {
                    case 'generar':    generarQR(id, btn);    break;
                    case 'regenerar':  abrirConfirmRegenerar(id); break;
                    case 'descargar':  descargarQR(id, btn);  break;
                }
            });

            /* ---- Generar QR ---- */
            async function generarQR(classroomId, btn) {
                const orig = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
                try {
                    const res = await apiFetch(`/api/v1/classrooms/${classroomId}/qr`, { method: 'POST' });
                    const qr = res.data ?? {};
                    /* Actualizar el objeto local */
                    const c = allClassrooms.find(x => x.id === classroomId);
                    if (c) {
                        c.hasActiveQr  = true;
                        c.qrId         = qr.id ?? qr.qrId ?? null;
                        c.qrImageUrl   = qr.imagePath ?? qr.imageUrl ?? null;
                    }
                    renderGallery();
                    showToast('QR generado', `Codigo QR generado exitosamente.`, 'success');
                } catch (err) {
                    btn.disabled = false;
                    btn.innerHTML = orig;
                    showToast('Error', err.json?.message ?? 'No se pudo generar el QR.', 'error');
                }
            }

            /* ---- Modal regenerar ---- */
            function abrirConfirmRegenerar(classroomId) {
                const c = allClassrooms.find(x => x.id === classroomId);
                currentRegenTarget = classroomId;
                modalAulaName.textContent = c?.nombre ?? `ID ${classroomId}`;
                modalRegenerar.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function cerrarModalRegenerar() {
                currentRegenTarget = null;
                modalRegenerar.classList.remove('active');
                document.body.style.overflow = '';
            }

            document.getElementById('modalRegenerarClose').addEventListener('click',  cerrarModalRegenerar);
            document.getElementById('btnCancelarRegenerar').addEventListener('click', cerrarModalRegenerar);
            modalRegenerar.addEventListener('click', e => { if (e.target === modalRegenerar) cerrarModalRegenerar(); });

            document.getElementById('btnConfirmarRegenerar').addEventListener('click', async () => {
                if (!currentRegenTarget) return;
                const id = currentRegenTarget;
                cerrarModalRegenerar();

                /* Reutiliza el endpoint de generacion (lo regenera si ya existe) */
                const card = qrGallery.querySelector(`[data-classroom-id="${id}"]`);
                const regenBtn = card?.querySelector('[data-action="regenerar"]');
                if (regenBtn) {
                    regenBtn.disabled = true;
                    regenBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Regenerando...';
                }
                try {
                    const res = await apiFetch(`/api/v1/classrooms/${id}/qr`, { method: 'POST' });
                    const qr = res.data ?? {};
                    const c = allClassrooms.find(x => x.id === id);
                    if (c) {
                        c.hasActiveQr = true;
                        c.qrId        = qr.id ?? qr.qrId ?? null;
                        c.qrImageUrl  = qr.imagePath ?? qr.imageUrl ?? null;
                    }
                    renderGallery();
                    showToast('QR regenerado', 'El codigo QR fue regenerado exitosamente.', 'success');
                } catch (err) {
                    if (regenBtn) { regenBtn.disabled = false; regenBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Regenerar'; }
                    showToast('Error', err.json?.message ?? 'No se pudo regenerar el QR.', 'error');
                }
            });

            /* ---- Descargar QR individual ---- */
            async function descargarQR(classroomId, btn) {
                const c = allClassrooms.find(x => x.id === classroomId);
                if (!c?.qrId) { showToast('Sin QR', 'El aula no tiene un QR generado.', 'warning'); return; }
                const orig = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                try {
                    const a = document.createElement('a');
                    a.href = `/api/v1/qr-codes/${c.qrId}/file`;
                    a.download = `QR_${c.nombre}.png`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    showToast('Descarga iniciada', `Preparando QR del aula ${c.nombre}.`, 'success');
                } finally {
                    setTimeout(() => { btn.disabled = false; btn.innerHTML = orig; }, 1500);
                }
            }

            /* ---- Generar QR masivo ---- */
            btnGenerarBulk.addEventListener('click', async () => {
                const selectedCards = Array.from(qrGallery.querySelectorAll('.qr-card.selected'));
                const pendientes = selectedCards.filter(card => {
                    const id = Number(card.dataset.classroomId);
                    return !allClassrooms.find(c => c.id === id)?.hasActiveQr;
                });

                if (!pendientes.length) {
                    showToast('Sin pendientes', 'Todas las aulas seleccionadas ya tienen QR generado.', 'warning');
                    return;
                }

                btnGenerarBulk.disabled = true;
                btnGenerarBulk.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';

                let ok = 0, fail = 0;
                for (const card of pendientes) {
                    const id = Number(card.dataset.classroomId);
                    try {
                        const res = await apiFetch(`/api/v1/classrooms/${id}/qr`, { method: 'POST' });
                        const qr = res.data ?? {};
                        const c = allClassrooms.find(x => x.id === id);
                        if (c) { c.hasActiveQr = true; c.qrId = qr.id ?? qr.qrId ?? null; c.qrImageUrl = qr.imagePath ?? null; }
                        ok++;
                    } catch (_) { fail++; }
                }

                renderGallery();
                btnGenerarBulk.disabled = false;
                btnGenerarBulk.innerHTML = '<i class="fas fa-qrcode"></i> Generar QR';

                if (ok > 0) showToast('QR generados', `${ok} codigo(s) QR generado(s) exitosamente.`, 'success');
                if (fail > 0) showToast('Errores parciales', `${fail} aula(s) no pudieron generarse.`, 'warning');
            });

            /* ---- Descargar ZIP ---- */
            btnDescargarZIP.addEventListener('click', async () => {
                const selectedCards = Array.from(qrGallery.querySelectorAll('.qr-card.selected'));
                const conQr = selectedCards
                    .map(card => Number(card.dataset.classroomId))
                    .filter(id => allClassrooms.find(c => c.id === id)?.qrId)
                    .map(id => allClassrooms.find(c => c.id === id).qrId);

                if (!conQr.length) {
                    showToast('Sin QR para descargar', 'Seleccione aulas con QR activo para descargar.', 'warning');
                    return;
                }

                btnDescargarZIP.disabled = true;
                btnDescargarZIP.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Preparando...';

                try {
                    const res = await fetch('/api/v1/qr-codes/download', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/octet-stream', 'X-CSRF-TOKEN': getCsrf() },
                        body: JSON.stringify({ qr_code_ids: conQr }),
                    });
                    if (!res.ok) throw new Error('Error en descarga');
                    const blob = await res.blob();
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = 'codigos_qr.zip';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(a.href);
                    showToast('Descarga iniciada', `ZIP con ${conQr.length} codigos QR listo.`, 'success');
                } catch (err) {
                    showToast('Error', 'No se pudo generar el ZIP.', 'error');
                } finally {
                    btnDescargarZIP.disabled = false;
                    btnDescargarZIP.innerHTML = '<i class="fas fa-download"></i> Descargar ZIP';
                }
            });

            /* ---- Imprimir ---- */
            btnPrint.addEventListener('click', () => {
                const selected = Array.from(qrGallery.querySelectorAll('.qr-card.selected'));
                const visible  = Array.from(qrGallery.querySelectorAll('.qr-card'));
                const toPrint  = selected.length > 0 ? selected : visible;

                if (!toPrint.length) { showToast('Sin resultados', 'No hay aulas para imprimir.', 'warning'); return; }

                const printWin = window.open('', '_blank', 'width=1000,height=700');
                if (!printWin) { showToast('Bloqueado', 'Permita ventanas emergentes para imprimir.', 'error'); return; }

                const cardsHtml = toPrint.map(card => {
                    const titulo    = card.querySelector('.qr-card-title')?.textContent ?? '';
                    const subtitulo = card.querySelector('.qr-card-subtitle')?.textContent ?? '';
                    const preview   = card.querySelector('.qr-preview')?.outerHTML ?? '';
                    return `<div class="print-item"><div class="print-header"><h2>${titulo}</h2><p>${subtitulo}</p></div>${preview}</div>`;
                }).join('');

                printWin.document.write(`<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Impresion QR</title>
                <style>body{font-family:Arial,sans-serif;margin:20px}.print-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:20px}
                .print-item{border:1px solid #ccc;padding:15px;text-align:center;border-radius:8px}
                .print-header h2{margin:0;font-size:18px}.print-header p{margin:4px 0 10px;font-size:13px;color:#555}
                .qr-preview img{width:140px;height:140px}@media print{body{margin:10mm}.print-item{break-inside:avoid}}</style>
                </head><body>
                <div style="text-align:center;margin-bottom:20px"><h1 style="margin:0;font-size:22px">Codigos QR - GAMA</h1></div>
                <div class="print-grid">${cardsHtml}</div></body></html>`);
                printWin.document.close();
                printWin.focus();
                setTimeout(() => { printWin.print(); printWin.close(); }, 300);
            });

            /* ---- Escape cierra modal ---- */
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape' && modalRegenerar.classList.contains('active')) cerrarModalRegenerar();
            });

            /* ---- Arranque ---- */
            loadData();
        });
    </script>
@endsection
