# FastLogi — Estado y Contexto del Proyecto

FastLogi es una plataforma SaaS multi-tenant diseñada para pequeños y medianos comercios en Colombia (restaurantes, calzado, minimarkets, etc.) para la gestión integrada de pedidos, inventario con bloqueo pesimista y logística de domicilios con trazabilidad y telemetría.

---

## 1. Información General y Stack Tecnológico

- **Framework:** Laravel 12.0
- **Frontend Reactivo & Componentes:** Livewire v3.8 + Alpine.js
- **Motor de Plantillas & Diseño:** Blade + Tailwind CSS v3.4.19 (vía PostCSS) con el sistema de diseño propio `<x-ui.*>` descrito en `doc/UI_GUIDE.md` (sustituye al tema de Stitch), tipografía Inter e iconos `blade-ui-kit/blade-heroicons` (outline) servidos por `<x-icon>`; assets compilados con Vite
- **Lenguaje:** PHP 8.4.0 (NTS Visual C++ 2019 x64)
- **Base de Datos:** MySQL 8.4.3 Community Server (`fastlogi` para desarrollo, `fastlogi_test` para pruebas)
- **Pruebas:** Pest PHP v3.8 + PHPUnit 11.5.56
- **Autenticación API & Web:** Laravel Sanctum v4.3 + Autenticación basada en sesiones web
- **Reportes:** `barryvdh/laravel-dompdf` v3.1
- **Colas y Tareas Programadas:** `QUEUE_CONNECTION=database` y Laravel Scheduler

---

## 2. Credenciales de Demostración y Accesos

| Rol | Correo / Identificador | Teléfono | Contraseña | Panel / Ruta Destino |
| :--- | :--- | :--- | :--- | :--- |
| **SuperAdmin** | `super@fastlogi.test` | `3000000000` | `superadmin` | `/superadmin` |
| **Dueño (Comida)** | `owner@fastlogi.test` | `3101234567` | `password123` | `/negocio` |
| **Dueña (Calzado)** | `calzado@fastlogi.test` | `3157891234` | `password123` | `/negocio` |
| **Repartidor 1 (Comida)** | `driver1@fastlogi.test` | `3101112233` | `driverpass` | `/repartidor/rutas` |
| **Repartidor 2 (Comida)** | `driver2@fastlogi.test` | `3102223344` | `driverpass` | `/repartidor/rutas` |
| **Repartidor 3 (Comida)** | `driver3@fastlogi.test` | `3103334455` | `driverpass` | `/repartidor/rutas` |
| **Repartidor 4 (Calzado)** | `driver4@fastlogi.test` | `3114445566` | `driverpass` | `/repartidor/rutas` |
| **Repartidor 5 (Calzado)** | `driver5@fastlogi.test` | `3115556677` | `driverpass` | `/repartidor/rutas` |
| **Repartidor 6 (Calzado)** | `driver6@fastlogi.test` | `3116667788` | `driverpass` | `/repartidor/rutas` |

---

## 3. Estado de las Fases del Proyecto

- [x] **Fase 0: Scaffold y Vistas de Referencia**
  - Repositorio Git inicializado con resguardo de `doc/`.
  - Bases de datos `fastlogi` y `fastlogi_test` configuradas con `utf8mb4_unicode_ci`.
  - Laravel 12 + Livewire v3.8 + Tailwind CSS + Vite + Pest instalados.
  - Vistas y layouts de Stitch integrados con Vite.
  - Commit: `968f335 feat(scaffold): initialize Laravel 12 with Livewire, Tailwind and Pest (F0)`
- [x] **Fase 1: Migraciones, Modelos, Enums, Factories, Seeder y Multi-Tenant Scope**
  - Enums de PHP 8.4 (`UserRole`, `UserStatus`, `BusinessStatus`, `DriverAvailability`, `OrderStatus`, etc.).
  - Migraciones para las 10 entidades del modelo de datos + notificaciones.
  - `BelongsToBusiness` trait + `BusinessScope` para aislamiento hermético por `business_id`.
  - 10 Factories y `DatabaseSeeder` con SuperAdmin, 2 comercios, 6 repartidores, 15 productos y pedidos en diversos estados.
  - Pruebas Pest en verde: 13 tests, 55 aserciones.
  - Commit: `c592801 feat(database): implement migrations, models, enums, factories, seeder and tenant scope (F1)`
- [x] **Fase 2: Auth, Roles y Middleware**
  - `RoleMiddleware` con verificación de estado de cuenta y de negocio suspendido, y autorización por rol.
  - Login único (`/login`) por correo o teléfono + contraseña, con redirección inteligente por rol.
  - Cuentas suspendidas/inactivas y negocios suspendidos se rechazan con flash `suspended` (RF-010, RF-003).
  - Reglas consolidadas en `User::canLogin()` (sin duplicación en el controlador).
  - Pruebas Pest en verde: 30 tests, 127 aserciones (`Auth/LoginTest`, `RoleMiddlewareTest`, scaffold actualizado).
  - Commit: `bfa850d`
