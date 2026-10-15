# FastLogi — vistas Blade del MVP (generadas desde Stitch)

10 pantallas de Stitch convertidas a Blade (Tailwind), con layouts por rol.

## Mapa de archivos

| Stitch | Vista Blade | Layout | Ruta (nombre) |
|---|---|---|---|
| code5 — Login | `auth/login` | `guest` | `login` |
| code10 — SuperAdmin | `superadmin/dashboard` | `superadmin` | `superadmin.dashboard` |
| code — Dashboard dueño | `owner/dashboard` | `owner` | `owner.dashboard` |
| code8 — Pedidos + ofrecer | `owner/orders/index` | `owner` | `owner.orders.index` |
| code7 — Nuevo pedido | `owner/orders/create` | `owner` | `owner.orders.create` |
| code2 — Detalle pedido | `owner/orders/show` | `owner` | `owner.orders.show` |
| code6 — Inventario | `owner/inventory` | `owner` | `owner.inventory.index` |
| code4 — Repartidores | `owner/drivers` | `owner` | `owner.drivers.index` |
| code9 — Rutas de hoy | `driver/routes` | `driver` | `driver.routes` |
| code3 — Detalle entrega | `driver/order-detail` | `driver` | `driver.orders.show` |

## Instalación

1. Copia `resources/views/*` a tu proyecto Laravel 12.
2. Copia `routes/web.php.example` a tu `routes/web.php` (o fusiónalo) para ver las vistas con `Route::view`.
3. `php artisan serve` y abre `/login`, `/negocio`, `/superadmin`, `/repartidor/rutas`.

Por ahora Tailwind va por CDN con los tokens en `partials/head.blade.php`.
Para producción: instala Tailwind con Vite, usa `tailwind.config.js` (ya trae los tokens) y quita el CDN.

## Qué cambió respecto al HTML de Stitch

- Sidebar, barra superior y navegación inferior salieron a **layouts**; el ítem activo se calcula con `routeIs()`.
- Los links usan `Route::has()`: los módulos sin ruta todavía apuntan a `#` sin romper.
- Errata corregida en Rutas de hoy: el ícono de advertencia tenía `text-[22kpx]` (clase inválida), ahora `text-[22px]`.
- Config de Tailwind **unificada**: en las pantallas móviles (Rutas, Detalle entrega) faltaba `fontSize`, así que clases como `text-headline-sm` no aplicaban.
- Se quitó el andamiaje de demo de Stitch: selector "Modo Demo" del login, selector "Modo de Vista" del detalle de entrega y la sección "Vistas Previas de Módulos" del SuperAdmin (los modales reales se conservan).
- Login: formulario real (`POST`, `@csrf`, `name="identifier"` / `password`), errores por `$errors` y `session('suspended')`.
- Detalle de entrega: el estado cancelado se controla con `$cancelled` (bool).

## Pendiente (siguiente paso del MVP)

- Datos: todo sigue con texto de ejemplo; hay que reemplazarlo por datos reales (Livewire/controladores).
- Nombre del negocio en la barra superior (marcado con `TODO`).
- Logo del login: la imagen es una URL temporal de Stitch; descárgala a `public/`.
- Decisión de estilo: `DESIGN.md` pide esquinas rectas y sombras duras, pero el HTML usa `rounded-lg/xl` y `shadow-md`.
