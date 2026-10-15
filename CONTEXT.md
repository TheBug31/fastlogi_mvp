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
- [ ] **Fase 3: SuperAdmin (Negocios, Suspensión y Ventana de Gracia)**
- [ ] **Fase 4: Inventario y Alertas de Stock Persistentes**
- [ ] **Fase 5: Pedidos, Concurrencia Pesimista y Máquina de Estados**
- [ ] **Fase 6: Panel del Repartidor, Escaneos, GPS e Inactividad**
- [ ] **Fase 7: Dashboard de Dueño, Notificaciones y Timeline**
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