- [x] **Fase 3: SuperAdmin (Negocios, Suspensión y Ventana de Gracia)**
  - `BusinessService::createWithOwner()`: alta atómica de negocio + usuario dueño en una sola transacción (RF-004).
  - `BusinessSuspensionService`: `suspend()` (bloquea al dueño, cancela `offered` liberando reserva, congela `pending`, deja gracia de 24 h a `assigned`/`in_transit`), `reactivate()` y `closeGraceOrders()` (RF-003, CU20).
  - `InventoryService::releaseForOrder()`: liberación de reserva con `lockForUpdate()` en orden ascendente de `product_id`, idempotente y con rearme de la alerta de stock.
  - Componente Livewire `Superadmin\Dashboard`: KPIs reales, tabla de negocios, filtros/búsqueda, modales de alta y suspensión, y acciones reactivar / "Cerrar pedidos en gracia".
  - Job `CloseGraceOrders` + tarea programada `close-expired-grace-windows` (cada minuto) que cierra las ventanas de gracia vencidas.
  - Notificación in-app `OrderCancelled` a los repartidores afectados y bitácora en `order_status_logs`.
  - Pruebas Pest en verde: 54 tests, 205 aserciones.
  - Commit: `b2c0b5e`
- [x] **Fase 4: Inventario y Alertas de Stock Persistentes**
  - `InventoryService` completo: `createProduct`, `updateProduct`, `restock`, `adjustStock`, `reserveForOrder`, `releaseForOrder`, `returnForOrder` y `deleteProduct`. Toda mutación de stock usa `DB::transaction` con `lockForUpdate()` en orden ascendente de `product_id` y registra un `inventory_movement`.
  - Regla de alertas (RF-008 §4.8.1): disparo único al cruzar el mínimo (`low_stock_alerted`), alerta adicional de agotado (`out_of_stock_alerted`, nueva columna) y rearme al volver a `stock >= min_stock`.
  - Notificación `LowStockAlert` (canales `database` + `mail`, encolada) enviada a los dueños del negocio fuera de la transacción de stock.
  - Componente Livewire `Owner\Inventory`: tabla con búsqueda y filtro por estado, banner de stock bajo, y alta/edición/eliminación de productos (la edición de stock genera un movimiento `adjustment` y evalúa alertas).
  - Excepciones de dominio `InsufficientStockException` y `ProductInUseException` (no se eliminan productos con pedidos asociados para no romper la auditoría).
  - Migración `2026_10_15_000012_add_out_of_stock_alerted_to_products_table`.
  - Pruebas Pest en verde: 74 tests, 260 aserciones.
  - Commit: `a7618bc`
- [x] **Fase 5: Pedidos, Concurrencia Pesimista y Máquina de Estados**
  - `OrderService`: `createOrder` (reserva de stock + paquete si hay trazabilidad, en una única transacción con reintentos), `offerToDriver`, `acceptOffer`, `rejectOffer`, `expireOffer`, `cancelOrder`, `reassignToPending`, `markInTransit`, `markDelivered`, `registerIncident` y `confirmDevolution`; matriz de transiciones de §4.1.3 en `canTransition()` y bitácora en `order_status_logs`.
  - `DriverAvailabilityService`: recalcula `available`/`busy` según pedidos `assigned`/`in_transit` y lista candidatos por capacidad (incluye ofertas pendientes, RF-005).
  - Ofertas con ventana de 90 s: job encolado `ExpireOrderOffer` + barrido del scheduler `expire-order-offers` como respaldo; la aceptación y la expiración son idempotentes bajo `lockForUpdate()` del pedido.
  - Notificaciones in-app `OrderOffered` (repartidor) y `OfferRejectedOrExpired` (dueño); la reserva devuelve las alertas y el llamador las despacha tras el commit (nunca dentro de la transacción).
  - Excepciones de dominio `InvalidOrderTransitionException`, `OfferNotAllowedException` y `BusinessSuspendedException`.
  - UI Livewire `Owner\Orders`: KPIs, filtros/búsqueda, tabla con acciones (ofrecer, cancelar, reasignar, incidencia, confirmar devolución) y modal de detalle con timeline; las rutas `owner.orders.index/create/show` reutilizan el componente.
  - Pruebas Pest en verde: 110 tests, 379 aserciones — incluye concurrencia con **dos conexiones MySQL independientes** (bloqueo de fila y rechazo de la segunda reserva de la última unidad).
  - Commit: `84ffd31`
