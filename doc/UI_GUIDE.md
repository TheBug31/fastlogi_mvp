# FastLogi — Guía de UI (fuente de verdad visual)

Esta guía manda sobre cualquier HTML de referencia. Los archivos de `docs/ui-reference/` se usan para ver la **composición** (layout, jerarquía, espaciado), pero cuando una clase, un color o un texto de la referencia contradiga esta guía, **gana la guía**.

Stack visual: Tailwind CSS compilado con Vite (nunca CDN), fuente Inter, componentes Blade (`<x-ui.*>`) y Livewire 3 (que ya trae Alpine). Interfaz 100 % en español de Colombia.

---

## 1. Reglas de oro

1. Una sola familia tipográfica: **Inter**. Sin serif ni monoespaciada.
2. **Nada por debajo de 12 px**: prohibido `text-[9px]`, `text-[10px]`, `text-[11px]`. El mínimo es `text-xs`.
3. Botones y badges **nunca se parten en dos líneas** (`whitespace-nowrap`) ni se cortan. Direcciones largas hacen wrap a 2 líneas, jamás `truncate` en datos importantes.
4. Un solo logo por pantalla. Se usan los archivos reales de `public/images/brand/`, sin filtros CSS (nada de `brightness-0 invert`).
5. Sin marcos de dispositivo, sin selectores de "modo demo", sin datos hardcodeados donde el SRS exige lógica.
6. No inventar funciones. Prohibido: botón SOS, estimaciones de ruta/distancia/ETA, GPS en vivo en mapa, descuentos, cupones, firmas digitales, "prioridad de asignación", canales de soporte, "Reordenar" proveedor, "Ver comprobante", "Exportar", "Datáfono". Métodos de pago: solo **Efectivo, Transferencia, Tarjeta**.
7. Dinero en COP: `$ 84.500` (puntos de miles, sin decimales). Fechas: `15 de octubre de 2026, 3:20 p. m.`. Crear helpers `format_cop()` y `format_datetime()` y usarlos en todas las vistas. Cifras y cantidades con `tabular-nums`.
8. Contraste: el botón primario es `teal-700` (`#0F766E`) con texto blanco. El `teal-600` (`#0D9488`) queda para detalles (puntos, barras, logo), no para fondos con texto blanco.

---

## 2. Tokens

Solo se definen 3 colores propios; el resto usa la paleta por defecto de Tailwind.

| Uso | Valor | Clase |
|---|---|---|
| Fondo de página | `#F5F7FA` | `bg-canvas` |
| Sidebar | `#0B1220` | `bg-sidebar` |
| Ítem activo/hover del sidebar | `#162033` | `bg-sidebar-active` |
| Texto principal | `#0F172A` | `text-slate-900` |
| Texto secundario | `#475569` | `text-slate-600` |
| Texto tenue / placeholder | `#94A3B8` | `text-slate-400` |
| Bordes | `#E2E8F0` | `border-slate-200` |
| Primario (botones, links, foco) | `#0F766E` | `bg-teal-700` / `hover:bg-teal-800` |
| Acento de marca | `#0D9488` | `teal-600` |
| Tinte primario | `#CCFBF1` | `bg-teal-100` |

**Tailwind v4** (default de Laravel 12) en `resources/css/app.css`:

```css
@import "tailwindcss";
@source "../views";
@source "../../app/Livewire";

@theme {
  --font-sans: "Inter", ui-sans-serif, system-ui, sans-serif;
  --color-canvas: #F5F7FA;
  --color-sidebar: #0B1220;
  --color-sidebar-active: #162033;
}
```

**Tailwind v3**: mismos tres colores en `theme.extend.colors` y `fontFamily.sans = ['Inter', ...]` en `tailwind.config.js`.

Inter se carga en el `<head>` de los layouts:
`<link rel="preconnect" href="https://fonts.googleapis.com">` + `<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">`.

Tipografía: título de página `text-2xl font-bold tracking-tight`; título de sección `text-lg font-semibold`; cuerpo y tablas `text-sm`; etiquetas, ayudas y badges `text-xs`; cifras KPI `text-3xl font-semibold tabular-nums`.

