<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') — GAMA</title>

    @vite(['resources/css/app.css', 'resources/css/gama-dashboard.css', 'resources/css/sidebar.css', 'resources/css/footer.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="{{ asset('js/api.js') }}"></script>
   @stack('styles')
</head>
<body>

    @include('components.layout.sidebar')

    <div class="dashboard">
        <main>
            @yield('content')
        </main>
    </div>

    @include('components.layout.footer')

</body>
</html>