- [x] **Fase 6: Panel del Repartidor, Escaneos, GPS e Inactividad**
  - `PackageScanService`: escaneo de recogida (`assigned → in_transit`, paquete `picked_up`) y de entrega (`in_transit → delivered`, paquete `delivered`), guardando lat/lng y `scanned_at` en `package_scans`; idempotente por tipo de escaneo y dentro de una transacción con `lockForUpdate()` del pedido y del paquete (RF-006, CU14, CU15).
  - `DriverTelemetryService`: `record()`/`touch()` actualizan lat/lng/`last_seen_at` y restauran la disponibilidad según la carga (un ping vuelve a `busy`/`available` superando un `offline` por inactividad); `inactiveDrivers()` detecta repartidores con pedidos `assigned`/`in_transit` cuyo `last_seen_at` supera el umbral del negocio (`inactivity_threshold_minutes`, default 15); `flagInactive()` marca `offline` y notifica una sola vez por episodio.
  - Job `DetectInactiveDrivers` + tarea programada `detect-inactive-drivers` (cada minuto) que marca y notifica; notificación in-app `DriverInactive` al dueño (RF-011, CU17).
  - Endpoint `POST /repartidor/telemetria` (`DriverTelemetryController`) que valida lat/lng y responde JSON; el panel del repartidor envía un ping cada 60 s mediante `navigator.geolocation` **solo cuando hay al menos un pedido `in_transit`**.
  - UI Livewire `Driver\Routes` (estado de flota, ofertas con cuenta atrás y aceptar/rechazar, pedidos en curso) y `Driver\OrderDetail` (barra de progreso, manifiesto, escaneos, escanear recogida/entrega, marcar entregado en pedidos sin trazabilidad y reportar incidencia). Las rutas `driver.routes`/`driver.orders.show` reutilizan los componentes y aíslan por `delivery_driver_id` (un repartidor solo ve sus pedidos).
  - Nota de alcance: RF-011 menciona un endpoint con token Sanctum; en el MVP se usa una ruta web autenticada por sesión (mismo panel web del repartidor) para no introducir emisión de tokens fuera de alcance.
  - Pruebas Pest en verde: 137 tests, 443 aserciones (`PackageScanServiceTest`, `DriverTelemetryServiceTest`, `DriverTelemetryEndpointTest`, `Livewire/DriverPanelTest`).
  - Commit: `d78766c`
- [x] **Fase 7: Dashboard de Dueño, Notificaciones y Timeline**
  - `DriverManagementService`: alta atómica de repartidor (usuario rol `driver` + perfil en una sola transacción), edición de perfil, ajuste del límite `max_active_orders` (rango 1..8) y conmutación manual `offline`/`available` recalculando `busy` según la carga real (RF-005, CU07).
  - `Support\NotificationPresenter`: centraliza icono, color y enlace profundo de las notificaciones in-app (`order_id` → detalle del pedido, `product_id` → inventario, `driver_id` → flota).
  - Centro de notificaciones persistente (RF-002 §4.2.1, RF-008): campana en el encabezado del panel (`Owner\NotificationBell`) con contador de no leídas y dropdown de recientes, más la página `Owner\Notifications` (`owner.notifications.index`) con filtros todas / no leídas / leídas, marcar leída y no leída, marcar todas como leídas y eliminar. Ambos consultan mediante polling cada 10 s.
  - Dashboard del dueño `Owner\Dashboard` (RF-006): KPIs del día (ventas, pedidos, entregados, flota disponible), alerta de stock bajo, pedidos en curso con su repartidor, panel de flota con capacidad y última señal, y timeline de actividad reciente alimentado por `order_status_logs`; refresco cada 15 s.
  - Gestión de flota `Owner\Drivers` (`owner.drivers.index`): KPIs por disponibilidad, búsqueda y filtro por estado, alta y edición de repartidores y conmutación de disponibilidad. La ruta `owner.notifications.index` se añade al panel.
  - Nota de alcance: el timeline de escaneos por pedido (RF-006) ya vivía en el modal de detalle de `Owner\Orders`; en F7 se complementa con el feed de actividad a nivel de negocio y se mantiene el polling de 10 s como mecanismo de notificación (sin push/WebSocket).
  - `DatabaseSeeder`: 3 notificaciones in-app de demostración para el dueño principal (stock bajo, agotado y oferta expirada).
  - Pruebas Pest en verde: 162 tests, 523 aserciones (`DriverManagementServiceTest`, `Livewire/OwnerDashboardTest`, `Livewire/NotificationCenterTest`, `Livewire/OwnerDriversTest`).
  - Commit: `96ff606`
