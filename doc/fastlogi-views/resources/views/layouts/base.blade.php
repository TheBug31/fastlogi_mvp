<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.head')
</head>
<body class="@yield('body_class', 'bg-surface font-body-md text-on-surface antialiased')">
    @yield('body')
    @stack('scripts')
</body>
</html>
