<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.head')
</head>
<body class="@yield('body_class', 'bg-canvas text-slate-900 antialiased')">
    @yield('body')
    @livewireScripts
    @stack('scripts')
</body>
</html>
