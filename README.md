# FastLogi — MVP

MVP de **FastLogi**, una plataforma SaaS multi-tenant para la gestión de pedidos, inventario y
domicilios de pequeños negocios colombianos. Cada negocio (tenant) opera aislado del resto, con
roles de **SuperAdmin**, **Dueño de negocio** y **Repartidor**.

Documentación funcional de referencia: [`doc/SRS_FastLogi_v1_2.md`](doc/SRS_FastLogi_v1_2.md).
Bitácora de implementación por fases: [`CONTEXT.md`](CONTEXT.md).

## Stack

| Capa | Tecnología |
| --- | --- |
| Lenguaje | PHP 8.4 |
| Framework | Laravel 12 |
| UI reactiva | Livewire v3 |
| Estilos | Tailwind CSS 4 + Vite |
| Base de datos | MySQL 8 (utf8mb4) |
| Colas / scheduler | Driver `database` (`QUEUE_CONNECTION=database`) |
| PDF | barryvdh/laravel-dompdf |
| Pruebas | Pest |

> **Nota de diseño:** el SRS menciona Bootstrap 5 / AdminLTE para la interfaz, pero el diseño de
> referencia (Stitch) es Tailwind; se priorizó Tailwind para reutilizar las vistas entregadas.
> Igualmente, RF-011 describe un endpoint con token Sanctum, pero en el MVP la telemetría usa la
> sesión web ya autenticada del repartidor (`POST /repartidor/telemetria`), para no introducir
> emisión de tokens fuera de alcance.

## Requisitos

- PHP 8.4 con las extensiones habituales de Laravel (incl. `pdo_mysql`, `mbstring`, `gd` para dompdf).
- Composer 2.
- Node.js 20+ y npm.
- MySQL 8 en ejecución.

## Instalación

```bash
# 1. Dependencias PHP y JS
composer install
npm install

# 2. Configuración del entorno
cp .env.example .env       # (en Windows: copy .env.example .env)
php artisan key:generate

# 3. Crear las bases de datos (dev + test)
#    mysql -u root -e "CREATE DATABASE fastlogi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
#    mysql -u root -e "CREATE DATABASE fastlogi_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
#    Ajusta DB_USERNAME / DB_PASSWORD en .env si es necesario.

# 4. Migrar y sembrar datos de demostración
php artisan migrate:fresh --seed

# 5. Compilar los assets
npm run build              # producción
# o, para desarrollo:
npm run dev
```

### Variables de entorno relevantes

```dotenv
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fastlogi
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database   # requerido para ofertas, inactividad y correos
MAIL_MAILER=log             # los correos de alertas se escriben en storage/logs
```

## Ejecución

La aplicación necesita **tres procesos** en entornos locales:

```bash
php artisan serve            # 1. servidor web
php artisan queue:work       # 2. worker de colas (expiración de ofertas, correos, cierres)
php artisan schedule:work    # 3. scheduler (barridos de respaldo cada minuto)
```

O bien un único comando (servidor + cola + logs + Vite):

```bash
composer run dev
```

> El worker de colas y el scheduler son **obligatorios** para el comportamiento completo:
> expiración de ofertas a los 90 s (RF-002), detección de repartidores inactivos (RF-011),
> cierre de ventanas de gracia de negocios suspendidos (RF-003) y envío de correos de stock (RF-008).

Tareas programadas registradas (`php artisan schedule:list`):

| Tarea | Frecuencia | Propósito |
| --- | --- | --- |
| `close-expired-grace-windows` | cada minuto | Cierra pedidos en gracia vencidos (24 h) |
| `expire-order-offers` | cada minuto | Respaldo de ofertas vencidas (90 s) |
| `detect-inactive-drivers` | cada minuto | Marca repartidores inactivos y notifica al dueño |

## Credenciales de demostración

Sembradas por `php artisan migrate:fresh --seed`:

| Rol | Usuario | Contraseña | Panel |
| --- | --- | --- | --- |
| SuperAdmin | `super@fastlogi.test` | `superadmin` | `/superadmin` |
| Dueño (Restaurante El Leñador) | `owner@fastlogi.test` | `password123` | `/negocio` |
| Dueño (Calzado San Victorino) | `calzado@fastlogi.test` | `password123` | `/negocio` |
| Repartidores | `driver1@fastlogi.test` … `driver6@fastlogi.test` | `driverpass` | `/repartidor/rutas` |

