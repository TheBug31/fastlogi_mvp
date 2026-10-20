# Bitácora de Desarrollo — FastLogi MVP

Registro cronológico de la construcción del MVP de **FastLogi** (Laravel 12 + Livewire v3 + MySQL 8),
organizado por fases. Cada fase se cerró con una *suite* de prueba en verde, formateo con Pint y dos
commits: el de la funcionalidad (`feat(...)`) y el de la documentación que fija su hash
(`docs(context): record F<x> phase commit hash`).

- **Documentación de referencia:** [`CONTEXT.md`](CONTEXT.md) · [`doc/SRS_FastLogi_v1_2.md`](doc/SRS_FastLogi_v1_2.md)
- **Instalación y ejecución:** [`README.md`](README.md)
- **Último commit:** `feaf194` (2026-10-15) · **Post-MVP sin commitear:** slugs
- **Estado:** ✅ MVP completo + slugs — 182 tests / 568 aserciones.

---

## Resumen de fases

| Fase | Tema | Commit feature | Tests | Aserciones |
| :--- | :--- | :--- | ---: | ---: |
| F0 | Scaffold y vistas de referencia | `968f335` | — | — |
| F1 | Migraciones, modelos, enums, factories, seeder y multi-tenant scope | `c592801` | 13 | 55 |
| F2 | Autenticación, roles y middleware | `bfa850d` | 30 | 127 |
| F3 | SuperAdmin: negocios, suspensión y ventana de gracia | `b2c0b5e` | 54 | 205 |
| F4 | Inventario y alertas de stock persistentes | `a7618bc` | 74 | 260 |
| F5 | Pedidos, concurrencia pesimista y máquina de estados | `84ffd31` | 110 | 379 |
| F6 | Panel del repartidor, escaneos, GPS e inactividad | `d78766c` | 137 | 443 |
| F7 | Dashboard del dueño, notificaciones y timeline | `96ff606` | 162 | 523 |
| F8 | Reportes en PDF y cierre (README) | `d89b967` | 169 | 548 |

Commits de documentación asociados:
`bb5814e` (F2), `2e694bd` (F3), `d31c908` (F4), `852a817` (F5), `ebee60e` (F6), `632554e` (F7), `feaf194` (F8).

---

## Bitácora cronológica

### [F0] Scaffold y vistas de referencia — `968f335`
- Repositorio Git inicializado con `doc/` resguardado.
- Bases de datos `fastlogi` (dev) y `fastlogi_test` (tests) en `utf8mb4_unicode_ci`.
- Laravel 12 + Livewire v3.8 + Tailwind + Vite + Pest; layouts y vistas de referencia (Stitch) integrados.

### [F1] Base de datos y multi-tenant — `c592801`
- Enums PHP 8.4 (`UserRole`, `UserStatus`, `BusinessStatus`, `DriverAvailability`, `OrderStatus`, `PackageScanType`, `PackageStatus`, `InventoryMovementType`).
- 10 migraciones de entidades + tabla `notifications`.
- Trait `BelongsToBusiness` + `BusinessScope` para aislamiento por `business_id`.
- 10 factories y `DatabaseSeeder` (SuperAdmin, 2 comercios, 6 repartidores, 15 productos, pedidos en varios estados).
- **13 tests / 55 aserciones.**

### [F2] Autenticación y roles — `bfa850d` · docs `bb5814e`
- `RoleMiddleware` (invitados → login; rol cruzado → 403; cuenta/negocio suspendido → logout + flash).
- Login único por correo **o** teléfono con redirección por rol; reglas centralizadas en `User::canLogin()`.
- **30 tests / 127 aserciones.**

### [F3] SuperAdmin: negocios, suspensión y gracia — `b2c0b5e` · docs `2e694bd`
- `BusinessService::createWithOwner()` (alta atómica negocio + dueño).
- `BusinessSuspensionService::suspend()/reactivate()/closeGraceOrders()`; ventana de gracia de 24 h.
- Job `CloseGraceOrders` + schedule `close-expired-grace-windows`.
- Livewire `Superadmin\Dashboard` (KPIs, tabla, alta y suspensión) y notificación `OrderCancelled`.
- **54 tests / 205 aserciones.**

### [F4] Inventario y alertas — `a7618bc` · docs `d31c908`
- `InventoryService` con `lockForUpdate()` en orden ascendente de `product_id` y bitácora en `inventory_movements`.
- Alertas de stock bajo y agotado con rearme (`low_stock_alerted`, migración `000012` añade `out_of_stock_alerted`).
- Notificación `LowStockAlert` (database + mail) despachada fuera de la transacción.
- Livewire `Owner\Inventory`; excepciones `InsufficientStockException`, `ProductInUseException`.
- **74 tests / 260 aserciones.**