- [x] **Fase 8: Reportes en PDF y Cierre**
  - `ReportService` (RF-009): construye el conjunto de datos de los reportes de pedidos (rango de fechas, estado, repartidor y método de pago) y de repartidores (entregas y montos dentro del periodo). Todas las consultas pasan por los modelos con `BusinessScope`, por lo que un reporte nunca filtra datos de otro tenant (RF-007).
  - `Owner\ReportController` (RF-009): endpoints `GET /negocio/reportes/pedidos/pdf` (`owner.reports.orders`) y `GET /negocio/reportes/repartidores/pdf` (`owner.reports.drivers`), que validan los filtros (rango de fechas coherente, `estado` en el enum o `todos`) y generan el PDF con **dompdf** (`Pdf::loadView(...)`).
  - Vistas PDF `resources/views/reports/orders.blade.php` y `reports/drivers.blade.php`: encabezado con negocio/periodo/estado/pago y fecha de generación, tabla de datos y pie con totales (fuente DejaVu Sans para acentos).
  - Página `Owner\Reports` (`owner.reports.index`, enlazada desde el menú): filtros en vivo, KPIs del periodo (pedidos, valor total, repartidores), vista previa de los primeros 10 pedidos y botones de descarga de ambos PDF. Se activa la ruta `owner.reports.*` que antes aparecía deshabilitada en el layout.
  - Cierre: `README.md` reescrito con descripción, stack, requisitos, instalación paso a paso, ejecución (servidor + `queue:work` + `schedule:work`), tareas programadas, credenciales de demostración, pruebas y mapa RF → archivos, además de las desviaciones de alcance (Tailwind en lugar de Bootstrap; telemetría por sesión web en lugar de token Sanctum).
  - Pruebas Pest en verde: **169 tests, 548 aserciones** (`ReportsPdfTest` añade 7 pruebas: aislamiento por negocio, filtros de estado/fecha, agregados de repartidores, descarga real de ambos PDF, rango inválido rechazado y control de acceso por rol).
  - Commit: `d89b967`

---

## 4. Reglas Críticas del Negocio (Fuentes de Verdad: SRS v1.2)

1. **Aislamiento Multi-Tenant (RF-007):** Toda consulta de orders, products y delivery_drivers filtra por el `business_id` del usuario autenticado; el SuperAdmin tiene acceso global para tareas administrativas.
2. **Reserva Concurrente de Stock (RF-001):** `DB::transaction(..., 3)` con `lockForUpdate()` sobre los productos en orden ascendente de `product_id`. Sin side-effects dentro de la transacción. El stock nunca es negativo.
3. **Máquina de Estados de Pedidos (RF-001 §4.1.3):** 7 estados (`pending`, `offered`, `assigned`, `in_transit`, `delivered`, `cancelled`, `incident`). Un pedido `in_transit` nunca se cancela; transiciona a `delivered` o `incident`.
4. **Capacidad y Ofertas (RF-002, RF-005):** Ofertas vencen en 90 s (`offer_expires_at`). Las ofertas cuentan en la capacidad activa del repartidor (`offered + assigned + in_transit`). Un repartidor con pedidos activos $\ge$ `max_active_orders` o desconectado (`offline`) no es elegible.
5. **Alertas de Stock Bajo (RF-008):** Se dispara una única vez al cruzar el mínimo (`stock_ant >= min && stock_nuevo < min`). Se rearma cuando `stock >= min`. Alerta adicional de "agotado" al llegar a 0.
6. **Suspensión de Negocio (RF-003):** Bloqueo inmediato del login del dueño. Pedidos `offered` se cancelan (liberan stock); `pending` se congelan; `assigned`/`in_transit` tienen 24 horas de gracia para entrega o incidencia.
7. **Telemetría y Detección de Inactividad (RF-011):** Repartidor envía GPS cada 60 s sólo con pedidos `in_transit`. Scheduler detecta inactividad tras `inactivity_threshold_minutes` (default 15 min), pasa a `offline` y notifica al dueño.

---

## 5. Fuera de Alcance (Decisiones del MVP)

- Variantes de producto y catálogos jerárquicos.
- Mapa en vivo / seguimiento en tiempo real (solo telemetría GPS persistida y última posición).
- Notificaciones push y WebSockets: se sustituyen por notificaciones in-app persistentes con *polling* (10 s) y correo (RF-002 §4.2.1).
- Facturación / cobros en línea y pasarelas de pago.
- Aplicación nativa o PWA (solo web responsive).
- Emisión y gestión de tokens API (Sanctum); los endpoints operativos usan la sesión web autenticada.

---

## 6. Verificación Funcional y Estado Final

**Fecha:** 2026-10-15 · **Último commit verificado:** `feaf194`

> **Nota (2026-10-19):** esta sección documenta el cierre del MVP funcional. El estado vigente tras el
> Post-MVP (§7) y el rediseño visual en curso (§8) es **182 tests / 568 aserciones en verde**; el
> último commit verificado del rediseño es `bd9e7da` (UI U5), con U6 (Repartidor) ya aplicado en el
> árbol de trabajo pero **sin commitear** (el repo también tiene el trabajo del usuario de slugs).

### 6.1 Suite automatizada

- `php artisan test` → **169 tests, 548 aserciones, todas en verde** (base de datos MySQL `fastlogi_test`).
- `php artisan migrate:fresh --seed` → sin errores; 9 usuarios (1 SuperAdmin + 2 dueños + 6 repartidores) y 3 notificaciones in-app de demostración.
- `vendor/bin/pint` → sin cambios pendientes en los archivos tocados.

### 6.2 Prueba de extremo a extremo por HTTP

Levantado `php artisan serve` y navegado con sesión y cookies reales (login por `identifier` + `password`):