Forma: tarjetas y drawers `rounded-xl`; modales `rounded-2xl`; botones, inputs y selects `rounded-lg`; badges `rounded-full`. Sombra de tarjetas `shadow-sm`; modales y drawers `shadow-2xl`. Sin gradientes en superficies.

Iconos: SVG de contorno (estilo Heroicons outline, 20 px, `stroke-width` 1.75) dentro de un componente `<x-icon name="...">`. No instalar paquetes de iconos.

---

## 3. Mapas de estado (siempre iguales en todo el producto)

### Estado de pedido (`<x-ui.status-badge :status="$order->status">`)

| Estado | Etiqueta | Clases del badge | Punto |
|---|---|---|---|
| `pending` | Pendiente | `bg-slate-200 text-slate-800` | `bg-slate-500` |
| `offered` | Ofrecido | `bg-violet-100 text-violet-800` | `bg-violet-500` |
| `assigned` | Asignado | `bg-blue-100 text-blue-800` | `bg-blue-500` |
| `in_transit` | En camino | `bg-amber-100 text-amber-800` | `bg-amber-500` |
| `delivered` | Entregado | `bg-green-100 text-green-800` | `bg-green-500` |
| `cancelled` | Cancelado | `bg-slate-100 text-slate-500` | `bg-slate-400` |
| `incident` | Incidencia | `bg-red-100 text-red-700` | `bg-red-600` |

> Las referencias HTML usan colores distintos entre frames (ofrecido en ámbar o azul, asignado en índigo o morado…). Ignóralos: vale esta tabla.

### Disponibilidad del repartidor (`<x-ui.availability-badge>`)

Disponible `bg-green-100 text-green-800` · Ocupado `bg-amber-100 text-amber-800` · Desconectado `bg-slate-100 text-slate-600`. Solo existen estos tres estados.

### Stock

Normal `green` · Stock bajo `amber` · Agotado `red` (mismo formato de badge).

---

## 4. Layouts (shells)

### Owner y SuperAdmin (`layouts/owner`, `layouts/superadmin`)

- **Sidebar** fijo: `fixed inset-y-0 left-0 z-30 w-64 bg-sidebar flex flex-col justify-between`. Arriba: logo `public/images/brand/logo-horizontal-dark.png` (`h-8 w-auto`) en un bloque `h-16 px-6 border-b border-white/10`. Navegación `p-3 space-y-1`.
  - Ítem: `flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium`.
  - Inactivo: `text-slate-400 hover:bg-sidebar-active hover:text-white`.
  - Activo: `bg-sidebar-active text-white border-l-[3px] border-teal-500`, con `aria-current="page"` (se calcula con `request()->routeIs()`).
  - Ítems del dueño: Dashboard, Pedidos, Nuevo pedido, Inventario, Repartidores, Reportes, Configuración. SuperAdmin: Dashboard, Negocios, Métricas. Si un módulo no tiene ruta aún, el ítem se muestra deshabilitado (`opacity-50 pointer-events-none`, `aria-disabled`).
  - Pie del sidebar: punto verde + "Servicio en línea".
- **Contenido**: `pl-64 min-h-screen`. **Top bar**: `sticky top-0 z-20 h-16 border-b border-slate-200 bg-white px-8 flex items-center justify-between`. Izquierda: nombre del negocio (`text-base font-semibold`) con su inicial en un cuadro `h-8 w-8 rounded-lg bg-teal-100 text-teal-800`. Derecha: campana con contador, separador `h-6 w-px bg-slate-200`, nombre y rol del usuario + avatar con iniciales.
- **Main**: `mx-auto max-w-7xl p-8 space-y-6` sobre `bg-canvas`.
- **Campana**: abre un **drawer a la derecha** (480 px) con pestañas Todas / No leídas y "Marcar todas como leídas". Nunca un dropdown que tape las tarjetas KPI.

### Repartidor — web de escritorio (`layouts/driver`)

