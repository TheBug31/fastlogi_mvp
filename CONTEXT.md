# FastLogi — Estado y Contexto del Proyecto

FastLogi es una plataforma SaaS multi-tenant diseñada para pequeños y medianos comercios en Colombia (restaurantes, calzado, minimarkets, etc.) para la gestión integrada de pedidos, inventario con bloqueo pesimista y logística de domicilios con trazabilidad y telemetría.

---

## 1. Información General y Stack Tecnológico

- **Framework:** Laravel 12.0
- **Frontend Reactivo & Componentes:** Livewire v3.8 + Alpine.js
- **Motor de Plantillas & Diseño:** Blade + Tailwind CSS v3.4 (con tokens y tema de Stitch) compilado con Vite
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
- [ ] **Fase 8: Reportes en PDF y Cierre**

---

## 4. Reglas Críticas del Negocio (Fuentes de Verdad: SRS v1.2)

1. **Aislamiento Multi-Tenant (RF-007):** Toda consulta de orders, products y delivery_drivers filtra por el `business_id` del usuario autenticado; el SuperAdmin tiene acceso global para tareas administrativas.
2. **Reserva Concurrente de Stock (RF-001):** `DB::transaction(..., 3)` con `lockForUpdate()` sobre los productos en orden ascendente de `product_id`. Sin side-effects dentro de la transacción. El stock nunca es negativo.
3. **Máquina de Estados de Pedidos (RF-001 §4.1.3):** 7 estados (`pending`, `offered`, `assigned`, `in_transit`, `delivered`, `cancelled`, `incident`). Un pedido `in_transit` nunca se cancela; transiciona a `delivered` o `incident`.
4. **Capacidad y Ofertas (RF-002, RF-005):** Ofertas vencen en 90 s (`offer_expires_at`). Las ofertas cuentan en la capacidad activa del repartidor (`offered + assigned + in_transit`). Un repartidor con pedidos activos $\ge$ `max_active_orders` o desconectado (`offline`) no es elegible.
5. **Alertas de Stock Bajo (RF-008):** Se dispara una única vez al cruzar el mínimo (`stock_ant >= min && stock_nuevo < min`). Se rearma cuando `stock >= min`. Alerta adicional de "agotado" al llegar a 0.
6. **Suspensión de Negocio (RF-003):** Bloqueo inmediato del login del dueño. Pedidos `offered` se cancelan (liberan stock); `pending` se congelan; `assigned`/`in_transit` tienen 24 horas de gracia para entrega o incidencia.
7. **Telemetría y Detección de Inactividad (RF-011):** Repartidor envía GPS cada 60 s sólo con pedidos `in_transit`. Scheduler detecta inactividad tras `inactivity_threshold_minutes` (default 15 min), pasa a `offline` y notifica al dueño.
