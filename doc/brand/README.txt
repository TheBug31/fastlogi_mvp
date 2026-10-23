FastLogi - assets de marca (PNG con fondo transparente)

logo-horizontal.png       Logo + nombre, para fondos claros (login, navbar del repartidor, PDFs)
logo-horizontal-dark.png  Logo + nombre en blanco, para el sidebar oscuro (#0B1220)
logo-symbol.png           Simbolo con F azul marino sobre teal (512x512)
logo-symbol-white.png     Simbolo con F blanca sobre teal (512x512), version de app icon
favicon.ico + favicon-16/32/48/180/192/512.png  Favicons (180 = apple-touch-icon, 192/512 = PWA)

Laravel: copiar la carpeta a public/images/brand/ y usar asset('images/brand/logo-horizontal.png').
Favicon en el <head>:
  <link rel="icon" href="{{ asset('images/brand/favicon.ico') }}" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/brand/favicon-32.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('images/brand/favicon-180.png') }}">