Sin sidebar y sin barra inferior.

- Navbar: `fixed inset-x-0 top-0 z-30 h-16 border-b border-slate-200 bg-white`, contenedor interno `mx-auto flex h-full max-w-[1100px] items-center justify-between px-6`. Izquierda: logo `logo-horizontal.png` (`h-8`) y enlaces "Rutas de hoy" e "Historial" (`h-16 inline-flex items-center border-b-2 text-sm font-medium`; activo `border-teal-600 text-slate-900`, inactivo `border-transparent text-slate-500 hover:text-slate-900`). Derecha: pill verde "Ubicación activa", pill de disponibilidad, avatar con nombre y rol "Repartidor".
- Contenido: `pt-16`, `mx-auto max-w-[1100px] px-6 py-8`, fondo `bg-canvas`.

### Guest — login (`layouts/guest`)

Pantalla dividida `md:flex-row`. Izquierda `md:w-1/2 bg-sidebar text-white p-14`: logo oscuro arriba, titular "Tu operación de domicilios, bajo control" (`text-4xl font-bold tracking-tight`), 3 puntos con icono en cuadro `h-10 w-10 rounded-lg bg-white/5 border border-white/10 text-teal-400`. Derecha `md:w-1/2 bg-white`: formulario `max-w-[400px]` centrado, pie "Nexlab Studio" (`text-xs text-slate-400`).

---

## 5. Componentes (recetas de clases)

Crear estos componentes Blade en `resources/views/components/ui/` y reutilizarlos. Ninguna vista debe repetir estas clases a mano.

**Botón** (`<x-ui.button variant="primary|secondary|danger|danger-solid|ghost" size="md|sm">`)
- Base: `inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg text-sm font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50`. Tamaño md `h-10 px-4`; sm `h-8 px-3 text-xs`.
- primary `bg-teal-700 text-white hover:bg-teal-800` · secondary `bg-white text-slate-700 border border-slate-300 hover:bg-slate-50` · danger `bg-white text-red-600 border border-red-300 hover:bg-red-50` · danger-solid `bg-red-600 text-white hover:bg-red-700` · ghost `text-teal-700 hover:underline`.

**Badge**: `inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium` + par de colores + punto `h-1.5 w-1.5 rounded-full` opcional.

**Tarjeta** (`<x-ui.card>`): `rounded-xl border border-slate-200 bg-white shadow-sm`; padding `p-6` (denso `p-5`). Encabezado de tarjeta: `flex items-center justify-between border-b border-slate-200 px-6 py-4`.

**Campos** (`x-ui.input|select|textarea`, con label, ayuda y error)
- Label `mb-1.5 block text-sm font-medium text-slate-700`.
- Control `h-10 w-full rounded-lg border border-slate-300 bg-white px-3.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20`.
- Error: borde `border-red-500 focus:ring-red-500/20` + mensaje `mt-1.5 text-xs font-medium text-red-600`. Ayuda: `mt-1.5 text-xs text-slate-500`.
- Los errores salen de `$errors` de Laravel / Livewire, nunca fijos.
- Contraseña con botón de mostrar/ocultar funcional (Alpine).
- Slider (máximo de pedidos 1–8): `accent-teal-600`, etiqueta con el valor actual enlazada con `wire:model.live`; sin gradientes escritos a mano.

**Tabla** (`<x-ui.table>`)
- Contenedor `overflow-x-auto` dentro de una tarjeta con `overflow-hidden`.
- `thead`: `bg-slate-50 border-b border-slate-200`; `th`: `px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500`.
- `tbody`: `divide-y divide-slate-100`; `tr`: `hover:bg-slate-50/70`; `td`: `px-4 py-3.5 text-sm text-slate-700`.
- Número de pedido `font-semibold text-teal-700`; dinero `text-right font-semibold tabular-nums text-slate-900`; texto secundario `text-xs text-slate-500`; "Sin asignar" `italic text-slate-400`.
- Acciones alineadas a la derecha. Fila de incidencia con `bg-red-50/40`. Pie con paginación: `Mostrando 8 de 12 pedidos` + Anterior / páginas / Siguiente (usar el paginador de Laravel con vista propia).

