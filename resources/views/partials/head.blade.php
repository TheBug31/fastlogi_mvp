<meta charset="utf-8"/>
<meta name="viewport" content="@yield('viewport', 'width=device-width, initial-scale=1.0')"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>@yield('title', 'FastLogi') · FastLogi</title>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Space+Grotesk:wght@100..900&display=swap" rel="stylesheet"/>
@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles
