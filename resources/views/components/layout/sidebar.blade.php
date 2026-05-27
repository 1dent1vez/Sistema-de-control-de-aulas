{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion     Vista de Sidebar y Toggle móvil
 * @autor           Rubén Alejandro Nolasco Ruiz
 * @autorizador     Rubén Alejandro Nolasco Ruiz
 * @prueba          Diego Miguel Hernandez Fabela  
 * @mantenimiento   Ghael Garcia Manjarrez 
 * @version       0.1.0
 * @creado        11/04/2026
 * @modificado    11/04/2026
 *
 * @cambios
 * Fecha       | Autor             | Descripción
 * ------------|-------------------|------------------------------------------
 * 03/04/2026  | Rubén Alejandro   | Implementación inicial de Sidebar y Toggle móvil.
 * 11/04/2026  | Rubén Alejandro   | Ajuste de estructura de prólogo según manual GAMA-MPL-03.
 */
--}}     
     
<style>
        /* ========================================
           GAMA DESIGN FRAMEWORK - Variables
        ======================================== */
        :root {
            /* Colores Primarios */
            --gama-azul-profundo: #134474;
            --gama-azul-intermedio: #1E5A8A;
            --gama-azul-claro: #E8F4FC;
            
            /* Colores de Acento */
            --gama-naranja: #F28B2C;
            --gama-naranja-hover: #D97A25;
            
            /* Colores de Estado */
            --gama-exito: #28A745;
            --gama-error: #DC3545;
            --gama-advertencia: #FFC107;
            --gama-info: #17A2B8;
            
            /* Neutrales */
            --gama-blanco: #FFFFFF;
            --gama-gris-100: #F8F9FA;
            --gama-gris-200: #E9ECEF;
            --gama-gris-300: #DEE2E6;
            --gama-gris-400: #CED4DA;
            --gama-gris-500: #6C757D;
            --gama-gris-600: #495057;
            --gama-gris-700: #343A40;
            --gama-negro: #212529;
            
            /* Tipografía */
            --font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            
            /* Espaciado */
            --spacing-xs: 4px;
            --spacing-sm: 8px;
            --spacing-md: 16px;
            --spacing-lg: 24px;
            --spacing-xl: 32px;
            --spacing-2xl: 48px;
            
            /* Bordes */
            --border-radius-sm: 4px;
            --border-radius-md: 8px;
            --border-radius-lg: 12px;
            
            /* Sombras */
            --shadow-sm: 0 1px 2px rgba(19, 68, 116, 0.05);
            --shadow-md: 0 4px 6px rgba(19, 68, 116, 0.07);
            --shadow-lg: 0 10px 15px rgba(19, 68, 116, 0.1);
            
            /* Transiciones */
            --transition-fast: 0.18s ease;
            --transition-normal: 0.3s ease;
            
            /* Sidebar */
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 70px;
        }

        /* ========================================
           Reset y Base
        ======================================== */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            font-size: 16px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--gama-gris-100);
            color: var(--gama-negro);
            line-height: 1.5;
            min-height: 100vh;
        }

        /* ========================================
           Layout Principal
        ======================================== */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* ========================================
           Sidebar
        ======================================== */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--gama-azul-profundo) 0%, var(--gama-azul-intermedio) 100%);
            color: var(--gama-blanco);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 40;
            transition: width var(--transition-normal), transform var(--transition-normal);
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-header {
            padding: var(--spacing-lg);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }

        .sidebar-logo {
            width: 40px;
            height: 40px;
            background: var(--gama-blanco);
            border-radius: var(--border-radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-logo i {
            color: var(--gama-azul-profundo);
            font-size: 1.25rem;
        }

        .sidebar-brand {
            overflow: hidden;
            transition: opacity var(--transition-fast);
        }

        .sidebar.collapsed .sidebar-brand {
            opacity: 0;
            width: 0;
        }

        .sidebar-brand h1 {
            font-size: 1.25rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .sidebar-brand span {
            font-size: 0.75rem;
            opacity: 0.7;
            display: block;
        }

        .sidebar-toggle {
            position: absolute;
            top: 28px;
            right: -12px;
            width: 24px;
            height: 24px;
            background: var(--gama-blanco);
            border: 2px solid var(--gama-gris-200);
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-fast);
            z-index: 41;
        }

        .sidebar-toggle:hover {
            background: var(--gama-naranja);
            border-color: var(--gama-naranja);
            color: var(--gama-blanco);
        }

        .sidebar-toggle i {
            font-size: 0.625rem;
            color: var(--gama-gris-600);
            transition: transform var(--transition-normal), color var(--transition-fast);
        }

        .sidebar-toggle:hover i {
            color: var(--gama-blanco);
        }

        .sidebar.collapsed .sidebar-toggle i {
            transform: rotate(180deg);
        }

        /* Navegación */
        .sidebar-nav {
            flex: 1;
            padding: var(--spacing-md) 0;
            overflow-y: auto;
        }

        .nav-section {
            margin-bottom: var(--spacing-lg);
        }

        .nav-section-title {
            padding: var(--spacing-sm) var(--spacing-lg);
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(255, 255, 255, 0.5);
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar.collapsed .nav-section-title {
            opacity: 0;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: var(--spacing-sm) var(--spacing-lg);
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all var(--transition-fast);
            gap: var(--spacing-md);
            position: relative;
            cursor: pointer;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--gama-blanco);
        }

        .nav-item.active {
            background: rgba(242, 139, 44, 0.2);
            color: var(--gama-naranja);
            border-left: 3px solid var(--gama-naranja);
        }

        .nav-item i {
            width: 20px;
            text-align: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .nav-item span {
            white-space: nowrap;
            overflow: hidden;
            transition: opacity var(--transition-fast);
        }

        .sidebar.collapsed .nav-item span {
            opacity: 0;
            width: 0;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            padding: var(--spacing-md) var(--spacing-lg);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            flex-shrink: 0;
        }

        .user-details {
            overflow: hidden;
            transition: opacity var(--transition-fast);
        }

        .sidebar.collapsed .user-details {
            opacity: 0;
            width: 0;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.875rem;
            white-space: nowrap;
        }

        .user-role {
            font-size: 0.75rem;
            opacity: 0.7;
            white-space: nowrap;
        }

        /* ========================================
           Contenido Principal
        ======================================== */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: margin-left var(--transition-normal);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Header */
        .main-header {
            background: var(--gama-blanco);
            padding: var(--spacing-md) var(--spacing-xl);
            border-bottom: 1px solid var(--gama-gris-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            font-size: 0.875rem;
            color: var(--gama-gris-500);
        }

        .breadcrumb a {
            color: var(--gama-gris-500);
            text-decoration: none;
            transition: color var(--transition-fast);
        }

        .breadcrumb a:hover {
            color: var(--gama-azul-profundo);
        }

        .breadcrumb .current {
            color: var(--gama-azul-profundo);
            font-weight: 500;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }

        .header-btn {
            width: 40px;
            height: 40px;
            border-radius: var(--border-radius-md);
            border: none;
            background: var(--gama-gris-100);
            color: var(--gama-gris-600);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-fast);
            position: relative;
        }

        .header-btn:hover {
            background: var(--gama-azul-claro);
            color: var(--gama-azul-profundo);
        }

        .notification-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            background: var(--gama-error);
            border-radius: 50%;
        }

        /* Page Content */
        .page-content {
            flex: 1;
            padding: var(--spacing-xl);
        }

        /* Page Header */
        .page-header {
            margin-bottom: var(--spacing-xl);
        }

        .page-title-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: var(--spacing-md);
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gama-azul-profundo);
        }

        .page-subtitle {
            color: var(--gama-gris-500);
            font-size: 0.9375rem;
            max-width: 600px;
        }

        /* ========================================
           Botones
        ======================================== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-sm) var(--spacing-md);
            font-family: var(--font-family);
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: var(--border-radius-md);
            border: none;
            cursor: pointer;
            transition: all var(--transition-fast);
            text-decoration: none;
            white-space: nowrap;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-sm {
            padding: var(--spacing-xs) var(--spacing-sm);
            font-size: 0.8125rem;
        }

        .btn-md {
            padding: var(--spacing-sm) var(--spacing-lg);
            font-size: 0.875rem;
        }

        .btn-lg {
            padding: var(--spacing-md) var(--spacing-xl);
            font-size: 1rem;
        }

        .btn-primary {
            background: var(--gama-azul-profundo);
            color: var(--gama-blanco);
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--gama-azul-intermedio);
        }

        .btn-secondary {
            background: var(--gama-azul-intermedio);
            color: var(--gama-blanco);
        }

        .btn-secondary:hover:not(:disabled) {
            background: var(--gama-azul-profundo);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--gama-gris-300);
            color: var(--gama-gris-600);
        }

        .btn-outline:hover:not(:disabled) {
            border-color: var(--gama-azul-profundo);
            color: var(--gama-azul-profundo);
            background: var(--gama-azul-claro);
        }

        .btn-danger {
            background: var(--gama-error);
            color: var(--gama-blanco);
        }

        .btn-danger:hover:not(:disabled) {
            background: #c82333;
        }

        .btn-ghost {
            background: transparent;
            color: var(--gama-gris-600);
        }

        .btn-ghost:hover:not(:disabled) {
            background: var(--gama-gris-100);
            color: var(--gama-azul-profundo);
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            padding: 0;
        }

        /* ========================================
           Toolbar / Filtros
        ======================================== */
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
            flex-wrap: wrap;
        }

        .toolbar-left {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            flex-wrap: wrap;
        }

        .toolbar-right {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        /* Filtro Select */
        .filter-group {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .filter-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gama-gris-600);
        }

        .filter-select {
            padding: var(--spacing-sm) var(--spacing-md);
            padding-right: var(--spacing-xl);
            font-family: var(--font-family);
            font-size: 0.875rem;
            border: 1px solid var(--gama-gris-300);
            border-radius: var(--border-radius-md);
            background: var(--gama-blanco);
            color: var(--gama-negro);
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236C757D' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            min-width: 180px;
            transition: all var(--transition-fast);
        }

        .filter-select:hover {
            border-color: var(--gama-azul-intermedio);
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--gama-azul-profundo);
            box-shadow: 0 0 0 3px rgba(19, 68, 116, 0.1);
        }

        /* Búsqueda */
        .search-box {
            position: relative;
        }

        .search-box input {
            padding: var(--spacing-sm) var(--spacing-md);
            padding-left: 40px;
            font-family: var(--font-family);
            font-size: 0.875rem;
            border: 1px solid var(--gama-gris-300);
            border-radius: var(--border-radius-md);
            background: var(--gama-blanco);
            min-width: 240px;
            transition: all var(--transition-fast);
        }

        .search-box input:hover {
            border-color: var(--gama-azul-intermedio);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--gama-azul-profundo);
            box-shadow: 0 0 0 3px rgba(19, 68, 116, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gama-gris-400);
        }

        /* ========================================
           Contador y Acciones Selección
        ======================================== */
        .selection-bar {
            display: none;
            align-items: center;
            justify-content: space-between;
            padding: var(--spacing-md) var(--spacing-lg);
            background: var(--gama-azul-claro);
            border-radius: var(--border-radius-md);
            margin-bottom: var(--spacing-lg);
            border: 1px solid var(--gama-azul-intermedio);
        }

        .selection-bar.visible {
            display: flex;
        }

        .selection-info {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }

        .selection-count {
            font-weight: 600;
            color: var(--gama-azul-profundo);
        }

        .selection-actions {
            display: flex;
            gap: var(--spacing-sm);
        }

        /* ========================================
           Cards Grid - Galería QR
        ======================================== */
        .qr-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: var(--spacing-lg);
        }

        .qr-card {
            background: var(--gama-blanco);
            border-radius: var(--border-radius-lg);
            border: 1px solid var(--gama-gris-200);
            overflow: hidden;
            transition: all var(--transition-fast);
            position: relative;
        }

        .qr-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .qr-card.selected {
            border-color: var(--gama-azul-profundo);
            box-shadow: 0 0 0 3px rgba(19, 68, 116, 0.15);
        }

        /* Checkbox de selección */
        .qr-card-checkbox {
            position: absolute;
            top: var(--spacing-md);
            left: var(--spacing-md);
            z-index: 10;
        }

        .qr-card-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: var(--gama-azul-profundo);
        }

        /* Header de la tarjeta */
        .qr-card-header {
            padding: var(--spacing-md) var(--spacing-lg);
            padding-left: calc(var(--spacing-lg) + 28px);
            background: var(--gama-gris-100);
            border-bottom: 1px solid var(--gama-gris-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .qr-card-title {
            font-weight: 600;
            font-size: 1rem;
            color: var(--gama-azul-profundo);
        }

        .qr-card-subtitle {
            font-size: 0.8125rem;
            color: var(--gama-gris-500);
        }

        /* Badge de estado */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-xs);
            padding: var(--spacing-xs) var(--spacing-sm);
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .badge-activo {
            background: rgba(30, 90, 138, 0.1);
            color: var(--gama-azul-intermedio);
        }

        .badge-pendiente {
            background: rgba(242, 139, 44, 0.1);
            color: var(--gama-naranja);
        }

        /* Contenido QR */
        .qr-card-body {
            padding: var(--spacing-lg);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .qr-preview {
            width: 160px;
            height: 160px;
            background: var(--gama-blanco);
            border: 2px dashed var(--gama-gris-300);
            border-radius: var(--border-radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: var(--spacing-md);
            position: relative;
        }

        .qr-preview.has-qr {
            border: 1px solid var(--gama-gris-200);
        }

        .qr-preview img {
            width: 140px;
            height: 140px;
            object-fit: contain;
        }

        .qr-preview-placeholder {
            text-align: center;
            color: var(--gama-gris-400);
        }

        .qr-preview-placeholder i {
            font-size: 2.5rem;
            margin-bottom: var(--spacing-sm);
        }

        .qr-preview-placeholder span {
            display: block;
            font-size: 0.8125rem;
        }

        .qr-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gama-gris-600);
            text-align: center;
        }

        /* Acciones de la tarjeta */
        .qr-card-actions {
            padding: var(--spacing-md) var(--spacing-lg);
            border-top: 1px solid var(--gama-gris-200);
            display: flex;
            gap: var(--spacing-sm);
            justify-content: center;
        }

        /* ========================================
           Estado Vacío
        ======================================== */
        .empty-state {
            text-align: center;
            padding: var(--spacing-2xl);
            color: var(--gama-gris-500);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--gama-gris-300);
            margin-bottom: var(--spacing-lg);
        }

        .empty-state h3 {
            font-size: 1.25rem;
            color: var(--gama-gris-600);
            margin-bottom: var(--spacing-sm);
        }

        .empty-state p {
            max-width: 400px;
            margin: 0 auto;
        }

        /* ========================================
           Modal
        ======================================== */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(33, 37, 41, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all var(--transition-normal);
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            background: var(--gama-blanco);
            border-radius: var(--border-radius-lg);
            width: 100%;
            max-width: 480px;
            max-height: 90vh;
            overflow: hidden;
            transform: scale(0.95) translateY(20px);
            transition: transform var(--transition-normal);
            box-shadow: var(--shadow-lg);
        }

        .modal-overlay.active .modal {
            transform: scale(1) translateY(0);
        }

        .modal-header {
            padding: var(--spacing-lg);
            border-bottom: 1px solid var(--gama-gris-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gama-azul-profundo);
        }

        .modal-close {
            width: 32px;
            height: 32px;
            border: none;
            background: transparent;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            color: var(--gama-gris-500);
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            background: var(--gama-gris-100);
            color: var(--gama-negro);
        }

        .modal-body {
            padding: var(--spacing-lg);
        }

        .modal-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto var(--spacing-lg);
            background: rgba(242, 139, 44, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-icon i {
            font-size: 1.75rem;
            color: var(--gama-naranja);
        }

        .modal-icon.danger {
            background: rgba(220, 53, 69, 0.1);
        }

        .modal-icon.danger i {
            color: var(--gama-error);
        }

        .modal-text {
            text-align: center;
            color: var(--gama-gris-600);
            margin-bottom: var(--spacing-lg);
            line-height: 1.6;
        }

        .modal-text strong {
            color: var(--gama-azul-profundo);
        }

        .modal-footer {
            padding: var(--spacing-lg);
            border-top: 1px solid var(--gama-gris-200);
            display: flex;
            justify-content: flex-end;
            gap: var(--spacing-sm);
        }

        /* ========================================
           Toast Notifications
        ======================================== */
        .toast-container {
            position: fixed;
            top: var(--spacing-lg);
            right: var(--spacing-lg);
            z-index: 3000;
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }

        .toast {
            background: var(--gama-blanco);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-lg);
            padding: var(--spacing-md) var(--spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            min-width: 320px;
            transform: translateX(120%);
            transition: transform var(--transition-normal);
            border-left: 4px solid var(--gama-exito);
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.error {
            border-left-color: var(--gama-error);
        }

        .toast.warning {
            border-left-color: var(--gama-advertencia);
        }

        .toast-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gama-exito);
            color: var(--gama-blanco);
            flex-shrink: 0;
        }

        .toast.error .toast-icon {
            background: var(--gama-error);
        }

        .toast.warning .toast-icon {
            background: var(--gama-advertencia);
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--gama-negro);
        }

        .toast-message {
            font-size: 0.8125rem;
            color: var(--gama-gris-500);
        }

        .toast-close {
            background: none;
            border: none;
            color: var(--gama-gris-400);
            cursor: pointer;
            padding: var(--spacing-xs);
            transition: color var(--transition-fast);
        }

        .toast-close:hover {
            color: var(--gama-negro);
        }

        /* ========================================
           Footer
        ======================================== */
        .main-footer {
            background: var(--gama-blanco);
            border-top: 1px solid var(--gama-gris-200);
            padding: var(--spacing-md) var(--spacing-xl);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8125rem;
            color: var(--gama-gris-500);
        }

        .footer-links {
            display: flex;
            gap: var(--spacing-lg);
        }

        .footer-links a {
            color: var(--gama-gris-500);
            text-decoration: none;
            transition: color var(--transition-fast);
        }

        .footer-links a:hover {
            color: var(--gama-azul-profundo);
        }

        /* ========================================
           Print Styles - Optimizado para impresión
        ======================================== */
        @media print {
            .sidebar,
            .main-header,
            .toolbar,
            .selection-bar,
            .qr-card-checkbox,
            .qr-card-actions,
            .main-footer,
            .toast-container {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
            }

            .page-content {
                padding: 0;
            }

            .qr-gallery {
                display: block;
            }

            .qr-card {
                page-break-inside: avoid;
                border: 2px solid #000;
                margin-bottom: 20mm;
            }

            .qr-card-header {
                background: #fff;
                padding-left: var(--spacing-lg);
            }

            .badge {
                border: 1px solid #000;
                background: transparent !important;
                color: #000 !important;
            }

            .qr-preview {
                border: none;
            }

            .qr-label {
                font-size: 14pt;
                font-weight: bold;
                color: #000;
            }
        }

        /* ========================================
           Mobile Menu Toggle Base
        ======================================== */
        .mobile-menu-toggle {
            display: none;
            position: relative;
            top: auto;
            left: auto;
            z-index: 50;
            width: 44px;
            height: 44px;
            border: none;
            background: transparent;
            border-radius: var(--border-radius-md);
            cursor: pointer;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .hamburger {
            width: 24px;
            height: 18px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .hamburger span {
            display: block;
            width: 100%;
            height: 2.5px;
            background: var(--gama-blanco);
            border-radius: 2px;
            transition: all var(--transition-fast);
        }

        .mobile-menu-toggle.active .hamburger span:nth-child(1) {
            transform: rotate(45deg) translate(5.5px, 5.5px);
        }

        .mobile-menu-toggle.active .hamburger span:nth-child(2) {
            opacity: 0;
        }

        .mobile-menu-toggle.active .hamburger span:nth-child(3) {
            transform: rotate(-45deg) translate(5.5px, -5.5px);
        }

        /* ========================================
           Responsive
        ======================================== */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .qr-gallery {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .toolbar-left,
            .toolbar-right {
                width: 100%;
                justify-content: space-between;
            }

            .search-box input {
                width: 100%;
                min-width: auto;
            }

            .selection-bar {
                flex-direction: column;
                gap: var(--spacing-md);
            }

            .qr-gallery {
                grid-template-columns: 1fr;
            }

            .page-title-row {
                flex-direction: column;
                gap: var(--spacing-md);
            }
        }
    </style>
     
      <!-- Sidebar Overlay -->
      <div class="sidebar-overlay" id="sidebarOverlay"></div>

      <!-- Sidebar -->
      <aside class="sidebar" id="sidebar">
        <!-- Brand Header -->
        <div class="sidebar-brand">
          <div class="logo-icon">
            <img src="{{ asset('img/gama-logo.png') }}" alt="G.A.M.A Solutions">
          </div>
        </div>

        <!-- User Info -->
        <div class="sidebar-user">
          <div class="user-avatar" id="sidebarAvatar">US</div>
          <div class="user-info">
            <span class="user-name" id="sidebarName">Cargando...</span>
            <span class="user-role" id="sidebarRole">--</span>
          </div>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav">
          @php $isAdmin = Auth::user()?->role?->value === 'admin'; @endphp

          <!-- Principal -->
          <div class="nav-section">
            <span class="nav-label">Principal</span>
            @if (Auth::user()?->role?->value === 'admin')
              <a href="{{ route('admin.dashboard') }}" class="nav-item {{ (request()->routeIs('admin.dashboard') || (request()->routeIs('dashboard') && Auth::user()?->role?->value === 'admin')) ? 'active' : '' }}" data-tooltip="Dashboard Admin">
                <i class="fas fa-home nav-icon"></i>
                <span class="nav-text">Dashboard Admin</span>
              </a>
            @elseif (Auth::user()?->role?->value === 'teacher')
              <a href="{{ route('docente.dashboard') }}" class="nav-item {{ (request()->routeIs('docente.dashboard') || (request()->routeIs('dashboard') && Auth::user()?->role?->value === 'teacher')) ? 'active' : '' }}" data-tooltip="Dashboard Docente">
                <i class="fas fa-home nav-icon"></i>
                <span class="nav-text">Dashboard Docente</span>
              </a>
            @else
              <a href="{{ route('espera.rol') }}" class="nav-item active" data-tooltip="Espera de Rol">
                <i class="fas fa-user-shield nav-icon"></i>
                <span class="nav-text">Espera de Rol</span>
              </a>
            @endif
          </div>

          <!-- DOCENTE -->
          @if (Auth::user()?->role?->value === 'teacher')
          <div class="nav-section">
            <span class="nav-label">DOCENTE</span>
            <a href="{{ route('docente.estatus') }}" class="nav-item {{ request()->routeIs('docente.estatus') ? 'active' : '' }}" data-tooltip="Estatus Docente">
              <i class="fas fa-chalkboard-teacher nav-icon"></i>
              <span class="nav-text">Estatus Docente</span>
            </a>
          </div>
          @endif

          <!-- GESTIÓN ACADÉMICA -->
          @if ($isAdmin)
          <div class="nav-section">
            <span class="nav-label">GESTIÓN ACADÉMICA</span>
            <a href="{{ route('aulas') }}" class="nav-item {{ request()->routeIs('aulas') ? 'active' : '' }}" data-tooltip="Aulas">
              <i class="fas fa-school nav-icon"></i>
              <span class="nav-text">Aulas</span>
            </a>
            <a href="{{ route('codigosqr') }}" class="nav-item {{ request()->routeIs('codigosqr') ? 'active' : '' }}" data-tooltip="CodigosQR">
              <i class="fas fa-qrcode nav-icon"></i>
              <span class="nav-text">Códigos QR</span>
            </a>
            <a href="{{ route('edificios') }}" class="nav-item {{ request()->routeIs('edificios') ? 'active' : '' }}" data-tooltip="Edificios">
              <i class="fas fa-building nav-icon"></i>
              <span class="nav-text">Edificios</span>
            </a>
            <a href="{{ route('admin.teacher-absences.index') }}" class="nav-item {{ request()->routeIs('admin.teacher-absences.index') ? 'active' : '' }}" data-tooltip="Gestión de Ausencias">
              <i class="fas fa-user-clock nav-icon"></i>
              <span class="nav-text">Gestión de Ausencias</span>
            </a>
            <a href="{{ route('aulas.horario_publico') }}" class="nav-item {{ request()->routeIs('aulas.horario_publico') ? 'active' : '' }}" data-tooltip="Horario Público QR">
              <i class="fas fa-clock nav-icon"></i>
              <span class="nav-text">Horario Público QR</span>
            </a>
            <a href="{{ route('horarios.manual') }}" class="nav-item {{ request()->routeIs('horarios.manual') ? 'active' : '' }}" data-tooltip="Horarios Manuales">
              <i class="fas fa-calendar-alt nav-icon"></i>
              <span class="nav-text">Horarios Manuales</span>
            </a>
            <a href="{{ route('horarios.importar') }}" class="nav-item {{ request()->routeIs('horarios.importar') ? 'active' : '' }}" data-tooltip="Importar Horarios">
              <i class="fas fa-file-import nav-icon"></i>
              <span class="nav-text">Importar Horarios</span>
            </a>
            <a href="{{ route('horarios.semestres.index') }}" class="nav-item {{ request()->routeIs('horarios.semestres.index') ? 'active' : '' }}" data-tooltip="Semestres">
              <i class="fas fa-calendar-check nav-icon"></i>
              <span class="nav-text">Semestres</span>
            </a>
            <a href="{{ route('usuarios') }}" class="nav-item {{ request()->routeIs('usuarios') ? 'active' : '' }}" data-tooltip="Usuarios">
              <i class="fas fa-users nav-icon"></i>
              <span class="nav-text">Usuarios</span>
            </a>
          </div>
          @endif

          <!-- Configuración -->
          @if ($isAdmin)
          <div class="nav-section">
            <span class="nav-label">Configuración</span>
            <a href="{{ route('configuracion') }}" class="nav-item {{ request()->routeIs('configuracion') ? 'active' : '' }}" data-tooltip="Configuración">
              <i class="fas fa-cog nav-icon"></i>
              <span class="nav-text">Configuración</span>
            </a>
          </div>
          @endif
        </nav>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">

          <a
            href="#"
            class="nav-item logout"
            id="logoutBtn"
            data-tooltip="Cerrar sesión"
          >
            <i class="fas fa-sign-out-alt nav-icon"></i>
            <span class="nav-text">Cerrar sesión</span>
          </a>
        </div>
      </aside>

      <script>
        // Mobile menu toggle
        const sidebar = document.getElementById("sidebar");
        const sidebarOverlay = document.getElementById("sidebarOverlay");
        const mobileMenuToggle = document.getElementById("mobileMenuToggle");

        mobileMenuToggle.addEventListener("click", () => {
          sidebar.classList.toggle("active");
          sidebarOverlay.classList.toggle("active");
          mobileMenuToggle.classList.toggle("active");
        });

        sidebarOverlay.addEventListener("click", () => {
          sidebar.classList.remove("active");
          sidebarOverlay.classList.remove("active");
          mobileMenuToggle.classList.remove("active");
        });

        // Close sidebar on link/nav-item click in mobile
        document.querySelectorAll(".sidebar a, .sidebar-footer a, .nav-item").forEach(function(item) {
          item.addEventListener("click", function() {
            if (window.innerWidth <= 1024) {
              sidebar.classList.remove("active");
              sidebarOverlay.classList.remove("active");
              if (mobileMenuToggle) {
                mobileMenuToggle.classList.remove("active");
              }
            }
          });
        });

        // Logout handler
        document.getElementById("logoutBtn").addEventListener("click", function(e) {
          e.preventDefault();
          var btn = this;
          btn.innerHTML = '<i class="fas fa-spinner fa-spin nav-icon"></i><span class="nav-text">Cerrando sesión...</span>';
          apiFetch('/api/v1/auth/logout', { method: 'POST' })
            .then(function() { clearSession(); window.location.href = '/'; })
            ['catch'](function() { clearSession(); window.location.href = '/'; });
        });

        // Load real user info
        apiFetch('/api/v1/auth/me')
          .then(function(res) {
            if (res && res.data) {
              var u = res.data;
              document.getElementById('sidebarName').textContent = u.fullName || u.externalId || 'Usuario';
              document.getElementById('sidebarRole').textContent = u.role ? u.role.charAt(0).toUpperCase() + u.role.slice(1) : '--';
              var initials = (u.fullName || u.externalId || 'U').substring(0, 2).toUpperCase();
              document.getElementById('sidebarAvatar').textContent = initials;
            }
          })
          ['catch'](function() {
            document.getElementById('sidebarName').textContent = 'Sesión';
            document.getElementById('sidebarRole').textContent = '--';
          });
      </script>