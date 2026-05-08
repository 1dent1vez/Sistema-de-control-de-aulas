<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>

    <link rel="stylesheet" href="{{ asset('css/gama-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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