El login acepta **correo o teléfono**. Ejemplos de teléfono de repartidor: `3101112233` (driver1).

## Pruebas

Las pruebas usan una base de datos **MySQL independiente** (`fastlogi_test`, ver `phpunit.xml`) y no
SQLite en memoria; un par de pruebas de concurrencia abren **dos conexiones MySQL reales**.

```bash
php artisan test
# Equivalente: vendor/bin/pest
```

Cobertura actual: **169 tests, 548 aserciones** (auth/roles, multi-tenant, inventario, pedidos y
máquina de estados, concurrencia, escaneos, telemetría, dashboard, notificaciones, gestión de flota
y reportes PDF).

Formateo de código (solo archivos tocados):

```bash
vendor/bin/pint app/Services/NombreServicio.php ...
```

## Estructura funcional (mapa RF → archivos)

| RF | Descripción | Archivos principales |
| --- | --- | --- |
| RF-001 | Creación de pedidos y reserva de stock | `app/Services/OrderService.php`, `app/Services/InventoryService.php`, `app/Livewire/Owner/Orders.php` |
| RF-002 | Oferta de pedido y aceptación del repartidor | `app/Services/OrderService.php`, `app/Jobs/ExpireOrderOffer.php`, `app/Notifications/OrderOffered.php`, `app/Notifications/OfferRejectedOrExpired.php`, `app/Livewire/Driver/Routes.php` |
| RF-003 | Suspensión y reactivación de negocios | `app/Services/BusinessSuspensionService.php`, `app/Jobs/CloseGraceOrders.php`, `app/Livewire/Superadmin/Dashboard.php` |
| RF-004 | Creación de negocios por el SuperAdmin | `app/Services/BusinessService.php`, `app/Livewire/Superadmin/Dashboard.php` |
| RF-005 | Disponibilidad y capacidad de repartidores | `app/Services/DriverAvailabilityService.php`, `app/Services/DriverManagementService.php`, `app/Livewire/Owner/Drivers.php` |
| RF-006 | Trazabilidad de paquetes (timeline) | `app/Services/PackageScanService.php`, `app/Livewire/Driver/OrderDetail.php`, `app/Livewire/Owner/Orders.php`, `app/Livewire/Owner/Dashboard.php` |
| RF-007 | Aislamiento multi-tenant | `app/Models/Traits/BelongsToBusiness.php`, `app/Models/Scopes/BusinessScope.php` |
| RF-008 | Inventario y alertas persistentes de stock | `app/Services/InventoryService.php`, `app/Notifications/LowStockAlert.php`, `app/Livewire/Owner/Inventory.php` |
| RF-009 | Reportes en PDF | `app/Services/ReportService.php`, `app/Http/Controllers/Owner/ReportController.php`, `resources/views/reports/*`, `app/Livewire/Owner/Reports.php` |
| RF-010 | Autenticación y roles | `app/Http/Controllers/Auth/LoginController.php`, `app/Http/Middleware/RoleMiddleware.php` |
| RF-011 | Ubicación GPS e inactividad del repartidor | `app/Services/DriverTelemetryService.php`, `app/Jobs/DetectInactiveDrivers.php`, `app/Http/Controllers/Driver/DriverTelemetryController.php` |
| Centro de notificaciones | Campana + centro persistente | `app/Livewire/Owner/NotificationBell.php`, `app/Livewire/Owner/Notifications.php`, `app/Support/NotificationPresenter.php` |
| Dashboard del dueño | KPIs, flota y timeline | `app/Livewire/Owner/Dashboard.php` |

## Alcance

**Incluido:** gestión de negocios (SuperAdmin), inventario con alertas multicanal, pedidos con
máquina de estados y concurrencia pesimista, ofertas con expiración, panel del repartidor con
escaneos, telemetría GPS e inactividad, trazabilidad, centro de notificaciones in-app, gestión de
flota y reportes en PDF.

**Fuera de alcance (por decisión del MVP):** variantes de producto, mapas en vivo, notificaciones
push/WebSocket, facturación, aplicación nativa/PWA.