| Flujo | Resultado |
| :--- | :--- |
| `GET /login` | `200` con formulario |
| Login dueño → `/negocio` | `200` · "Panel de Operaciones" |
| → `/negocio/repartidores` | `200` · "Gestión de Repartidores" |
| → `/negocio/reportes` | `200` · "Descargar pedidos" |
| → `/negocio/notificaciones` | `200` · "Centro de Notificaciones" |
| `GET /negocio/reportes/pedidos/pdf` | `200` · `application/pdf`, `attachment`, ~880 KB |
| `GET /negocio/reportes/repartidores/pdf` | `200` · `application/pdf`, `attachment`, ~880 KB |
| Login SuperAdmin → `/superadmin` | `200` · "Negocios" |
| Login repartidor → `/repartidor/rutas` | `200` · "Estado de flota" / "Nuevas ofertas" |

### 6.3 Incidencia detectada y corregida

- **CSS desactualizado:** las utilidades introducidas en F7/F8 (colores arbitrarios de los badges, p. ej. `text-[#15803D]`, `bg-[#DCFCE7]`, `bg-[#EDE9FE]`) no estaban en `public/build` porque Tailwind compila en tiempo de build. Los tests no lo detectan. **Resuelto** con `npm run build`; verificado que todas las clases y tokens del tema (`p-space-lg`, `font-body-md`, `surface-container-lowest`, etc.) están presentes en el CSS compilado. `public/build` está en `.gitignore`, por lo que un clon nuevo debe ejecutar `npm install && npm run build`.

### 6.4 Advertencias operativas

- Requiere **tres procesos**: `php artisan serve` + `php artisan queue:work` + `php artisan schedule:work`. Sin worker no se ejecutan la expiración de ofertas (90 s), la detección de inactividad ni el cierre de ventanas de gracia.
- `MAIL_MAILER=log`: los correos de alerta se escriben en `storage/logs`, no se envían realmente (configuración de desarrollo del SRS).
- Sin push/WebSockets (por diseño): notificaciones por *polling* (campana 10 s, dashboard 15 s).
- El enlace `public/storage` no se crea; no se usa (sin subida de archivos, PDFs en memoria). Si se requiere: `php artisan storage:link`.

---

## 7. Post-MVP: Identificadores legibles (slug)

**Fecha:** 2026-10-15

Se añadió un `slug` a **todas las entidades del dominio** con generación automática, sin cambios de
alcance funcional (no introduce nuevos RF).

- Trait `app/Models/Traits/HasSlug.php`: genera el slug en `creating` desde un atributo fuente
  (`name` por defecto), garantiza unicidad con sufijo numérico (`-2`, `-3`, …), lo regenera al
  cambiar la fuente, normaliza valores enum (`BackedEnum`/`UnitEnum`) y define
  `getRouteKeyName() === 'slug'`.
- Unicidad **global** (`businesses`, `users`, `packages` y las tablas de bitácora) y **por tenant**
  (`business_id`) en `delivery_drivers`, `products` y `orders`.
- Fuentes: `name` (negocios/usuarios/productos), nombre del usuario vinculado (repartidores),
  `customer_name` (pedidos), `tracking_code` (paquetes) y compuestos descriptivos en
  `order_items` / `inventory_movements` / `package_scans` / `order_status_logs`.
- Migraciones `2026_10_15_000013_add_slug_to_core_tables` y
  `2026_10_15_000014_add_slug_to_log_tables` (columna nullable + índice único; nullable para no
  romper filas preexistentes).
- Rutas: las URLs de pedidos usan el slug (`/negocio/pedidos/{slug}`, `/repartidor/pedidos/{slug}`)
  mediante el scope `Order::whereKeyOrSlug()`, que además sigue resolviendo el id numérico (enlaces
  antiguos). Enlaces actualizados en `Owner\Dashboard`, `Driver\Routes` y `NotificationPresenter`.
- Comando `php artisan slugs:backfill`: rellena de forma **no destructiva e idempotente** el slug de
  filas con `slug = NULL` (no reescribe los existentes).
- Pruebas: `tests/Feature/HasSlugTest.php` (13 pruebas). Suite total: **182 tests / 568 aserciones en
  verde**. Formateo con Pint aplicado a los archivos tocados.

---

## 8. Rediseño visual (en curso)

**Inicio:** 2026-10-17 · **Fuente de verdad visual:** `doc/UI_GUIDE.md` (gana ante cualquier conflicto)

Se rediseña **solo la capa visual**: vistas Blade, CSS, layouts y componentes `<x-ui.*>`. No se toca
lógica de negocio, migraciones, modelos, servicios, jobs, policies ni rutas; se conservan nombres de
ruta, propiedades/métodos públicos de Livewire, atributos `wire:*`, nombres de campo, mensajes de
validación y todo lo que asertan los tests.

- Se descarta por completo `doc/fastlogi-views/` (diseño antiguo de Stitch). Las referencias visuales
  vivirán en `doc/ui-reference/` (todavía no disponibles).