**Pestañas con contador** (`<x-ui.tabs>`): contenedor `flex gap-6 border-b border-slate-200`; pestaña `flex items-center gap-2 border-b-2 pb-3 text-sm font-medium whitespace-nowrap`; activa `border-teal-600 text-teal-700`, inactiva `border-transparent text-slate-500 hover:text-slate-800`; contador `rounded-full px-2 py-0.5 text-xs font-semibold` (activa `bg-teal-50 text-teal-700`, inactiva `bg-slate-100 text-slate-600`).

**Barra de filtros**: `flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50/70 p-4`; búsqueda con icono a la izquierda; selects de método de pago y fecha. El filtro de fecha es un input de fecha real, no texto fijo.

**KPI** (`<x-ui.kpi>`): tarjeta con etiqueta `text-xs font-medium text-slate-500`, valor `text-3xl font-semibold tabular-nums`, tendencia `text-xs` en verde o rojo y un icono dentro de un círculo `h-10 w-10 bg-teal-50 text-teal-700`.

**Avisos** (`<x-ui.alert type="warning|error|info|success">`): `flex items-start gap-3 rounded-xl border p-4 text-sm`; warning `border-amber-200 bg-amber-50 text-amber-900`, error `border-red-200 bg-red-50 text-red-800`, info `border-blue-200 bg-blue-50 text-blue-800`, success `border-green-200 bg-green-50 text-green-800`; icono a la izquierda, texto corto y enlace opcional subrayado a la derecha.

**Modal** (`<x-ui.modal>`, Alpine + Livewire)
- Fondo `fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4`; panel `w-full max-w-[560px] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl`, `role="dialog" aria-modal="true" aria-labelledby`.
- Cabecera `flex items-start justify-between border-b border-slate-100 px-6 py-5` (título `text-xl font-bold`, subtítulo `text-xs text-slate-500`, botón cerrar). Cuerpo `max-h-[calc(100vh-210px)] space-y-5 overflow-y-auto p-6`. Pie `flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4` con Cancelar (secondary) a la izquierda de la acción principal.
- Cierra con Escape y con clic en el fondo, y devuelve el foco. Las confirmaciones destructivas exigen un checkbox antes de habilitar el botón rojo.

**Drawer** (`<x-ui.drawer>`): panel `fixed inset-y-0 right-0 z-50 flex w-full max-w-[560px] flex-col bg-slate-50 shadow-2xl` sobre fondo oscurecido. Cabecera: título en la primera línea; badge de estado y fecha en la línea de abajo; botón cerrar a la derecha. Cuerpo: tarjetas blancas separadas por espacio (no cajas teñidas). Pie fijo con Cerrar y la acción principal o destructiva.

**Toast** (`<x-ui.toast>`): `fixed right-8 top-5 z-[60] flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-800 shadow-lg`, icono de color a la izquierda, cierra solo a los 4 s y con botón. Se dispara con eventos de Livewire (`$this->dispatch('toast', ...)`).

**Barra de capacidad** (`<x-ui.capacity-bar :active="2" :max="3">`): texto `2/3 pedidos` + pista `h-1.5 w-full rounded-full bg-slate-200` y relleno `bg-teal-600` por debajo del 70 %, `bg-amber-500` desde el 70 % y `bg-red-500` al 100 %. En el cálculo cuentan las ofertas pendientes.

**Cuenta regresiva de la oferta**: anillo SVG de 64 px con el tiempo en `tabular-nums`; teal normal, ámbar bajo 30 s, rojo bajo 10 s. El tiempo restante se calcula en el servidor con `offer_expires_at` y se sincroniza en el cliente con Alpine; nunca un número fijo.

**Línea de tiempo (trazabilidad)**: lista vertical con línea `border-l border-slate-200`; paso completado punto `bg-teal-600` con hora y ubicación; paso actual punto con anillo; paso pendiente punto hueco gris. Título `text-sm font-medium`, meta `text-xs text-slate-500`.

