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
                        <a>Gestión Académica</a>
                        <i class="fas fa-chevron-right"></i>
                        <span class="current">Códigos QR</span>
                    </nav>
                </div>
            <!-- Page Content -->
            <div class="page-content">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="page-title-row">
                        <div>
                            <h1 class="page-title">Generación de Códigos QR</h1>
                            <p class="page-subtitle">Genere, visualice y descargue códigos QR estáticos por aula para control de asistencia. Los QR generados están listos para impresión.</p>
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
                                <option value="">Todos los edificios</option>
                                <option value="A">Edificio A - Principal</option>
                                <option value="B">Edificio B - Ciencias</option>
                                <option value="C">Edificio C - Humanidades</option>
                                <option value="D">Edificio D - Laboratorios</option>
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
                            Limpiar selección
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

                <!-- QR Gallery -->
                <div class="qr-gallery" id="qrGallery">
                    <!-- Card 1 - Con QR Activo -->
                    <div class="qr-card" data-edificio="A" data-aula="A101">
                        <div class="qr-card-checkbox">
                            <input type="checkbox" id="check-A101" aria-label="Seleccionar Aula A101">
                        </div>
                        <div class="qr-card-header">
                            <div>
                                <div class="qr-card-title">Aula A101</div>
                                <div class="qr-card-subtitle">Edificio A - Principal</div>
                            </div>
                            <span class="badge badge-activo">
                                <i class="fas fa-check-circle"></i>
                                Activo
                            </span>
                        </div>
                        <div class="qr-card-body">
                            <div class="qr-preview has-qr">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=https://gama.edu.mx/asistencia/A101&format=svg" alt="QR Aula A101">
                            </div>
                            <div class="qr-label">Aula A101 - Capacidad 40</div>
                        </div>
                        <div class="qr-card-actions">
                            <button class="btn btn-outline btn-sm" title="Regenerar QR" data-action="regenerar" data-aula="A101">
                                <i class="fas fa-sync-alt"></i>
                                Regenerar
                            </button>
                            <button class="btn btn-secondary btn-sm" title="Descargar QR" data-action="descargar" data-aula="A101">
                                <i class="fas fa-download"></i>
                                Descargar
                            </button>
                        </div>
                    </div>

                    <!-- Card 2 - Con QR Activo -->
                    <div class="qr-card" data-edificio="A" data-aula="A102">
                        <div class="qr-card-checkbox">
                            <input type="checkbox" id="check-A102" aria-label="Seleccionar Aula A102">
                        </div>
                        <div class="qr-card-header">
                            <div>
                                <div class="qr-card-title">Aula A102</div>
                                <div class="qr-card-subtitle">Edificio A - Principal</div>
                            </div>
                            <span class="badge badge-activo">
                                <i class="fas fa-check-circle"></i>
                                Activo
                            </span>
                        </div>
                        <div class="qr-card-body">
                            <div class="qr-preview has-qr">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=https://gama.edu.mx/asistencia/A102&format=svg" alt="QR Aula A102">
                            </div>
                            <div class="qr-label">Aula A102 - Capacidad 35</div>
                        </div>
                        <div class="qr-card-actions">
                            <button class="btn btn-outline btn-sm" title="Regenerar QR" data-action="regenerar" data-aula="A102">
                                <i class="fas fa-sync-alt"></i>
                                Regenerar
                            </button>
                            <button class="btn btn-secondary btn-sm" title="Descargar QR" data-action="descargar" data-aula="A102">
                                <i class="fas fa-download"></i>
                                Descargar
                            </button>
                        </div>
                    </div>

                    <!-- Card 3 - Pendiente -->
                    <div class="qr-card" data-edificio="B" data-aula="B201">
                        <div class="qr-card-checkbox">
                            <input type="checkbox" id="check-B201" aria-label="Seleccionar Aula B201">
                        </div>
                        <div class="qr-card-header">
                            <div>
                                <div class="qr-card-title">Aula B201</div>
                                <div class="qr-card-subtitle">Edificio B - Ciencias</div>
                            </div>
                            <span class="badge badge-pendiente">
                                <i class="fas fa-clock"></i>
                                Pendiente
                            </span>
                        </div>
                        <div class="qr-card-body">
                            <div class="qr-preview">
                                <div class="qr-preview-placeholder">
                                    <i class="fas fa-qrcode"></i>
                                    <span>Sin QR generado</span>
                                </div>
                            </div>
                            <div class="qr-label">Aula B201 - Capacidad 30</div>
                        </div>
                        <div class="qr-card-actions">
                            <button class="btn btn-primary btn-sm" title="Generar QR" data-action="generar" data-aula="B201">
                                <i class="fas fa-qrcode"></i>
                                Generar QR
                            </button>
                        </div>
                    </div>

                    <!-- Card 4 - Con QR Activo -->
                    <div class="qr-card" data-edificio="B" data-aula="B202">
                        <div class="qr-card-checkbox">
                            <input type="checkbox" id="check-B202" aria-label="Seleccionar Aula B202">
                        </div>
                        <div class="qr-card-header">
                            <div>
                                <div class="qr-card-title">Aula B202</div>
                                <div class="qr-card-subtitle">Edificio B - Ciencias</div>
                            </div>
                            <span class="badge badge-activo">
                                <i class="fas fa-check-circle"></i>
                                Activo
                            </span>
                        </div>
                        <div class="qr-card-body">
                            <div class="qr-preview has-qr">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=https://gama.edu.mx/asistencia/B202&format=svg" alt="QR Aula B202">
                            </div>
                            <div class="qr-label">Aula B202 - Capacidad 25</div>
                        </div>
                        <div class="qr-card-actions">
                            <button class="btn btn-outline btn-sm" title="Regenerar QR" data-action="regenerar" data-aula="B202">
                                <i class="fas fa-sync-alt"></i>
                                Regenerar
                            </button>
                            <button class="btn btn-secondary btn-sm" title="Descargar QR" data-action="descargar" data-aula="B202">
                                <i class="fas fa-download"></i>
                                Descargar
                            </button>
                        </div>
                    </div>

                    <!-- Card 5 - Pendiente -->
                    <div class="qr-card" data-edificio="C" data-aula="C301">
                        <div class="qr-card-checkbox">
                            <input type="checkbox" id="check-C301" aria-label="Seleccionar Aula C301">
                        </div>
                        <div class="qr-card-header">
                            <div>
                                <div class="qr-card-title">Aula C301</div>
                                <div class="qr-card-subtitle">Edificio C - Humanidades</div>
                            </div>
                            <span class="badge badge-pendiente">
                                <i class="fas fa-clock"></i>
                                Pendiente
                            </span>
                        </div>
                        <div class="qr-card-body">
                            <div class="qr-preview">
                                <div class="qr-preview-placeholder">
                                    <i class="fas fa-qrcode"></i>
                                    <span>Sin QR generado</span>
                                </div>
                            </div>
                            <div class="qr-label">Aula C301 - Capacidad 45</div>
                        </div>
                        <div class="qr-card-actions">
                            <button class="btn btn-primary btn-sm" title="Generar QR" data-action="generar" data-aula="C301">
                                <i class="fas fa-qrcode"></i>
                                Generar QR
                            </button>
                        </div>
                    </div>

                    <!-- Card 6 - Laboratorio con QR -->
                    <div class="qr-card" data-edificio="D" data-aula="D-LAB1">
                        <div class="qr-card-checkbox">
                            <input type="checkbox" id="check-DLAB1" aria-label="Seleccionar Laboratorio D-LAB1">
                        </div>
                        <div class="qr-card-header">
                            <div>
                                <div class="qr-card-title">Laboratorio D-LAB1</div>
                                <div class="qr-card-subtitle">Edificio D - Laboratorios</div>
                            </div>
                            <span class="badge badge-activo">
                                <i class="fas fa-check-circle"></i>
                                Activo
                            </span>
                        </div>
                        <div class="qr-card-body">
                            <div class="qr-preview has-qr">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=https://gama.edu.mx/asistencia/D-LAB1&format=svg" alt="QR Laboratorio D-LAB1">
                            </div>
                            <div class="qr-label">Lab. Cómputo 1 - Capacidad 20</div>
                        </div>
                        <div class="qr-card-actions">
                            <button class="btn btn-outline btn-sm" title="Regenerar QR" data-action="regenerar" data-aula="D-LAB1">
                                <i class="fas fa-sync-alt"></i>
                                Regenerar
                            </button>
                            <button class="btn btn-secondary btn-sm" title="Descargar QR" data-action="descargar" data-aula="D-LAB1">
                                <i class="fas fa-download"></i>
                                Descargar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal de Confirmación - Regenerar QR -->
    <div class="modal-overlay" id="modalRegenerar">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Confirmar regeneración</h2>
                <button class="modal-close" id="modalRegenerarClose" aria-label="Cerrar modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <p class="modal-text">
                    Está a punto de regenerar el código QR del aula <strong id="modalAulaName">A101</strong>. 
                    El QR anterior dejará de ser válido y será reemplazado por uno nuevo.
                    <br><br>
                    ¿Desea continuar?
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
            // Referencias DOM
            const filtroEdificio = document.getElementById('filtroEdificio');
            const searchAula = document.getElementById('searchAula');
            const qrGallery = document.getElementById('qrGallery');
            const selectionBar = document.getElementById('selectionBar');
            const selectionCount = document.getElementById('selectionCount');
            const btnSelectAll = document.getElementById('btnSelectAll');
            const btnClearSelection = document.getElementById('btnClearSelection');
            const btnGenerarQRBulk = document.getElementById('btnGenerarQRBulk');
            const btnDescargarZIP = document.getElementById('btnDescargarZIP');
            const btnPrint = document.getElementById('btnPrint');
            const modalRegenerar = document.getElementById('modalRegenerar');
            const modalAulaName = document.getElementById('modalAulaName');
            const toastContainer = document.getElementById('toastContainer');

            let currentAulaToRegenerate = null;

            // ========================================
            // Filtrado por Edificio
            // ========================================
            filtroEdificio.addEventListener('change', function() {
                filterCards();
            });

            // ========================================
            // Búsqueda de Aulas
            // ========================================
            let searchTimeout;
            searchAula.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(filterCards, 300);
            });

            function filterCards() {
                const edificio = filtroEdificio.value.toLowerCase();
                const search = searchAula.value.toLowerCase();
                const cards = qrGallery.querySelectorAll('.qr-card');

                cards.forEach(card => {
                    const cardEdificio = card.dataset.edificio.toLowerCase();
                    const cardAula = card.dataset.aula.toLowerCase();
                    const cardTitle = card.querySelector('.qr-card-title').textContent.toLowerCase();

                    const matchEdificio = !edificio || cardEdificio === edificio;
                    const matchSearch = !search || cardAula.includes(search) || cardTitle.includes(search);

                    card.style.display = (matchEdificio && matchSearch) ? '' : 'none';
                });

                updateSelectionCount();
            }

            // ========================================
            // Selección de Tarjetas
            // ========================================
            qrGallery.addEventListener('change', function(e) {
                if (e.target.type === 'checkbox') {
                    const card = e.target.closest('.qr-card');
                    card.classList.toggle('selected', e.target.checked);
                    updateSelectionCount();
                }
            });

            function updateSelectionCount() {
                const checkboxes = qrGallery.querySelectorAll('input[type="checkbox"]:checked');
                const count = checkboxes.length;
                selectionCount.textContent = `${count} ${count === 1 ? 'aula seleccionada' : 'aulas seleccionadas'}`;
                selectionBar.classList.toggle('visible', count > 0);
            }

            btnSelectAll.addEventListener('click', function() {
                const visibleCards = qrGallery.querySelectorAll('.qr-card:not([style*="display: none"])');
                const allChecked = Array.from(visibleCards).every(card => 
                    card.querySelector('input[type="checkbox"]').checked
                );

                visibleCards.forEach(card => {
                    const checkbox = card.querySelector('input[type="checkbox"]');
                    checkbox.checked = !allChecked;
                    card.classList.toggle('selected', !allChecked);
                });

                updateSelectionCount();
            });

            btnClearSelection.addEventListener('click', function() {
                const checkboxes = qrGallery.querySelectorAll('input[type="checkbox"]:checked');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                    checkbox.closest('.qr-card').classList.remove('selected');
                });
                updateSelectionCount();
            });

            // ========================================
            // Acciones de Tarjetas (Generar, Regenerar, Descargar)
            // ========================================
            qrGallery.addEventListener('click', function(e) {
                const btn = e.target.closest('button[data-action]');
                if (!btn) return;

                const action = btn.dataset.action;
                const aula = btn.dataset.aula;

                switch(action) {
                    case 'generar':
                        generarQR(btn, aula);
                        break;
                    case 'regenerar':
                        currentAulaToRegenerate = { btn, aula };
                        modalAulaName.textContent = aula;
                        openModal(modalRegenerar);
                        break;
                    case 'descargar':
                        descargarQR(aula);
                        break;
                }
            });

            function generarQR(btn, aula) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';

                // Simulación de generación
                setTimeout(() => {
                    const card = btn.closest('.qr-card');
                    const badge = card.querySelector('.badge');
                    badge.className = 'badge badge-activo';
                    badge.innerHTML = '<i class="fas fa-check-circle"></i> Activo';

                    const qrPreview = card.querySelector('.qr-preview');
                    qrPreview.classList.add('has-qr');
                    qrPreview.innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=https://gama.edu.mx/asistencia/${aula}&format=svg" alt="QR Aula ${aula}">`;

                    const actionsDiv = card.querySelector('.qr-card-actions');
                    actionsDiv.innerHTML = `
                        <button class="btn btn-outline btn-sm" title="Regenerar QR" data-action="regenerar" data-aula="${aula}">
                            <i class="fas fa-sync-alt"></i>
                            Regenerar
                        </button>
                        <button class="btn btn-secondary btn-sm" title="Descargar QR" data-action="descargar" data-aula="${aula}">
                            <i class="fas fa-download"></i>
                            Descargar
                        </button>
                    `;

                    showToast('QR generado exitosamente', `El código QR del aula ${aula} ha sido creado.`, 'success');
                }, 1500);
            }

            function descargarQR(aula) {
                showToast('Descarga iniciada', `Preparando QR del aula ${aula} para descarga...`, 'success');
                
                // Crear enlace de descarga
                const link = document.createElement('a');
                link.href = `https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=https://gama.edu.mx/asistencia/${aula}&format=png`;
                link.download = `QR_${aula}.png`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            // ========================================
            // Modal de Regeneración
            // ========================================
            document.getElementById('modalRegenerarClose').addEventListener('click', () => closeModal(modalRegenerar));
            document.getElementById('btnCancelarRegenerar').addEventListener('click', () => closeModal(modalRegenerar));
            
            document.getElementById('btnConfirmarRegenerar').addEventListener('click', function() {
                if (!currentAulaToRegenerate) return;

                const { btn, aula } = currentAulaToRegenerate;
                closeModal(modalRegenerar);

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Regenerando...';

                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-sync-alt"></i> Regenerar';

                    // Actualizar imagen QR con timestamp para forzar recarga
                    const card = btn.closest('.qr-card');
                    const img = card.querySelector('.qr-preview img');
                    if (img) {
                        img.src = `https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=https://gama.edu.mx/asistencia/${aula}&t=${Date.now()}&format=svg`;
                    }

                    showToast('QR regenerado', `El código QR del aula ${aula} ha sido regenerado exitosamente.`, 'success');
                    currentAulaToRegenerate = null;
                }, 1500);
            });

            modalRegenerar.addEventListener('click', function(e) {
                if (e.target === modalRegenerar) closeModal(modalRegenerar);
            });

            // ========================================
            // Acciones Masivas
            // ========================================
            btnGenerarQRBulk.addEventListener('click', function() {
                const selectedCards = qrGallery.querySelectorAll('.qr-card.selected');
                const pendientes = Array.from(selectedCards).filter(card => 
                    card.querySelector('.badge-pendiente')
                );

                if (pendientes.length === 0) {
                    showToast('Sin aulas pendientes', 'Todas las aulas seleccionadas ya tienen QR generado.', 'warning');
                    return;
                }

                btnGenerarQRBulk.disabled = true;
                btnGenerarQRBulk.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';

                setTimeout(() => {
                    pendientes.forEach(card => {
                        const aula = card.dataset.aula;
                        const badge = card.querySelector('.badge');
                        badge.className = 'badge badge-activo';
                        badge.innerHTML = '<i class="fas fa-check-circle"></i> Activo';

                        const qrPreview = card.querySelector('.qr-preview');
                        qrPreview.classList.add('has-qr');
                        qrPreview.innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=https://gama.edu.mx/asistencia/${aula}&format=svg" alt="QR Aula ${aula}">`;

                        const actionsDiv = card.querySelector('.qr-card-actions');
                        actionsDiv.innerHTML = `
                            <button class="btn btn-outline btn-sm" title="Regenerar QR" data-action="regenerar" data-aula="${aula}">
                                <i class="fas fa-sync-alt"></i>
                                Regenerar
                            </button>
                            <button class="btn btn-secondary btn-sm" title="Descargar QR" data-action="descargar" data-aula="${aula}">
                                <i class="fas fa-download"></i>
                                Descargar
                            </button>
                        `;
                    });

                    btnGenerarQRBulk.disabled = false;
                    btnGenerarQRBulk.innerHTML = '<i class="fas fa-qrcode"></i> Generar QR';

                    showToast('QR generados exitosamente', `Se han generado ${pendientes.length} códigos QR.`, 'success');
                }, 2000);
            });

            btnDescargarZIP.addEventListener('click', function() {
                const selectedCards = qrGallery.querySelectorAll('.qr-card.selected');
                const activos = Array.from(selectedCards).filter(card => 
                    card.querySelector('.badge-activo')
                );

                if (activos.length === 0) {
                    showToast('Sin QR para descargar', 'Seleccione aulas con QR activo para descargar.', 'warning');
                    return;
                }

                btnDescargarZIP.disabled = true;
                btnDescargarZIP.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Preparando...';

                setTimeout(() => {
                    btnDescargarZIP.disabled = false;
                    btnDescargarZIP.innerHTML = '<i class="fas fa-download"></i> Descargar ZIP';

                    showToast('Descarga iniciada', `Se está descargando un ZIP con ${activos.length} códigos QR.`, 'success');
                    
                    // En producción, aquí se generaría el ZIP real con JSZip o similar
                }, 1500);
            });

            // ========================================
            // Imprimir
            // ========================================
            btnPrint.addEventListener('click', function() {
                const selectedCards = Array.from(qrGallery.querySelectorAll('.qr-card.selected'));
                const visibleCards = Array.from(qrGallery.querySelectorAll('.qr-card')).filter(card => {
                    const parent = card.closest('.qr-card');
                    return parent && parent.style.display !== 'none';
                });

                const cardsToPrint = selectedCards.length > 0 ? selectedCards : visibleCards;

                if (cardsToPrint.length === 0) {
                    showToast('Sin resultados', 'No hay aulas visibles para imprimir.', 'warning');
                    return;
                }

                printCards(cardsToPrint);
            });

            function printCards(cards) {
                 const printWindow = window.open('', '_blank', 'width=1000,height=700');
    if (!printWindow) {
        showToast('Bloqueo de ventana', 'Permita ventanas emergentes para imprimir.', 'error');
        return;
    }

    const cardsHtml = cards.map((card) => {
        const clone = card.cloneNode(true);

        // Eliminar cosas innecesarias
        const checkbox = clone.querySelector('.qr-card-checkbox');
        const actions = clone.querySelector('.qr-card-actions');

        if (checkbox) checkbox.remove();
        if (actions) actions.remove();

        // Obtener datos
        const titulo = clone.querySelector('.qr-card-title')?.textContent || '';
        const subtitulo = clone.querySelector('.qr-card-subtitle')?.textContent || '';

        return `
            <div class="print-item">
                <div class="print-header">
                    <h2>${titulo}</h2>
                    <p>${subtitulo}</p>
                </div>
                ${clone.querySelector('.qr-preview').outerHTML}
            </div>
        `;
    }).join('');

    printWindow.document.open();
    printWindow.document.write(`
        <!doctype html>
        <html lang="es">
        <head>
            <meta charset="utf-8">
            <title>Impresión QR</title>

            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 20px;
                }

                .print-title {
                    text-align: center;
                    margin-bottom: 20px;
                }

                .print-title h1 {
                    margin: 0;
                    font-size: 22px;
                }

                .print-grid {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 20px;
                }

                .print-item {
                    border: 1px solid #ccc;
                    padding: 15px;
                    text-align: center;
                    border-radius: 8px;
                }

                .print-header h2 {
                    margin: 0;
                    font-size: 18px;
                }

                .print-header p {
                    margin: 4px 0 10px;
                    font-size: 13px;
                    color: #555;
                }

                .qr-preview img {
                    width: 140px;
                    height: 140px;
                }

                @media print {
                    body {
                        margin: 10mm;
                    }

                    .print-grid {
                        grid-template-columns: repeat(2, 1fr);
                        gap: 12px;
                    }

                    .print-item {
                        break-inside: avoid;
                    }
                }
            </style>
        </head>
        <body>

            <div class="print-title">
                <h1>Generación de Códigos QR - GAMA</h1>
                <p>Horarios por Aula</p>
            </div>

            <div class="print-grid">
                ${cardsHtml}
            </div>

        </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.focus();

    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 300);
}

            // ========================================
            // Utilidades: Modal y Toast
            // ========================================
            function openModal(modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeModal(modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }

            function showToast(title, message, type = 'success') {
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                toast.innerHTML = `
                    <div class="toast-icon">
                        <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'exclamation'}"></i>
                    </div>
                    <div class="toast-content">
                        <div class="toast-title">${title}</div>
                        <div class="toast-message">${message}</div>
                    </div>
                    <button class="toast-close" aria-label="Cerrar notificación">
                        <i class="fas fa-times"></i>
                    </button>
                `;

                toastContainer.appendChild(toast);

                // Animación de entrada
                setTimeout(() => toast.classList.add('show'), 10);

                // Auto-cerrar
                const autoClose = setTimeout(() => removeToast(toast), 5000);

                // Cerrar manualmente
                toast.querySelector('.toast-close').addEventListener('click', () => {
                    clearTimeout(autoClose);
                    removeToast(toast);
                });
            }

            function removeToast(toast) {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }

            // Cerrar modal con Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (modalRegenerar.classList.contains('active')) {
                        closeModal(modalRegenerar);
                    }
                }
            });
        });
    </script>
@endsection