### [F5] Pedidos y concurrencia — `84ffd31` · docs `852a817`
- `OrderService` con la matriz de transiciones de §4.1.3 y bitácora en `order_status_logs`.
- Ofertas con ventana de 90 s: job `ExpireOrderOffer` + barrido `expire-order-offers`.
- `DriverAvailabilityService` (capacidad = `offered + assigned + in_transit`).
- Livewire `Owner\Orders` (KPIs, acciones y modal de detalle con timeline).
- Prueba de concurrencia con **dos conexiones MySQL independientes**.
- **110 tests / 379 aserciones.**

### [F6] Panel del repartidor, escaneos y GPS — `d78766c` · docs `ebee60e`
- `PackageScanService` (recogida `assigned → in_transit`; entrega `in_transit → delivered`), idempotente por tipo.
- `DriverTelemetryService` + job `DetectInactiveDrivers` + schedule `detect-inactive-drivers`; notificación `DriverInactive`.
- Endpoint `POST /repartidor/telemetria`; Livewire `Driver\Routes` y `Driver\OrderDetail`.
- **137 tests / 443 aserciones.**

### [F7] Dashboard del dueño, notificaciones y flota — `96ff606` · docs `632554e`
- `DriverManagementService` (alta/edición de repartidor, capacidad 1..8, disponibilidad manual).
- Centro de notificaciones: `Owner\NotificationBell` (polling 10 s) + `Owner\Notifications` + `NotificationPresenter`.
- `Owner\Dashboard` (KPIs del día, pedidos en curso, flota y timeline; refresco 15 s) y `Owner\Drivers`.
- Seeder con 3 notificaciones de demostración.
- **162 tests / 523 aserciones.**

### [F8] Reportes PDF y cierre — `d89b967` · docs `feaf194`
- `ReportService` + `Owner\ReportController` (RF-009): PDF de pedidos y de repartidores con filtros.
- Vistas `reports/orders.blade.php` y `reports/drivers.blade.php` (dompdf, DejaVu Sans).
- Livewire `Owner\Reports` (`owner.reports.index`) con filtros, KPIs y descargas.
- `README.md` reescrito (instalación, ejecución, credenciales, mapa RF → archivos).
- **169 tests / 548 aserciones.**

### [Post-MVP] Slugs con generación automática — sin commitear
- Trait `HasSlug` (fuente configurable, sufijo ante colisión, unicidad global o por tenant,
  `getRouteKeyName() === 'slug'`, normalización de enums).
- Migraciones `000013` (núcleo: negocios, usuarios, repartidores, productos, pedidos, paquetes) y
  `000014` (bitácora: order_items, inventory_movements, package_scans, order_status_logs).
- Rutas por slug en pedidos con `Order::whereKeyOrSlug()` (slugs + retrocompatibilidad con el id);
  `Owner\Dashboard`, `Driver\Routes` y `NotificationPresenter` enlazan por modelo (slug).
- Comando `php artisan slugs:backfill` (no destructivo, idempotente) + backfill aplicado en dev.
- `tests/Feature/HasSlugTest.php` (13 pruebas).
- **182 tests / 568 aserciones.**

### [UI] Rediseño visual: base y login (U0–U2) — `ca309f9`, `171f0f0`
- Fuente de verdad `doc/UI_GUIDE.md`; se descarta `doc/fastlogi-views/` (Stitch). Solo capa visual:
  rutas, Livewire, `wire:*`, campos y mensajes de validación intactos.
- **U1 `ca309f9`** (50 archivos): tokens en `tailwind.config.js` (`canvas`, `sidebar`, `sidebar-active`,
  Inter), `app.css`, `partials/head.blade.php`, helpers `format_cop`/`format_datetime`/`format_date`/
  `format_time` (`autoload.files`), 24 componentes `<x-ui.*>` + `<x-icon>`, 5 layouts, marca en
  `public/images/brand/`, `NotificationPresenter` adaptado, `welcome.blade.php` eliminado,
  `blade-ui-kit/blade-heroicons` con registro de `blade-icons` desactivado.
- **U2 `171f0f0`** (9 archivos): `auth/login.blade.php` sobre el layout `guest` (panel de marca +
  formulario con mostrar/ocultar contraseña, "Recordar sesión" y alertas roja/ámbar de la guía); fuera
  el modo demo, el logo remoto, la telemetría inventada y los enlaces muertos.