**Avatar**: `flex h-10 w-10 items-center justify-center rounded-full border border-teal-200 bg-teal-50 text-sm font-semibold text-teal-800` con las iniciales.

**Estado vacío**: icono en círculo gris suave, título, una frase y un botón primario (por ejemplo "Aún no tienes pedidos. Crea el primero").

---

## 6. Composición de pantallas

Referencia visual en `docs/ui-reference/`. Estas listas dicen **qué contiene** cada pantalla; no agregar nada más.

- **Login** (`auth/login`): panel de marca + formulario (correo o teléfono, contraseña con mostrar/ocultar, "Recordar sesión", botón, nota "Las cuentas son creadas por el administrador de la plataforma"). Alerta roja `Correo, teléfono o contraseña incorrectos` con bordes rojos en los campos; alerta ámbar `Tu cuenta o tu negocio está suspendido. Contacta al administrador`. Sin registro, sin "olvidé mi contraseña", sin login social.
- **Pedidos del dueño** (`owner/orders/index`): título + botón "Nuevo pedido"; aviso ámbar de stock bajo con enlace "Ver inventario" (solo si hay productos bajo el mínimo); tarjeta con pestañas (Todos, Pendientes, Ofrecidos, Asignados, En camino, Entregados, Incidencias), barra de filtros y tabla: # Pedido, Cliente (nombre + teléfono), Dirección (2 líneas), Total (+ método de pago), Repartidor, Estado, Acción. Acciones: pendiente → "Ofrecer"; ofrecido → "Responde en 0:54" + "Retirar"; asignado, en camino y entregado → "Ver detalle"; cancelado → motivo en texto tenue; incidencia → "Confirmar devolución". Paginación.
- **Modal "Ofrecer pedido #FL-1042"**: resumen (cliente, dirección en 2 líneas, badge "Con trazabilidad" si aplica, monto y método) + lista de candidatos (avatar, nombre, badge de disponibilidad, barra de capacidad "1/3 pedidos activos (incluye ofertas pendientes)", botón "Ofrecer"). Filas deshabilitadas con la razón visible (Desconectado, Capacidad completa). Nota "El repartidor tiene 90 segundos para aceptar".
- **Modal "Nuevo repartidor"**: nombre, teléfono, contraseña (ayuda "Mínimo 8 caracteres"), slider "Máximo de pedidos activos" 1–8 (por defecto 3), errores de validación reales (por ejemplo "El teléfono ya está registrado").
- **Dashboard del dueño**: saludo con fecha; 4 KPI (Ventas de hoy, Pedidos entregados, Tiempo promedio de entrega, Pedidos activos); tarjeta "Pedidos activos" con el mismo patrón de tabla; debajo en dos columnas "Repartidores" (avatar, estado, capacidad) y "Alertas de inventario" (producto, stock, mínimo, badge, enlace "Ver inventario"). Botón de reposición solo si el SRS lo respalda (abre el inventario para sumar stock).
- **Nuevo pedido**: dos columnas (8/4). Izquierda: tarjeta Cliente (nombre, teléfono, dirección, indicaciones) y tarjeta Productos (buscador que muestra stock disponible, tabla de líneas con stepper, precio y subtotal, error "Stock insuficiente: disponibles N", interruptor "Requiere trazabilidad" con su ayuda). Derecha, sticky: Resumen (subtotal, total, método de pago, "Crear pedido", "Cancelar").
- **Detalle de pedido** (drawer o página): cliente, productos con total, pago, repartidor asignado, línea de tiempo de trazabilidad (con variante "Este pedido no tiene trazabilidad"); acciones por estado: Cancelar (solo pending, offered, assigned; pide motivo si está asignado), Reasignar (solo assigned), Registrar incidencia (solo in_transit), Confirmar devolución (solo incident).
- **Inventario**: título + "Nuevo producto" + búsqueda; aviso ámbar con el conteo bajo el mínimo; tabla Producto (nombre + código de barras), Precio, Stock (con indicador), Mínimo, Unidad, Estado, Acciones (editar, eliminar); modal de producto con "El stock no puede ser negativo".
- **Repartidores**: tabla Repartidor (avatar, nombre, teléfono), Disponibilidad, Pedidos activos (barra de capacidad), Última conexión ("hace 3 min"; texto ámbar si supera el umbral de inactividad), Límite de pedidos, Acciones; alerta roja en línea "Sin conexión con pedidos asignados" + "Ver pedidos"; modal Nuevo repartidor.
- **SuperAdmin**: título "Negocios" + "Nuevo negocio"; 4 KPI (Negocios activos, Negocios suspendidos, Pedidos hoy, Pedidos en ventana de gracia); tabla con búsqueda y chips (Todos, Activos, Suspendidos): Negocio, Dueño, Teléfono, Estado, Pedidos en gracia, Acciones ("Suspender" / "Reactivar" y, si hay pedidos en gracia, chip ámbar "3 pedidos en gracia, vence en 18 h" + "Cerrar pedidos en gracia"). Modal "Nuevo negocio" (nombre, dirección, teléfono, nombre del dueño, correo, contraseña inicial mínimo 8) y diálogo de confirmación "¿Suspender [negocio]?" con checkbox "Entiendo las consecuencias" y botón rojo "Suspender negocio".
- **Repartidor — Rutas de hoy** (web): tarjeta destacada "Nueva oferta" con borde `border-2 border-teal-600` (pedido, dirección, productos, método de cobro, cuenta regresiva real, "Rechazar" y "Aceptar"); debajo dos columnas: "Mis pedidos" (tarjetas con estado, cliente, dirección y botón "Registrar recogida" o "Marcar entregado") y una tarjeta sticky "Tu día" (entregadas, pendientes, capacidad "N de M pedidos activos"). Aviso ámbar "No estamos recibiendo tu ubicación. Mantén esta pestaña abierta y la pantalla encendida" solo cuando de verdad falte la ubicación.
- **Repartidor — Detalle de entrega**: migas "Rutas de hoy / Pedido #FL-1042"; izquierda (8 col.): cliente y dirección, productos con checklist, trazabilidad; derecha (4 col., sticky): estado y pasos (Asignado → En camino → Entregado), acción principal según estado (escaneo o código del paquete → "Registrar recogida"; "Marcar como entregado"), "Reportar incidencia" con modal de motivo obligatorio. Variante cancelada: banner rojo "Este pedido fue cancelado por el negocio", botón "Volver a rutas" y todas las acciones deshabilitadas.