- Prohibido: CDN de Tailwind, datos de ejemplo escritos a mano en las vistas, hex a mano (salvo el SVG
  del logo y las plantillas dompdf `reports/orders` y `reports/drivers`), y pintar estados fuera de
  `status-badge` / `availability-badge`.
- Accesibilidad: `role="dialog"` + `aria-modal`, foco visible, cierre con Escape y `aria-current`.
- Autorizaciones dadas por el usuario: eliminar `welcome.blade.php`, adaptar
  `App\Support\NotificationPresenter` (solo presentación), registrar helpers vía `autoload.files`,
  copiar `doc/brand/` → `public/images/brand/` y añadir la dependencia
  `blade-ui-kit/blade-heroicons`. **No** tocar el paquete `@tailwindcss/vite` (está instalado en v4
  pero **no** registrado en `vite.config.js`; Tailwind 3 compila vía PostCSS).

### 8.1 Fases y estado

- [x] **U0 · Diagnóstico (solo lectura).** Inventario de vistas y componentes Livewire, versión de
  Tailwind y línea base de pruebas (182 tests / 568 aserciones).
- [x] **U1 · Base del sistema de diseño.** Commit `ca309f9` (50 archivos).
  - `tailwind.config.js` con los tokens de la guía (`canvas`, `sidebar`, `sidebar-active`, Inter).
  - `resources/css/app.css` y `partials/head.blade.php` (Inter + favicons de marca; sin Material
    Symbols ni Geist).
  - `app/helpers.php` (`format_cop`, `format_datetime`, `format_date`, `format_time`) registrado en
    `autoload.files` de `composer.json`.
  - 24 componentes en `resources/views/components/` (`icon` + `ui/*`): button, badge, card, input,
    select, textarea, checkbox, tabs/tab, table/th/td, kpi, alert, modal, drawer, toast,
    capacity-bar, avatar, empty-state, page-header, status-badge y availability-badge.
  - 5 layouts: `base`, `owner`, `superadmin`, `driver` y `guest`.
  - `doc/brand/` copiado a `public/images/brand/`; `welcome.blade.php` eliminado.
  - `NotificationPresenter` adaptado (iconos heroicon outline y colores literales).
  - Dependencia nueva: `blade-ui-kit/blade-heroicons ^2.7` (outline, 20 px, trazo 1.75). Se desactiva
    el registro automático de componentes de `blade-icons` (`config/blade-icons.php`,
    `components.disabled`) y todos los iconos se usan como `<x-icon name="...">`.
- [x] **U2 · Inicio de sesión.** Commit `171f0f0` (9 archivos).
  - `auth/login.blade.php` reconstruido sobre el layout `guest`: campos correo-o-teléfono y contraseña
    (con botón mostrar/ocultar en Alpine), "Recordar sesión", botón, la nota "Las cuentas son creadas
    por el administrador de la plataforma" y las alertas roja/ámbar de la guía.
  - Se eliminan el selector de "modo demo", el logo remoto, la barra de telemetría inventada y los
    enlaces muertos. Sin registro, sin "olvidé mi contraseña" y sin login social.
- [x] **U3 · Pedidos del dueño.** Commit `7a46ac4` (3 archivos).
  - `livewire/owner/orders.blade.php` reconstruido con el sistema de diseño: cabecera + "Nuevo pedido",
    aviso ámbar de stock bajo con enlace "Ver inventario", tarjeta con pestañas y contadores, barra de
    filtros, tabla (`# Pedido`, cliente + teléfono, dirección, total + pago, repartidor, estado, acción)
    y paginación con vista propia `partials/pagination.blade.php`.
  - Se retiran los 4 KPIs (viven en el dashboard) y las acciones de la tabla siguen la guía (Ofrecer /
    "Responde en 0:54" + Retirar / Ver detalle / motivo del cancelado / Confirmar devolución); Cancelar,
    Reasignar y Registrar incidencia pasan al drawer de detalle.
  - `Owner\Orders::render()` solo suma dos datos de lectura (`statusCounts` para los contadores y
    `actionOrder` para el resumen de los modales); props públicas, métodos y validaciones intactos.
