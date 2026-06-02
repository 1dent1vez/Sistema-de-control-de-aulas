<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') — GAMA</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="{{ asset('js/api.js') }}"></script>
   @stack('styles')
</head>
<body>

    <!-- Fixed Header (mobile) -->
    <style>
        .app-header {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 56px;
            background: #134474;
            z-index: 50;
            align-items: center;
            padding: 0 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        @media (max-width: 1024px) {
            .app-header {
                display: flex;
            }
            .dashboard {
                padding-top: 56px;
            }
        }
    </style>
    <header class="app-header" id="appHeader">
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </button>
    </header>

    @include('components.layout.sidebar')

    <div class="dashboard">
        <main>
            @yield('content')
        </main>
    </div>

    @include('components.layout.footer')

</body>
</html>