---

## 7. Qué corregir de las referencias HTML

Las referencias salieron de Stitch y traen defectos que **no** hay que copiar:

1. Colores de estado inconsistentes entre frames → usar la tabla de la sección 3.
2. Barras laterales distintas entre frames ("Tablero General", "Pedidos en Vivo", "Flota de Repartidores", "Métricas e Informes") → usar los ítems de la sección 4.
3. `brightness-0 invert` sobre el logo y URLs `googleusercontent.com` → usar `public/images/brand/*`.
4. `text-[10px]` y `text-[11px]` → `text-xs`. La regla `* { min-font-size }` no existe en CSS: eliminarla.
5. `min-w-[1440px]` en el body → diseño fluido.
6. Cliente de Tailwind por CDN y `tailwind.config` inline → Vite.
7. Contenido inventado: "Rastrear GPS", "ETA", "Ver comprobante", botón de exportar, "Asignar" (el verbo correcto es "Ofrecer"), estados "Preparando" y "En ruta cercana", "Hoy, 24 Octubre" fijo, direcciones de otra ciudad → datos reales de la base.
8. Toast verde sólido → toast blanco con icono de color.
9. Valores de ejemplo escritos en inputs (`Juan Camilo Ospina`, `secretpassword123`) → campos vacíos enlazados a `wire:model`.
10. Botón primario `#0D9488` con texto blanco → `bg-teal-700`.