- [x] **U4 · Resto del panel del dueño.** Commit `1294d16` (9 archivos).
  - `livewire/owner/dashboard.blade.php`: KPIs de la guía (Ventas de hoy, Pedidos entregados, Tiempo
    promedio de entrega, Pedidos activos), tabla "Pedidos en curso", columnas "Flota" (avatar, badge de
    disponibilidad, barra de capacidad) y "Alertas de inventario" (producto, stock, mínimo, badge,
    "Ver inventario") y timeline "Actividad reciente"; se retiró el aviso ámbar suelto (su función pasa
    a la tarjeta de alertas).
  - `livewire/owner/inventory.blade.php`: cabecera + "Nuevo producto", aviso ámbar "Atención de
    inventario", barra de filtros (búsqueda + estado), tabla con producto/código, precio, stock, mínimo,
    unidad, badge de estado y acciones, más modal de producto con "El stock no puede ser negativo".
  - `livewire/owner/drivers.blade.php`: alerta roja "Sin conexión con pedidos asignados" + "Ver pedidos",
    tabla (repartidor, disponibilidad, pedidos activos, última conexión en ámbar si supera el umbral,
    límite, acciones), y modales de alta/edición con slider 1–8 y contraseña con mostrar/ocultar.
  - `livewire/owner/reports.blade.php`: filtros, botones "Descargar pedidos (PDF)" / "Descargar
    repartidores (PDF)", KPIs del periodo y vista previa paginada.
  - `livewire/owner/notifications.blade.php`: pestañas Todas/No leídas/Leídas con contadores, filas con
    icono por tono, "Sin leer", acciones ver/marcar/eliminar y vacío.
  - `Owner\Dashboard::render()` solo suma dos datos de lectura (`promedioEntrega` = media de
    `created_at`→`updated_at` de los entregados y `lowStockProducts` para las alertas). `x-ui.button`
    ahora renderiza `<a>` cuando recibe `href`, `x-ui.badge` añade el tono `teal` y `partials/pagination`
    acepta un `label` (por defecto "pedidos").
- [x] **U5 · SuperAdmin.** Commit `bd9e7da` (1 archivo).
  - `livewire/superadmin/dashboard.blade.php`: aviso de éxito con `x-ui.alert`, cabecera con
    "Portal SuperAdmin · Multi-Tenant" y botón "Nuevo Negocio", 4 KPIs (`x-ui.kpi`: negocios
    activos, suspendidos, pedidos hoy, ventana de gracia), chips de filtro (Todos / Activos /
    Suspendidos con contadores), búsqueda en vivo y tabla (`# Negocio` con avatar de iniciales,
    dueño, teléfono mono, badge Activo/Suspendido, pedidos en gracia con badge ámbar + hora
    restante y botón "Cerrar gracia", acciones Reactivar / Suspender). Modal "Crear Nuevo
    Negocio" (7 campos, contraseña con mostrar/ocultar) y modal "Confirmar Suspensión" (lista de
    implicaciones + checkbox obligatorio `suspendConfirmed`).
  - Nota: `x-ui.modal` exige el prop `property` (bool público de Livewire, cierra con
    `$wire.set`) en lugar de `wire:close` — ambos modales lo usan.
- [x] **U6 · Repartidor.** Vistas `livewire/driver/routes.blade.php` y `livewire/driver/order-detail.blade.php` reconstruidas (pendiente de commit; el árbol tiene trabajo del usuario sin commitear).
  - `routes.blade.php`: `<x-ui.toast />` para el flash de éxito y `x-ui.alert` para el error `offer`;
    `x-ui.page-header` "Rutas de hoy"; aviso ámbar de la guía ("No estamos recibiendo tu ubicación…")
    solo cuando hay un pedido `in_transit` y `last_seen_at` supera los 3 min; tarjeta destacada de oferta
    con `border-2 border-teal-600`, anillo SVG de 64 px de cuenta regresiva real (teal > 30 s, ámbar ≤ 30 s,
    rojo ≤ 10 s, `offer_expires_at` de servidor + Alpine), productos, total, método de pago y botones
    Rechazar / Aceptar; lista "Mis pedidos" (tarjetas con `status-badge` que enlazan al detalle) y tarjeta
    sticky "Tu día" (entregadas hoy, en curso, ofertas y `x-ui.capacity-bar`). Se retiran el banner
    "Estado de flota" (duplicaba el badge del navbar) y el aviso de telemetría permanente.
  - `order-detail.blade.php`: migas "Rutas de hoy / guía", encabezado con guía de rastreo, total y método
    de pago, dos columnas (cliente + teléfono, manifiesto con checklist, trazabilidad como línea de tiempo
    con `border-l`; derecha sticky con pasos Asignado → En camino → Entregado, acción principal
    "Registrar recogida" / "Marcar como entregado" y "Reportar incidencia") y modal de incidencia con
    `x-ui.select` / `x-ui.textarea`. Variante cancelada: banner rojo "Este pedido fue cancelado por el
    negocio" y acciones deshabilitadas.
  - `Driver\Routes::render()` solo suma un dato de lectura (`deliveredToday`, entregados con
    `updated_at` de hoy); métodos, props y `wire:*` intactos.
  - Verificación: `npm run build`, `php artisan test` (182 tests / 568 aserciones) y `pint` en verde;
    clases nuevas (`stroke-*`, `pl-7`, `-rotate-90`) confirmadas en el CSS compilado.
- [ ] **U7 · QA visual.** Barridos de patrones prohibidos (`text-[9|10|11]px`, `cdn.tailwindcss`,
  `googleusercontent`, `brightness-0`, `min-w-[1440`, textos SOS/Rastrear/ETA/comprobante/Datáfono/
  Reordenar/Lorem), hex a mano = 0 y revisión de textos por pantalla.

### 8.2 Convenciones de trabajo del rediseño