- Arreglos de componentes: `x-ui.alert` con un arreglo PHP sin cerrar; `status-badge`/
  `availability-badge` aceptan el enum del modelo; `x-ui.input` aplica `type`, soporta slot `suffix` e
  `invalid`/`show-error` y usa `$errors->has()` (`MessageBag::first()` devuelve `''`); nuevo
  `x-ui.checkbox` que envía `value="1"` (el checkbox roto impedía iniciar sesión con "Recordar
  sesión"); `x-icon` propaga atributos extra.
- Verificación: render de humo de los 24 componentes + `npm run build` + `php artisan test`.
- **182 tests / 568 aserciones.** Ninguna aserción existente tuvo que cambiarse.

---

### [UI] Rediseño visual: pedidos del dueño (U3) — `7a46ac4`
- **U3 `7a46ac4`** (3 archivos): `livewire/owner/orders.blade.php` reconstruido con el sistema de diseño
  (cabecera + "Nuevo pedido", aviso ámbar de stock bajo con enlace "Ver inventario", tarjeta con pestañas
  y contadores, barra de filtros, tabla de 7 columnas y paginación con vista propia
  `partials/pagination.blade.php`). Se retiran los 4 KPIs (viven en el dashboard) y las acciones de la
  tabla siguen la guía (Ofrecer / "Responde en 0:54" + Retirar / Ver detalle / motivo del cancelado /
  Confirmar devolución); Cancelar, Reasignar y Registrar incidencia pasan al drawer de detalle.
- `Owner\Orders::render()` solo suma dos datos de lectura (`statusCounts`, `actionOrder`); props públicas,
  métodos y validaciones intactos. Sin cambios en rutas, modelos, servicios ni tests.
- Verificación: `npm run build` + `php artisan test` (**182 passed / 568 assertions**) y volcado del HTML
  real de la tabla, el drawer y los cuatro modales.

### [UI] Rediseño visual: resto del panel del dueño (U4) — `1294d16`
- **U4 `1294d16`** (9 archivos): dashboard, inventario, repartidores, reportes y centro de notificaciones
  reconstruidos con `<x-ui.*>`. Dashboard con los KPIs de la guía, tabla "Pedidos en curso", columnas
  "Flota" y "Alertas de inventario" y timeline "Actividad reciente". Inventario con aviso "Atención de
  inventario", filtros, tabla de 7 columnas y modal de producto. Repartidores con alerta roja "Sin
  conexión con pedidos asignados", tabla (capacidad, última conexión en ámbar tras el umbral) y modales de
  alta/edición con slider 1–8 y contraseña con mostrar/ocultar. Reportes reestilizado (filtros, KPIs,
  vista previa) y centro de notificaciones con pestañas con contadores y acciones por fila.
- Cambios de apoyo (todos de solo lectura o de componente): `Owner\Dashboard::render()` suma
  `promedioEntrega` y `lowStockProducts`; `x-ui.button` soporta `href` (renderiza `<a>`); `x-ui.badge`
  suma el color `teal`; `partials/pagination.blade.php` acepta un `label` (por defecto "pedidos").
- Se conservan textos fijados por tests ("Panel de Operaciones", "Pedidos en curso", "Flota",
  "Actividad reciente", "Atención de inventario", "Reportes", "Descargar pedidos (PDF)") y se
  reestilizan las pantallas que la guía no lista (Reportes, Notificaciones). Desviaciones documentadas
  en `CONTEXT.md` §8.4.
- Verificación: `npm run build` + `php artisan test` (**182 passed / 568 assertions**); barrido de
  patrones prohibidos y de tokens antiguos en las 5 vistas = 0; iconos verificados contra el set outline.

### [UI] Rediseño visual: SuperAdmin (U5) — `bd9e7da` · docs `5f66b23`
- **U5 `bd9e7da`** (1 archivo): `livewire/superadmin/dashboard.blade.php` reconstruido con el sistema de
  diseño: 4 KPIs (`x-ui.kpi`), chips de filtro (Todos / Activos / Suspendidos con contadores), búsqueda
  en vivo, tabla con avatar de iniciales y pedidos en gracia con badge ámbar + tiempo restante y botón
  "Cerrar gracia". Modales "Crear Nuevo Negocio" y "Confirmar Suspensión" con checkbox obligatorio.
- Modales usan el prop `property` para enlazar con Alpine/Livewire.
- Verificación: `npm run build` + `php artisan test` (**182 passed / 568 assertions**).

### [UI] Rediseño visual: repartidor (U6) — `a6dac14` · docs `52794c7`
- **U6 `a6dac14`** (3 archivos): `routes.blade.php` y `order-detail.blade.php` reconstruidos con `<x-ui.*>`.
  - Rutas: aviso ámbar de ubicación solo con `in_transit` y `last_seen_at > 3m`, tarjeta de oferta destacada
    con borde `border-2 border-teal-600` y anillo SVG de 64 px con cuenta regresiva en vivo sincronizada con
    servidor + Alpine, lista de "Mis pedidos" y tarjeta sticky "Tu día" con barra de capacidad.
  - Detalle: migas, cabecera de rastreo, 2 columnas (cliente con botón de llamada, manifiesto de paquetes
    con checklist, trazabilidad vertical con línea `border-l`; derecha sticky con pasos Asignado → En camino →
    Entregado, botón Registrar recogida / Marcar como entregado y Reportar incidencia con modal).
- `Driver\Routes::render()` suma solo el dato de lectura `deliveredToday`.
- Verificación: `npm run build` + `php artisan test` (**182 passed / 568 assertions**).

### [UI] Rediseño visual: QA visual y auditoría (U7)
- Barridos de patrones prohibidos:
  - `text-[9|10|11]px` = 0 coincidencias.
  - `cdn.tailwindcss` = 0 coincidencias.
  - `googleusercontent` = 0 coincidencias.
  - `brightness-0` = 0 coincidencias.
  - `min-w-[1440` = 0 coincidencias.
  - Palabras prohibidas (`SOS`, `Rastrear`, `ETA`, `comprobante`, `Datáfono`/`Datafono`, `Reordenar`, `Lorem`) = 0 coincidencias.
  - Hexadecimales en vistas = 0 (salvo dompdf en `reports/orders.blade.php` y `reports/drivers.blade.php`).
- Consistencia de botones (`bg-teal-700` con texto blanco), tipografía Inter y helpers `format_cop()` / `format_datetime()`.
- Verificación: `npm run build`, `view:clear`, `icons:clear`, `vendor/bin/pint` y suite completa: **182 tests, 568 aserciones en verde**.

## Verificación final (2026-10-15)

Comprobación real sobre `feaf194`:

1. **Suite:** `php artisan test` → 169 tests / 548 aserciones en verde.
2. **Base de datos:** `php artisan migrate:fresh --seed` → 9 usuarios + 3 notificaciones de demo.
3. **HTTP de extremo a extremo** (`php artisan serve`, login real con cookies):

   | Flujo | Resultado |
   | :--- | :--- |
   | `GET /login` | 200 |
   | Dueño → `/negocio` | 200 · "Panel de Operaciones" |
   | Dueño → `/negocio/repartidores` | 200 · "Gestión de Repartidores" |
   | Dueño → `/negocio/reportes` | 200 · "Descargar pedidos" |
   | Dueño → `/negocio/notificaciones` | 200 · "Centro de Notificaciones" |
   | PDF pedidos / repartidores | 200 · `application/pdf`, `attachment`, ~880 KB |
   | SuperAdmin → `/superadmin` | 200 · "Negocios" |
   | Repartidor → `/repartidor/rutas` | 200 · "Estado de flota" |

---

## Incidencias y resoluciones

| # | Fecha | Incidencia | Causa | Resolución |
| :-- | :--- | :--- | :--- | :--- |
| 1 | 2026-10-15 | Badges de F7/F8 sin color en el navegador | CSS de Tailwind compilado antes de añadir las vistas; las utilidades nuevas no estaban en `public/build` (los tests no lo detectan) | `npm run build`; verificado que todas las clases y tokens del tema están en el CSS |

---

## Convenciones del proyecto

- **Commits por fase:** `feat(<área>): <descripción> (F<n>)` y luego `docs(context): record F<n> phase commit hash`.
- **Antes de avanzar de fase:** `php artisan migrate:fresh --seed` y `php artisan test` (nunca avanzar con tests en rojo).
- **Formateo:** `vendor/bin/pint` sólo sobre los archivos tocados.
- **Pruebas:** MySQL (`fastlogi_test`), nunca SQLite en memoria; la concurrencia se prueba con dos conexiones reales.
- **Documentación:** `CONTEXT.md` se actualiza al cerrar cada fase; este `LOG.md` es la bitácora cronológica.