- Un commit por fase: `style(ui): U<n> <descripción>`. **Nunca** `git add -A`: hay trabajo del usuario
  sin commitear, así que se preparan rutas explícitas.
- Al cerrar cada fase: `npm run build` y `php artisan test` en verde, `php artisan view:clear` (y
  `icons:clear`) cuando se toquen vistas o tokens, y un resumen con las URLs a revisar. Se espera el
  "continúa" del usuario antes de la siguiente fase.
- Los mapas de clases (`status-badge`, `availability-badge`, `NotificationPresenter`) son siempre
  literales completos, nunca concatenados (`'bg-'.$color`).

### 8.3 Hallazgos corregidos durante el rediseño

| # | Fecha | Hallazgo | Causa | Resolución |
| :-- | :--- | :--- | :--- | :--- |
| 1 | 2026-10-17 | `x-ui.alert` rompía el render con un error de sintaxis PHP | Un `[` sin cerrar en el `merge([...])` de la vista del componente (roto desde U1 y nunca renderizado) | Se cerró el arreglo; se añadió un render de humo de los 24 componentes |
| 2 | 2026-10-17 | `status-badge` / `availability-badge` fallaban con los modelos reales | Usaban el enum como índice de arreglo (`$map[$enum]`) | Se normaliza a `->value` cuando llega un `BackedEnum` |
| 3 | 2026-10-17 | Campos con `<p>` de error vacío y `aria-describedby` fantasma | `MessageBag::first()` devuelve `''` (no `null`) cuando no hay error | `x-ui.input` usa `$errors->has()` antes de `first()` |
| 4 | 2026-10-17 | "Recordar sesión" impedía iniciar sesión | Un checkbox sin `value` envía `on`, que la regla `boolean` del controlador rechaza | Solo marcado: `x-ui.checkbox` envía `value="1"` (el controlador no se tocó) |
| 5 | 2026-10-17 | Duda resuelta: `::type` en componentes | Blade convierte `::attr` en `:attr` para Alpine | Verificado que Alpine mapea `:` → `x-bind:` (`mapAttributes(startingWith(":", into(prefix("bind:"))))`), así que funciona |
| 6 | 2026-10-17 | `x-ui.modal` tiraba `Undefined variable $property` al renderizar | Los modales de U5 usaban `wire:close` en vez del prop `property` (el componente cierra con `$wire.set('{{ $property }}', false)`) | Ambos modales pasan `property="showCreateModal"` / `property="showSuspendModal"`, igual que los modales de U3/U4 |

### 8.4 Desviaciones conscientes de la guía

| # | Fase | La guía dice | Se mantiene | Motivo |
| :-- | :--- | :--- | :--- | :--- |
| 1 | U4 | Tarjeta "Pedidos activos" | "Pedidos en curso" | `OwnerDashboardTest` fija el texto; son equivalentes |
| 2 | U4 | Columna "Repartidores" | "Flota" | `OwnerDashboardTest` fija el texto; son equivalentes |
| 3 | U4 | El dashboard no lista "Actividad reciente" | Se conserva el timeline | Sección funcional existente verificada por test; regla "no borrar secciones" |
| 4 | U4 | No lista las pantallas "Reportes" ni "Notificaciones" | Se conservan y se reestilizan | Ya existen, están enrutadas y con tests; el rediseño no elimina funcionalidad |
| 5 | U4 | KPI "Tiempo promedio de entrega" | Se calcula de solo lectura (`updated_at - created_at` de los entregados) porque no hay columna `delivered_at` | No se tocaron modelos ni migraciones; si no se quiere, se revierte a "—" |
| 6 | U6 | Tarjeta de oferta incluye "productos" | Se listan la cantidad y el subtotal de los ítems | `$offer->items` es de solo lectura; no hay props nuevas |
| 7 | U6 | "Mis pedidos" con botón "Registrar recogida" / "Marcar entregado" | La tarjeta enlaza al detalle (donde viven esas acciones) | `Driver\Routes` no tiene esos métodos; no se añadió lógica al componente |
| 8 | U6 | Cuenta regresiva con anillo SVG | Se implementa el anillo, pero la acción principal del detalle conserva el `wire:confirm` | Mejora de UX ya verificada por test |
| 9 | U6 | Aviso ámbar de ubicación "solo cuando de verdad falte" | Se muestra solo con un pedido `in_transit` y `last_seen_at` > 3 min | Sin `in_transit` la telemetría no se envía por diseño (RF-011), así que no es una falta real |
| 10 | U6 | Detalle: pasos "Asignado → En camino → Entregado" (3) | Se sustituye el "Paso X de 5" previo por los 3 pasos de la guía | Ningún test fijaba el texto anterior |
| 11 | U6 | Detalle: etiquetas "Registrar recogida" / "Marcar como entregado" | Se adoptan las etiquetas de la guía (antes "Escanear recogida" / "Escanear entrega") | Ningún test fijaba el texto anterior; el flujo de escaneo no cambia |

