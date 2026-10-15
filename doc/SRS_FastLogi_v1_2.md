**Especificación de Requerimientos de Software (SRS)**

**FastLogi**

Plataforma SaaS Multi-tenant para la Gestión de Pedidos, Inventario y Logística de Domicilios de Pequeños y Medianos Negocios

**Versión 1.2**

13 de octubre de 2026

Marlon Julián Ortiz Valencia

Corporación Universitaria Misión Paz

Tecnología en Desarrollo de Software

**Contenido**

# Nota de Versión 1.2

Esta versión integra en el cuerpo del documento las correcciones derivadas de la revisión de la versión 1.0 (siete recomendaciones, incorporadas en la 1.1) y cierra los vacíos e inconsistencias detectados al revisar la 1.1. Las reglas nuevas ya no se marcan con etiquetas de actualización: forman parte de los requerimientos vigentes.

### Historial de versiones

| **Versión** | **Descripción** | **Fecha** |
| --- | --- | --- |
| 1.0 | Versión inicial del SRS. | — |
| 1.1 | Incorpora las siete recomendaciones de la revisión: concurrencia, matriz de cancelación, límite de pedidos, frecuencia GPS, aceptación del repartidor, suspensión y alertas de stock. | — |
| 1.2 | Cierra vacíos de la 1.1: estados offered e incident en la matriz, conteo de ofertas en la capacidad, reasignación por inactividad, mecanismo de notificación y expiración, tope de la ventana de gracia, regla de disparo de alertas, orden de bloqueo de filas y limitación del GPS en navegador. Corrige inconsistencias internas. | 2026-10-13 |

### Trazabilidad de las recomendaciones de la revisión

| **#** | **Recomendación** | **Dónde se resuelve** |
| --- | --- | --- |
| 1 | Concurrencia en la reserva de inventario | RF-001, RF-008, sección 2.4, RNF de consistencia, HU03 |
| 2 | Matriz de estados y reglas de cancelación | RF-001, CU18, CU19, HU03b |
| 3 | Límite máximo de asignación de pedidos | RF-005, CU09, HU07 |
| 4 | Frecuencia de la trazabilidad GPS | RF-011, CU17, HU08 |
| 5 | Aceptación o rechazo del repartidor | RF-002, CU09, HU04 |
| 6 | Suspensión de negocios con operaciones en curso | RF-003, CU20, HU02 |
| 7 | Canal y persistencia de las alertas de inventario | RF-008, CU11, HU10 |

### Cambios de la versión 1.2 respecto a la 1.1

- Se agregan los estados **offered** e **incident** a la matriz de estados y se define su tratamiento en cancelación, capacidad y suspensión.

- Las ofertas pendientes (offered) cuentan para el límite de pedidos activos del repartidor.

- Se define la reasignación por inactividad del repartidor (umbral de last_seen_at) para pedidos ya aceptados.

- Se reemplaza la referencia a notificaciones push por notificaciones in-app con consulta periódica (polling cada 10 s) y se define la expiración de ofertas mediante jobs en cola con respaldo del scheduler.

- La ventana de gracia por suspensión tiene un tope de 24 horas y un cierre administrativo a cargo del SuperAdmin.

- La alerta de stock bajo se dispara al cruzar el mínimo y se rearma al reponer stock; no se repite por cada venta.

- El bloqueo de filas en pedidos con varios productos se realiza en orden ascendente de product_id para evitar interbloqueos.

- Se documenta la limitación del envío de GPS desde navegador y se agrega el RF-011.

- Se unifica la versión de PHP (8.4) entre las secciones 2.4 y 9.

- Se elimina el flujo de aprobación/rechazo de negocios, que contradecía RF-004 (el negocio nace activo); CU03 pasa a ser la consulta de negocios.

- Se añaden los casos de uso CU18 a CU20 y las historias HU09 y HU10, y se redibujan el diagrama de casos de uso (sección 6) y los diagramas de actividades (sección 8).

# 1. Introducción

## 1.1 Propósito

Este documento detalla los requisitos funcionales y no funcionales de FastLogi, una plataforma SaaS multi-tenant para la gestión de pedidos, inventario y logística de domicilios de pequeños y medianos negocios de cualquier sector (por ejemplo, restaurantes, dark kitchens, farmacias, tiendas de barrio, minimarkets, papelerías, entre otros). El sistema no está atado a un rubro particular: cada negocio (tenant) configura si maneja o no inventario y si sus pedidos requieren trazabilidad, adaptándose a su propio modelo operativo. El sistema permite a cada negocio gestionar sus pedidos, repartidores, inventario y trazabilidad de forma aislada e independiente, con roles diferenciados según el tipo de usuario.

## 1.2 Ámbito del Sistema

**Nombre del Sistema:** FastLogi.

**Descripción:** FastLogi permite a pequeños y medianos negocios de cualquier sector (comida, farmacia, retail, minimarkets, papelerías, servicios con entrega a domicilio, etc.) gestionar sus procesos de venta, domicilios y logística, reduciendo la dependencia de plataformas de domicilios de terceros y sus altas comisiones. El sistema soporta múltiples negocios (tenants) de forma aislada sobre una misma infraestructura, con tres roles: SuperAdmin, Dueño de Negocio y Repartidor. Los ejemplos de restaurantes, dark kitchens y farmacias usados en este documento son solo casos ilustrativos de validación, no una limitación del alcance del producto.

**Beneficiarios:** pequeños y medianos negocios en Colombia, de cualquier sector que gestione pedidos con entrega a domicilio (con o sin inventario propio), que buscan independizarse de intermediarios externos y ganar control sobre su operación logística.

## 1.3 Definiciones, Acrónimos y Abreviaturas

- **SRS:** Especificación de Requerimientos de Software.

- **BD:** Base de Datos.

- **SaaS:** Software as a Service (Software como Servicio).

- **Multi-tenant:** arquitectura donde múltiples negocios (tenants) comparten la misma infraestructura con aislamiento de datos.

- **POS:** Point of Sale (Punto de Venta).

- **API:** Application Programming Interface.

- **CU:** Caso de Uso.

- **HU:** Historia de Usuario.

- **RF:** Requerimiento Funcional.

- **RNF:** Requerimiento No Funcional.

- **Bloqueo pesimista (lockForUpdate):** mecanismo de base de datos que bloquea una fila durante una transacción para impedir que otra transacción concurrente la lea o modifique hasta que la primera finalice.

- **Pedido activo:** pedido del repartidor en estado offered, assigned o in_transit.

- **Oferta:** pedido en estado offered, pendiente de aceptación o rechazo por parte del repartidor.

- **Ventana de gracia:** periodo posterior a la suspensión de un negocio en el que los pedidos assigned o in_transit pueden completarse.

- **Polling:** consulta periódica del cliente al servidor para obtener novedades sin usar conexiones persistentes.

## 1.4 Visión General del Documento

El documento se estructura así: 1. Introducción, 2. Descripción General, 3. Requerimientos Específicos, 4. Requerimientos Funcionales detallados, 5. Entrevista y Tabulación (investigación de mercado), 6. Casos de Uso, 7. Historias de Usuario, 8. Diagramas de Actividades, 9. Manual de Instalación, 10. Manual de Usuario.

# 2. Descripción General

## 2.1 Perspectiva del Producto

FastLogi es una aplicación web independiente, construida sobre Laravel 12 y Livewire v3, que puede operar como un producto SaaS comercial (marca Nexlab Studio) o como caso de estudio académico. El producto es agnóstico al sector: no fue diseñado exclusivamente para comida o farmacias, sino como una plataforma genérica de gestión de pedidos y logística de domicilios que cualquier pequeño o mediano negocio puede adoptar. El sistema debe ser lo suficientemente flexible para adaptarse a distintos tipos de negocio (con o sin inventario, con o sin trazabilidad de paquetes), y cada tenant configura estos módulos según su propio modelo de operación.

## 2.2 Funciones del Producto

- **Gestión Multi-tenant:** aislamiento completo de datos entre negocios registrados.

- **Gestión de Pedidos:** creación, oferta y asignación, seguimiento, cancelación, incidencias y entrega de pedidos, simples o con trazabilidad.

- **Gestión de Inventario (opcional):** productos, movimientos de stock, reservas y alertas de stock mínimo.

- **Trazabilidad:** escaneo de recogida y entrega de paquetes con geolocalización y timeline consultable.

- **Gestión de Repartidores:** estados de disponibilidad (available/busy/offline) sincronizados automáticamente, con límite de capacidad configurable y detección de inactividad.

- **Analítica y Reportes:** métricas de ventas, tiempos de entrega y reportes en PDF.

- **Notificaciones:** eventos de negocio, pedidos y stock bajo dirigidos a cada rol, persistidos en un centro de notificaciones y consultados por el cliente mediante polling.

## 2.3 Características de los Usuarios

- **SuperAdmin:** acceso completo a la plataforma; gestiona el ciclo de vida de los negocios (creación, suspensión, reactivación y cierre de pedidos en ventana de gracia).

- **Dueño de Negocio (BusinessOwner):** acceso restringido a su propio negocio (business_id); gestiona pedidos, repartidores, inventario y configuración.

- **Repartidor (Driver):** acceso restringido a sus propios pedidos; gestiona entregas, escaneos y disponibilidad, y debe aceptar o rechazar explícitamente cada oferta.

## 2.4 Restricciones

- El sistema debe desarrollarse utilizando PHP 8.4 con el framework Laravel 12 y Livewire v3.

- La base de datos utilizada es MySQL 8+.

- La interfaz utiliza Bootstrap 5 / AdminLTE con un tema visual de marca propio.

- La autenticación web utiliza sesiones; la autenticación de API utiliza tokens Laravel Sanctum.

- Toda operación de escritura sobre el stock de un producto (reserva, descuento, liberación, reintegro) debe ejecutarse dentro de una transacción de base de datos que aplique bloqueo pesimista de fila (SELECT ... FOR UPDATE / lockForUpdate de Eloquent) sobre el registro del producto.

- Cuando una transacción afecte varios productos, los bloqueos deben adquirirse siempre en orden ascendente de product_id, para evitar interbloqueos entre transacciones concurrentes.

- El sistema requiere un worker de colas (queue:work) y el scheduler de Laravel activos para procesar la expiración de ofertas, el correo de alertas y la detección de inactividad.

## 2.5 Suposiciones y Dependencias

- Se asume que los dueños de negocio y repartidores tienen conocimiento básico de informática y acceso a un dispositivo con navegador.

- El sistema depende de la correcta configuración de MySQL, del servidor de aplicaciones Laravel y del worker de colas.

- Se asume conexión a internet estable para el envío periódico de ubicación GPS y para el polling de ofertas y notificaciones.

- **Limitación conocida del GPS en navegador:** mientras la aplicación del repartidor sea web (y no una PWA instalada o una aplicación nativa), el navegador puede pausar el envío de ubicación cuando la pantalla se bloquea o la pestaña pasa a segundo plano. Para mitigarlo, el panel del repartidor solicita al navegador mantener la pantalla activa cuando la API lo permite y muestra un aviso si detecta que dejó de enviar ubicación. La solución definitiva se difiere a la aplicación móvil o PWA (sección 2.6).

## 2.6 Requerimientos Futuros

- Integración de mapa en vivo (Leaflet/OpenStreetMap) para visualizar la ubicación del repartidor en tiempo real.

- Notificaciones push y actualización en tiempo real mediante WebSockets/Laravel Broadcasting, en reemplazo del polling.

- Módulo de facturación electrónica y planes de suscripción (modelo SaaS comercial).

- Aplicación móvil nativa o PWA instalable para el rol de repartidor.

- Soporte para variantes de producto (talla, color) orientado a un caso de uso de comercio minorista (ej. calzado).

# 3. Requerimientos Específicos

## 3.1 Requisitos Funcionales (resumen)

- **Gestión de negocios:** creación por SuperAdmin (el negocio nace activo), suspensión y reactivación con sincronización del estado del usuario y manejo de las operaciones en curso.

- **Gestión de pedidos:** creación con reserva de inventario bajo bloqueo de fila, oferta a repartidor sujeta a aceptación, cambios de estado según una matriz de transiciones definida, cancelación con liberación de reserva e incidencias/devoluciones.

- **Gestión de inventario:** productos, control de stock, movimientos y alertas de stock mínimo persistidas y notificadas por centro de notificaciones y correo.

- **Trazabilidad:** escaneo de recogida/entrega, timeline visible en el detalle del pedido, envío de ubicación GPS cada 60 segundos.

- **Gestión de repartidores:** creación, edición, estado de disponibilidad automático según carga, límite máximo configurable de pedidos activos y detección de inactividad.

- **Autenticación y autorización:** login diferenciado por rol, aislamiento multi-tenant en todas las consultas.

- **Notificaciones:** eventos de negocio y pedidos dirigidos al rol correspondiente.

- **Reportes:** exportación en PDF de pedidos y repartidores con filtros.

## 3.2 Requisitos No Funcionales

- **Rendimiento:** el sistema debe soportar múltiples negocios y usuarios operando simultáneamente sin degradación perceptible, incluyendo operaciones concurrentes de venta sobre el mismo producto. El polling del panel del repartidor (10 s) debe resolverse con consultas ligeras e indexadas.

- **Seguridad:** autenticación basada en credenciales (sesión web) y tokens (API Sanctum); aislamiento estricto de datos por business_id.

- **Usabilidad:** interfaz sencilla e intuitiva, adaptada a cada rol (panel administrativo para dueño/SuperAdmin, vista simplificada para el repartidor).

- **Escalabilidad:** arquitectura multi-tenant que permite agregar nuevos negocios sin cambios estructurales.

- **Mantenibilidad:** código modular (Livewire components, Services, Requests) cubierto por una suite de pruebas automatizadas, incluyendo pruebas de concurrencia sobre la reserva de stock y pruebas de expiración de ofertas.

- **Consistencia transaccional:** toda operación que modifique el stock debe garantizar que el valor resultante nunca sea negativo, incluso bajo acceso concurrente.

- **Disponibilidad de procesos en segundo plano:** la expiración de ofertas no debe depender de que el repartidor tenga el panel abierto; se ejecuta en el servidor.

## 3.3 Otros Requisitos

- **Compatibilidad:** aplicación web accesible desde los navegadores y sistemas operativos más utilizados.

- **Confiabilidad:** las operaciones de inventario (reserva, descuento, liberación, reintegro) deben mantener la integridad de los datos incluso ante cancelaciones y accesos concurrentes.

- **Trazabilidad de auditoría:** cada movimiento de inventario, escaneo de paquete y cambio de estado de pedido queda registrado con fecha, autor y ubicación cuando aplica.

# 4. Requerimientos Funcionales Detallados

## RF-001 — Gestión de Pedidos con Reserva de Stock

| **Código / Nombre** | RF-001 — Gestión de Pedidos con Reserva de Stock |
| --- | --- |
| **Fecha / Grado Necesidad** | 2026-08-03 / Alta |
| **Descripción** | Permite al dueño de negocio crear un pedido web seleccionando productos; el sistema reserva el stock correspondiente. Define además la matriz de estados y las reglas de cancelación. |
| **Entradas** | Cliente, productos, cantidades, método de pago |
| **Fuente** | Formulario /orders/create |
| **Salida / Destino** | Pedido creado (pending) + reserva de stock → Base de Datos MySQL |
| **Restricciones** | El stock debe ser suficiente; el pedido debe pertenecer al negocio autenticado (business_id). |
| **Proceso** | El sistema abre una transacción, bloquea las filas de los productos con lockForUpdate() en orden ascendente de product_id, valida el stock, crea el pedido y la reserva, y confirma. Si el pedido requiere trazabilidad, crea también el paquete asociado. |
| **Efecto Colateral** | Si la reserva falla o el stock es insuficiente, el pedido se rechaza y no se descuenta inventario. |

### 4.1.1 Concurrencia en la reserva de stock

Dentro de la transacción de creación del pedido, la consulta de stock disponible se ejecuta con bloqueo pesimista de fila (lockForUpdate() de Eloquent, equivalente a SELECT ... FOR UPDATE). Si dos vendedores del mismo negocio intentan vender la última unidad en el mismo instante, la segunda transacción espera a que la primera confirme o revierta antes de leer el stock, evitando que ambas lean un valor mayor a cero y dejen el inventario en negativo.

El bloqueo se libera automáticamente al hacer commit o rollback, por lo que su duración debe ser mínima: solo la validación y el descuento/reserva, sin operaciones adicionales (envío de correos, notificaciones, generación de archivos) dentro del mismo bloque. Cuando el pedido incluye varios productos, las filas se bloquean siempre en orden ascendente de product_id para evitar interbloqueos entre transacciones concurrentes. Si el motor reporta un interbloqueo, la transacción se reintenta un número limitado de veces (por ejemplo, 3) antes de rechazar el pedido.

### 4.1.2 Estados del pedido

Un pedido puede tener los estados: **pending** (pendiente), **offered** (ofrecido a un repartidor, a la espera de su respuesta), **assigned** (aceptado, pendiente de recogida; solo pedidos con trazabilidad), **in_transit** (en camino), **delivered** (entregado), **cancelled** (cancelado) e **incident** (incidencia o devolución).

### 4.1.3 Matriz de transiciones

| **Origen** | **Destino** | **Quién** | **Condición / efecto** |
| --- | --- | --- | --- |
| pending | offered | Dueño | El repartidor es del mismo negocio, no está offline y tiene capacidad (RF-005). Se notifica la oferta. |
| pending | cancelled | Dueño | Sin restricciones. Se libera la reserva de stock. |
| offered | assigned / in_transit | Repartidor | Acepta dentro de 90 s. Con trazabilidad → assigned; simple → in_transit. El repartidor pasa a busy. |
| offered | pending | Repartidor / Sistema / Dueño | Rechazo, expiración de los 90 s o retiro de la oferta por el dueño. Se notifica al dueño (salvo que él la retire). |
| offered | cancelled | Dueño | Se libera la reserva y se retira la oferta del panel del repartidor. |
| assigned | in_transit | Repartidor | Escaneo de recogida (CU14). |
| assigned | cancelled | Dueño | Motivo obligatorio. Se libera la reserva, se notifica al repartidor y este recalcula su disponibilidad. |
| assigned | pending | Dueño | Reasignación (por ejemplo, por inactividad del repartidor). Motivo obligatorio; el repartidor deja de ver el pedido. |
| in_transit | delivered | Repartidor | Escaneo de entrega o marcado como entregado (CU15, CU16). |
| in_transit | incident | Dueño / Repartidor | Cancelación directa no permitida. Se registra incidencia con motivo; la devolución, una vez confirmada, reintegra el stock. |
| delivered | — | — | Estado final. Cualquier ajuste posterior se maneja como devolución (incidencia), no como cancelación. |
| cancelled / incident | — | — | Estados finales. |

### 4.1.4 Reglas de cancelación

- **pending:** se cancela sin restricciones; no hay repartidor involucrado.

- **offered:** se cancela libremente; la oferta desaparece del panel del repartidor y su capacidad se libera.

- **assigned:** se cancela con motivo obligatorio; se notifica al repartidor para que retire el pedido de su lista.

- **in_transit:** no se puede cancelar desde el panel del dueño, porque el paquete ya está en la calle. Solo puede finalizarse como entregado o registrarse como incidencia (devolución). Como los pedidos simples pasan directamente de offered a in_transit al aceptar, su cancelación solo es posible mientras están en pending u offered.

- **delivered:** no se puede cancelar; es un estado final.

- **Incidencia con devolución:** cuando el dueño confirma la recepción del paquete devuelto, el sistema reintegra el stock mediante un movimiento de inventario de tipo devolución, bajo el mismo bloqueo de fila.

Cuando se cancela un pedido, la aplicación del repartidor debe recibir la actualización en su siguiente ciclo de polling (máximo 10 s). Si el repartidor intenta escanear una recogida o entrega sobre un pedido ya cancelado o en incidencia, el sistema rechaza la operación con un mensaje explícito y retira el pedido de "Rutas de Hoy".

## RF-002 — Oferta de Pedido y Aceptación del Repartidor

| **Código / Nombre** | RF-002 — Oferta de Pedido y Aceptación del Repartidor |
| --- | --- |
| **Fecha / Grado Necesidad** | 2026-08-03 / Alta |
| **Descripción** | Permite al dueño ofrecer un pedido pendiente a un repartidor, respetando el tipo de pedido (simple o con trazabilidad) y sujeto a la aceptación explícita del repartidor. |
| **Entradas** | ID de pedido, ID de repartidor |
| **Fuente** | Order List (Livewire) |
| **Salida / Destino** | Pedido en estado offered → aceptado (assigned o in_transit) o devuelto a pending → Repartidor y dueño notificados |
| **Restricciones** | El repartidor debe pertenecer al mismo negocio; el pedido debe estar en pending; el repartidor no debe estar offline ni haber alcanzado su capacidad máxima (las ofertas pendientes cuentan, ver RF-005); el negocio no debe estar suspendido. |
| **Proceso** | El pedido pasa al estado offered y se registra offered_at y offer_expires_at (90 s). El repartidor responde desde su panel. Si acepta, sigue la regla original (trazabilidad → assigned; simple → in_transit) y pasa a busy. Si rechaza o no responde en el tiempo límite, el pedido vuelve a pending y se notifica al dueño para que reasigne. |
| **Efecto Colateral** | Si no se marca busy correctamente tras la aceptación, las métricas de disponibilidad pueden ser inexactas. |

### 4.2.1 Mecanismo de notificación y expiración

- **Entrega de la oferta:** la oferta se persiste como notificación in-app (tabla notifications) y el panel del repartidor la consulta mediante polling cada 10 segundos (Livewire wire:poll). Con 10 s de intervalo, el repartidor dispone en la práctica de al menos 80 s de los 90 s para responder. No se utilizan notificaciones push (ver sección 2.6).

- **Expiración:** al crear la oferta se programa un job en cola (ExpireOrderOffer) con retraso de 90 s. El job verifica que el pedido siga en offered y, de ser así, lo devuelve a pending y notifica al dueño. Como respaldo, el scheduler ejecuta cada minuto un comando que revierte las ofertas vencidas que el job no haya procesado (por ejemplo, por una caída del worker).

- **Idempotencia:** la aceptación y la expiración se ejecutan bajo bloqueo de la fila del pedido y verifican el estado actual; si el repartidor acepta justo cuando la oferta expira, solo una de las dos operaciones prospera y la otra se descarta con mensaje explícito.

- **Motivo del diseño:** esta aceptación evita que un repartidor fuera de servicio (por ejemplo, por una falla del vehículo o una desconexión sin cerrar sesión) acumule ofertas bloqueadas sin que el dueño lo note. Los pedidos ya aceptados se cubren con la regla de inactividad de RF-011.

## RF-003 — Suspensión y Reactivación de Negocios

| **Código / Nombre** | RF-003 — Suspensión y Reactivación de Negocios |
| --- | --- |
| **Fecha / Grado Necesidad** | 2026-08-03 / Alta |
| **Descripción** | Permite al SuperAdmin suspender o reactivar un negocio, bloqueando o restaurando el acceso del dueño, con manejo definido de las operaciones en curso. |
| **Entradas** | ID de negocio |
| **Fuente** | Dashboard SuperAdmin |
| **Salida / Destino** | Estado del negocio y del usuario actualizado → Base de Datos MySQL |
| **Restricciones** | Solo el SuperAdmin puede ejecutar esta acción. |
| **Proceso** | Al suspender, el sistema sincroniza el estado del negocio (suspended) con el del usuario dueño, bloqueando su login web y API de forma inmediata, y aplica las reglas de la sección 4.3.1 sobre los pedidos en curso. |
| **Efecto Colateral** | Si la sincronización falla, el dueño podría seguir accediendo pese a estar suspendido (riesgo de seguridad). |

### 4.3.1 Operaciones en curso al suspender

| **Estado del pedido** | **Tratamiento** |
| --- | --- |
| pending | Se conserva congelado: no puede ofrecerse ni asignarse mientras dure la suspensión. Se retoma al reactivar el negocio. |
| offered | Se retira la oferta y el pedido se cancela automáticamente (motivo: negocio suspendido), liberando la reserva de stock y notificando al repartidor. |
| assigned / in_transit | Ventana de gracia: el token API del repartidor sigue siendo válido únicamente para escanear recogida, escanear entrega, marcar como entregado y registrar incidencia sobre esos pedidos, hasta que pasen a delivered, cancelled o incident. |

- No se permite la creación de nuevos pedidos ni nuevas ofertas mientras el negocio esté suspendido.

- **Tope de la ventana de gracia:** 24 horas desde la suspensión. Vencido el plazo, el sistema cierra los pedidos restantes: los assigned pasan a cancelled (liberando la reserva) y los in_transit pasan a incident (motivo: cierre por suspensión), y el token del repartidor deja de aceptar acciones.

- **Cierre administrativo anticipado:** como el dueño está bloqueado, el SuperAdmin dispone de la acción "Cerrar pedidos en gracia" (CU20), que aplica el mismo cierre antes de las 24 horas, dejando registro del autor y del motivo.

- Una vez entregados o cerrados todos los pedidos, el token del repartidor deja de aceptar nuevas acciones mientras el negocio permanezca suspendido.

- Al reactivar el negocio, el login del dueño y las operaciones normales se restablecen.

Con esto ningún pedido queda "en el limbo" y se protege al cliente final sin comprometer el objetivo de la suspensión: impedir nueva operación comercial.

## RF-004 — Creación de Negocios por SuperAdmin

| **Código / Nombre** | RF-004 — Creación de Negocios por SuperAdmin |
| --- | --- |
| **Fecha / Grado Necesidad** | 2026-08-03 / Alta |
| **Descripción** | Permite al SuperAdmin registrar un nuevo negocio y su dueño desde el panel administrativo, sin registro público. |
| **Entradas** | Datos del negocio (nombre, dirección, teléfono), datos del dueño (nombre, email, contraseña) |
| **Fuente** | Modal "Nuevo Negocio" |
| **Salida / Destino** | Negocio y usuario creados (activos) → Base de Datos MySQL |
| **Restricciones** | El correo del dueño debe ser único; la contraseña debe tener mínimo 8 caracteres. |
| **Proceso** | El sistema crea el usuario y el negocio en una única transacción, evitando estados inconsistentes. El negocio queda activo de inmediato; no existe un flujo de aprobación posterior. |
| **Efecto Colateral** | Si la transacción falla a mitad de camino, no debe quedar un usuario huérfano sin negocio. |

## RF-005 — Gestión de Disponibilidad de Repartidores (con límite de capacidad)

| **Código / Nombre** | RF-005 — Gestión de Disponibilidad de Repartidores (con límite de capacidad) |
| --- | --- |
| **Fecha / Grado Necesidad** | 2026-08-03 / Media |
| **Descripción** | Actualiza automáticamente el estado del repartidor (available/busy) según su carga de pedidos, respetando un límite máximo configurable de pedidos activos simultáneos. |
| **Entradas** | Eventos de oferta, aceptación, rechazo, expiración, cancelación y entrega de pedidos |
| **Fuente** | OrderService |
| **Salida / Destino** | Estado del repartidor actualizado → Base de Datos MySQL |
| **Restricciones** | Pedidos activos = pedidos en estado offered, assigned o in_transit. Un repartidor no puede tener más de N pedidos activos simultáneos, donde N es max_active_orders (valor por defecto: 3; rango configurable de 1 a 8). El repartidor es busy si tiene al menos un pedido assigned o in_transit; solo pasa a available cuando no tiene ninguno. |
| **Proceso** | Al ofrecer un pedido, el sistema valida bajo bloqueo del repartidor que sus pedidos activos (incluidas las ofertas pendientes) sean menores que max_active_orders; si no, el repartidor no aparece como candidato. Al aceptar, rechazar, expirar, cancelar o entregar un pedido, se recalculan los pedidos activos y el estado del repartidor. |
| **Efecto Colateral** | Si la lógica no considera pedidos múltiples, las ofertas pendientes ni el límite de capacidad, el repartidor podría liberarse prematuramente o recibir una carga físicamente inmanejable. |

El límite se define con el campo max_active_orders por repartidor (valor por defecto del negocio: 3). El dueño puede ajustarlo por repartidor desde la gestión de repartidores, dentro del rango de 1 a 8. Contar las ofertas pendientes impide que a un mismo repartidor se le ofrezcan más pedidos de los que podría aceptar.

## RF-006 — Trazabilidad de Paquetes (Timeline)

| **Código / Nombre** | RF-006 — Trazabilidad de Paquetes (Timeline) |
| --- | --- |
| **Fecha / Grado Necesidad** | 2026-08-03 / Media |
| **Descripción** | Muestra al dueño de negocio la línea de tiempo de escaneos (recogida y entrega) de un pedido con trazabilidad. |
| **Entradas** | ID de pedido |
| **Fuente** | Modal "Detalle del Pedido" |
| **Salida / Destino** | Timeline cronológico de escaneos → Interfaz web |
| **Restricciones** | Solo visible para pedidos del propio negocio (aislamiento multi-tenant). |
| **Proceso** | El sistema consulta los package_scans asociados y los ordena cronológicamente (recogida, luego entrega). |
| **Efecto Colateral** | Pedidos sin trazabilidad muestran un mensaje informativo en lugar del timeline. |

## RF-007 — Aislamiento Multi-tenant

| **Código / Nombre** | RF-007 — Aislamiento Multi-tenant |
| --- | --- |
| **Fecha / Grado Necesidad** | 2026-08-03 / Alta |
| **Descripción** | Garantiza que ningún dueño de negocio pueda ver o modificar datos (pedidos, inventario, repartidores) de otro negocio. |
| **Entradas** | Solicitudes autenticadas (web o API) |
| **Fuente** | Todos los controladores y componentes Livewire |
| **Salida / Destino** | Datos filtrados por business_id → Base de Datos MySQL |
| **Restricciones** | Toda consulta a orders, products y delivery_drivers debe incluir el filtro business_id del usuario autenticado. |
| **Proceso** | El sistema valida en cada operación que el recurso solicitado pertenezca al negocio del usuario. |
| **Efecto Colateral** | Una fuga de aislamiento expondría datos sensibles entre negocios competidores. |

## RF-008 — Gestión de Inventario (con alertas persistentes y multicanal)

| **Código / Nombre** | RF-008 — Gestión de Inventario (con alertas persistentes y multicanal) |
| --- | --- |
| **Fecha / Grado Necesidad** | 2026-08-03 / Media |
| **Descripción** | Permite al dueño de negocio administrar productos con control de stock y alertas de stock mínimo persistentes. |
| **Entradas** | Nombre, precio, stock, stock mínimo, unidad |
| **Fuente** | Product Management (Livewire) |
| **Salida / Destino** | Producto creado/actualizado + alerta si aplica → Base de Datos MySQL |
| **Restricciones** | El stock no puede ser negativo. Toda modificación de stock respeta el bloqueo de fila descrito en RF-001. |
| **Proceso** | Cuando una operación (creación, edición, venta, cancelación o devolución) deja el stock por debajo del mínimo definido habiendo estado en o sobre el mínimo, el sistema genera la alerta LowStockAlert como notificación de Laravel persistida en la tabla notifications, visible en el centro de notificaciones del panel hasta que el dueño la marque como leída, y envía en paralelo un correo electrónico (mediante cola, fuera de la transacción de stock). |
| **Efecto Colateral** | Un cálculo incorrecto de stock puede generar sobreventa o alertas falsas. |

### 4.8.1 Regla de disparo y rearme

- **Disparo:** la alerta se genera una sola vez, al cruzar el mínimo (stock anterior ≥ stock mínimo y stock nuevo < stock mínimo). Las ventas posteriores que mantengan el producto bajo el mínimo no generan nuevas alertas ni correos.

- **Rearme:** la alerta se rearma cuando el stock vuelve a ser mayor o igual al mínimo (por ejemplo, tras reponer inventario o por una devolución). Si luego vuelve a cruzar el mínimo, se genera una nueva alerta.

- **Stock agotado:** si el stock llega a cero, se genera una alerta adicional de producto agotado, una única vez por ciclo.

- **Persistencia:** la alerta no depende de que el dueño esté conectado cuando se genera, y no se limita a un registro en log.

## RF-009 — Reportes en PDF

| **Código / Nombre** | RF-009 — Reportes en PDF |
| --- | --- |
| **Fecha / Grado Necesidad** | 2026-08-03 / Baja |
| **Descripción** | Permite generar reportes de pedidos o repartidores en formato PDF con filtros de fecha, estado y otros criterios. |
| **Entradas** | Rango de fechas, estado, repartidor, método de pago |
| **Fuente** | Endpoints /reports/orders/pdf y /reports/drivers/pdf |
| **Salida / Destino** | Archivo PDF descargable → Usuario final |
| **Restricciones** | El rango de fechas debe ser válido; el reporte solo incluye datos del negocio autenticado. |
| **Proceso** | El sistema consulta los registros filtrados y los renderiza con la librería dompdf. |
| **Efecto Colateral** | Reportes con grandes volúmenes de datos pueden tardar más en generarse. |

## RF-010 — Autenticación y Roles

| **Código / Nombre** | RF-010 — Autenticación y Roles |
| --- | --- |
| **Fecha / Grado Necesidad** | 2026-08-03 / Alta |
| **Descripción** | Permite a cada tipo de usuario (SuperAdmin, Dueño, Repartidor) iniciar sesión con credenciales propias y ser dirigido a su panel correspondiente. |
| **Entradas** | Credenciales (email/teléfono + contraseña) |
| **Fuente** | Formularios de login web y driver |
| **Salida / Destino** | Sesión iniciada o token Sanctum → Panel correspondiente al rol |
| **Restricciones** | La cuenta debe estar activa (status = active); los negocios suspendidos bloquean el login del dueño (ver RF-003 para el manejo de operaciones en curso y el acceso acotado del repartidor). |
| **Proceso** | El sistema valida credenciales, verifica el estado de la cuenta y redirige según el rol. |
| **Efecto Colateral** | Los intentos con cuenta suspendida o inactiva deben rechazarse con un mensaje claro. |

## RF-011 — Ubicación GPS e Inactividad del Repartidor

| **Código / Nombre** | RF-011 — Ubicación GPS e Inactividad del Repartidor |
| --- | --- |
| **Fecha / Grado Necesidad** | 2026-08-03 / Media |
| **Descripción** | Registra la ubicación aproximada del repartidor mientras tiene entregas en curso y detecta repartidores inactivos con pedidos aceptados. |
| **Entradas** | Latitud, longitud, marca de tiempo (enviadas por el cliente del repartidor) |
| **Fuente** | Panel del repartidor (cliente web, endpoint API con token Sanctum) |
| **Salida / Destino** | latitud, longitud y last_seen_at del repartidor → Base de Datos MySQL; notificación al dueño si hay inactividad |
| **Restricciones** | El envío ocurre cada 60 segundos y solo mientras exista al menos un pedido en in_transit; se suspende cuando no hay entregas en curso y se reanuda al pasar un pedido a in_transit. |
| **Proceso** | Cada envío actualiza latitud, longitud y last_seen_at. Un comando programado (cada minuto) revisa a los repartidores con pedidos assigned o in_transit cuyo last_seen_at supere el umbral de inactividad (por defecto 15 minutos, configurable por negocio): genera una notificación al dueño ("Repartidor sin conexión") y marca al repartidor como offline. |
| **Efecto Colateral** | Un envío demasiado frecuente satura el servidor y la batería; uno demasiado espaciado vuelve inútil la trazabilidad. Ver la limitación del GPS en navegador (sección 2.5). |

- **Acciones del dueño ante inactividad:** para pedidos assigned, puede reasignar (assigned → pending, con motivo) y volver a ofrecer el pedido a otro repartidor; para pedidos in_transit, puede registrar una incidencia, ya que el paquete está físicamente con el repartidor.

- Cuando el repartidor retoma la conexión (nuevo envío de ubicación o inicio de sesión), vuelve a su estado según su carga: busy si conserva pedidos activos, available en caso contrario.

# 5. Entrevista y Tabulación (Investigación de Mercado)

**Nota metodológica:** al igual que en el ejercicio de referencia de la asignatura, esta entrevista y encuesta fueron simuladas por el autor del proyecto como parte del proceso de levantamiento de requerimientos, representando el perfil de un dueño de negocio potencial (bodega de calzado / negocio de comida) al que puede ir dirigido FastLogi. Este perfil se usó únicamente como caso ilustrativo para validar necesidades comunes a cualquier pequeño negocio con inventario y domicilios (control de stock, reportes, roles diferenciados); no implica que el alcance del producto esté limitado a ese sector.

## 5.1 Entrevista

**1. ¿Cuál es el tiempo estimado para el desarrollo del proyecto?**

Respuesta: El desarrollo del núcleo funcional (multi-tenancy, pedidos, inventario, trazabilidad, repartidores) se estima entre 3 y 4 meses, considerando análisis, diseño, desarrollo, pruebas y despliegue, dado que se trata de un proyecto individual de trabajo de grado.

**2. ¿Cuál es el presupuesto estimado para el desarrollo del proyecto?**

Respuesta: Al ser un proyecto de grado desarrollado por el autor, el costo principal es el tiempo de desarrollo propio; no se contemplan costos de licenciamiento, ya que se usan tecnologías de código abierto (Laravel, MySQL, Livewire).

**3. ¿Tiene definida alguna metodología para el desarrollo del proyecto?**

Respuesta: Sí, se utilizó una metodología ágil basada en iteraciones cortas: se identificaron "indispensables" funcionales, se implementaron uno a uno, y cada uno se validó con pruebas automatizadas antes de continuar con el siguiente.

**4. ¿Tiene establecida la o las tecnologías en las que desea sea desarrollado el proyecto?**

Respuesta: Sí. Backend en PHP 8.4 con Laravel 12, frontend reactivo con Livewire v3, base de datos MySQL, autenticación API con Laravel Sanctum, y Bootstrap 5/AdminLTE para la interfaz.

**5. ¿Se cuenta con el apoyo de las instancias administrativas y financieras de la universidad?**

Respuesta: El proyecto se desarrolla en el marco académico de la Corporación Universitaria Misión Paz, bajo la asignatura de Trabajo de Grado, con acompañamiento del director asignado.

**6. ¿Cuál cree que es el principal problema que enfrenta su negocio hoy con la gestión de pedidos e inventario?**

Respuesta: La dependencia de sistemas que no descuentan correctamente el inventario al momento de la venta, generando descuadres entre el stock real y el registrado, además de tiempos de espera largos cuando el sistema falla y se requiere soporte manual.

**7. ¿Quién le brinda soporte actualmente cuando el sistema falla?**

Respuesta: Un desarrollador externo que debe desplazarse físicamente al negocio para corregir el problema, lo que implica horas o incluso días de inventario descuadrado mientras se resuelve.

**8. ¿Qué tan importante es para usted que el sistema sea accesible desde el celular?**

Respuesta: Muy importante, ya que gran parte de la operación diaria (consultar stock, ver pedidos) se realiza fuera del computador principal del negocio.

**9. ¿Qué información considera indispensable que el sistema gestione?**

Respuesta: Identificación del producto, stock disponible, precio, y alertas cuando un producto está por agotarse.

**10. ¿Cómo preferiría recibir soporte en caso de problemas?**

Respuesta: Preferiblemente por un canal digital (chat o correo electrónico) que permita una respuesta rápida sin depender de visitas presenciales.

**11. ¿Es necesario que el sistema funcione con varios usuarios al mismo tiempo?**

Respuesta: Sí, varios empleados pueden estar registrando ventas o consultando inventario de forma simultánea, por lo que el sistema debe manejar correctamente las operaciones concurrentes.

**12. ¿Qué tipo de informes le gustaría generar con el sistema?**

Respuesta: Informes de ventas diarias, semanales y mensuales, así como reporte de productos más vendidos e inventario actual.

**13. ¿Necesita que el sistema se integre con otras herramientas que ya utiliza?**

Respuesta: Sería valioso en una fase futura integrarlo con el sistema de contabilidad actual, aunque no es indispensable para una primera versión.

**14. ¿Qué tan dispuesto estaría a migrar de su sistema actual a uno nuevo?**

Respuesta: Muy dispuesto, siempre que el nuevo sistema resuelva de forma confiable el problema del descuento incorrecto de inventario y no implique una curva de aprendizaje larga para el personal.

**15. ¿Cómo le gustaría que fuera la interfaz del sistema?**

Respuesta: Intuitiva y fácil de usar, con un diseño moderno, que no requiera capacitación extensa para el personal de venta.

**16. ¿Qué medidas de seguridad considera importantes para proteger la información del negocio?**

Respuesta: Que cada usuario tenga su propio acceso con permisos definidos, y que la información de un negocio nunca sea visible para otro negocio que use la misma plataforma.

## 5.2 Tabulación de Encuesta

Encuesta aplicada a un (1) dueño de negocio simulado, representativo del perfil objetivo de FastLogi (bodega de calzado / negocio de comida con inventario propio).

**¿Cuál es el principal objetivo que desea lograr con este software de gestión?**

Mejorar la eficiencia en la gestión del inventario y ventas, asegurando que los productos estén bien controlados y accesibles para los clientes.

**¿Qué tareas debe poder realizar cada tipo de usuario?**

Administrador: gestionar inventario, precios, promociones, usuarios y generar informes. Empleados: registrar ventas, actualizar stock y atender a los clientes. Clientes: consultar disponibilidad de productos.

**Describa el proceso actual para gestionar inventario.**

Actualmente el inventario se gestiona con el sistema anterior o de forma manual, con registros que no siempre se actualizan en tiempo real, lo que puede llevar a errores y falta de precisión al momento de una venta.

**¿Qué aspectos desea mejorar con el nuevo software?**

Reducir errores en la gestión del stock (que el sistema descuente correctamente cada venta) y facilitar la generación de informes detallados sobre ventas y productos.

**¿Cómo le gustaría que fuera la interfaz del sistema?**

Intuitiva y fácil de usar, con un diseño moderno.

**¿Necesita que este software se integre con otros sistemas o herramientas que ya utiliza?**

Sí, sería ideal que en una fase futura se integre con el sistema de contabilidad actual del negocio.

**¿Hay funciones específicas que deban ser fácilmente accesibles?**

Sí, las funciones de búsqueda de productos, actualización de stock y generación de informes deben ser muy accesibles.

## 5.3 Conclusiones de la Investigación

- El principal dolor identificado es la falta de confiabilidad en el descuento de inventario al momento de la venta, y la dependencia de soporte presencial para corregir fallas.

- Se requiere un sistema multiusuario, con roles diferenciados y restricción de información sensible por perfil.

- La generación de informes (ventas, productos más vendidos, inventario) es un requerimiento explícito y prioritario.

- La interfaz debe ser simple e intuitiva, dado que el personal operativo no necesariamente tiene formación técnica.

- Estos hallazgos validan y refuerzan los requerimientos funcionales de la Sección 4 (gestión de inventario, reservas de stock bajo bloqueo de fila, reportes, aislamiento multi-tenant y roles diferenciados).

# 6. Casos de Uso

El modelo de casos de uso comprende tres actores (SuperAdmin, Dueño de Negocio y Repartidor) y los casos CU01 a CU20. Respecto a la versión 1.0 se modificaron CU03 y CU09 y se agregaron CU18, CU19 y CU20. La Figura 1 presenta el diagrama de casos de uso y la tabla siguiente resume la asignación de casos por actor.

## 6.1 Actores y Casos de Uso

*Figura 1. Diagrama de casos de uso de FastLogi (v1.2)*

| **Actor** | **Casos de uso** |
| --- | --- |
| SuperAdmin | CU01, CU02, CU03, CU04, CU05, CU20 |
| Dueño de Negocio | CU01, CU06, CU07, CU08, CU09, CU10, CU11, CU12, CU18, CU19 |
| Repartidor | CU01, CU09 (responder oferta), CU13, CU14, CU15, CU16, CU17, CU19 (reportar incidencia) |
| Sistema (procesos programados) | Expiración de ofertas (CU09), cierre por vencimiento de la ventana de gracia (CU20), detección de inactividad (CU17) |

## 6.2 Listado de Casos de Uso

- CU01 - Iniciar sesión

- CU02 - Crear negocio (SuperAdmin)

- CU03 - Consultar negocios (SuperAdmin)

- CU04 - Suspender / Reactivar negocio

- CU05 - Ver métricas globales

- CU06 - Configurar negocio

- CU07 - Gestionar repartidores (incluye límite de capacidad)

- CU08 - Crear pedido

- CU09 - Ofrecer pedido y confirmar aceptación del repartidor

- CU10 - Ver detalle / timeline de pedido

- CU11 - Gestionar inventario

- CU12 - Generar reportes PDF

- CU13 - Ver pedidos asignados y ofertas

- CU14 - Escanear recogida

- CU15 - Escanear entrega

- CU16 - Marcar pedido entregado

- CU17 - Actualizar ubicación GPS (cada 60 segundos)

- CU18 - Cancelar pedido

- CU19 - Registrar incidencia / devolución de pedido

- CU20 - Cerrar pedidos en ventana de gracia (SuperAdmin)

## 6.3 Descripción Detallada de Casos de Uso

### CU01 — Iniciar Sesión

**Descripción:** Permite a cualquier usuario (SuperAdmin, Dueño, Repartidor) acceder al sistema con sus credenciales.

**Campos / pasos:**

- Email o teléfono

- Contraseña

**Validaciones:**

- La cuenta debe estar activa.

- El sistema redirige según el rol del usuario.

- Los negocios suspendidos bloquean el login del dueño asociado.

**Restricciones:** Máximo de intentos fallidos según la configuración de seguridad de Laravel.

### CU02 — Crear Negocio

**Descripción:** El SuperAdmin registra un nuevo negocio junto con los datos de su dueño, sin necesidad de registro público.

**Campos / pasos:**

- Nombre del negocio, teléfono, dirección

- Nombre del dueño, email, contraseña inicial

**Validaciones:**

- El correo debe ser único.

- La contraseña debe tener mínimo 8 caracteres.

- El negocio queda activo de inmediato.

**Restricciones:** Solo accesible por el rol SuperAdmin.

### CU03 — Consultar Negocios

**Descripción:** El SuperAdmin consulta el listado de negocios, filtra por estado (activo, suspendido) y accede al detalle y a las acciones disponibles sobre cada uno.

**Campos / pasos:**

- Filtro por estado y búsqueda por nombre

**Validaciones:**

- Muestra el número de pedidos assigned/in_transit en ventana de gracia para los negocios suspendidos.

**Restricciones:** Solo accesible por el rol SuperAdmin.

### CU04 — Suspender / Reactivar Negocio

**Descripción:** El SuperAdmin suspende o reactiva un negocio y su usuario dueño.

**Campos / pasos:**

- Negocio seleccionado

- Acción: suspender o reactivar

**Validaciones:**

- Al suspender se aplican las reglas de RF-003 sobre pedidos pending, offered, assigned e in_transit.

- Al reactivar, el login del dueño y la operación normal se restablecen.

**Restricciones:** Solo accesible por el rol SuperAdmin.

### CU08 — Crear Pedido

**Descripción:** El dueño de negocio registra un nuevo pedido seleccionando productos, cantidades y método de pago.

**Campos / pasos:**

- Cliente (nombre, teléfono, dirección)

- Productos y cantidades

- Método de pago

- ¿Requiere trazabilidad?

**Validaciones:**

- Debe haber stock suficiente para cada producto, verificado bajo bloqueo de fila y en orden de product_id (RF-001).

- El pedido queda en estado pending.

- Si requiere trazabilidad, se crea el paquete asociado.

- El negocio no debe estar suspendido.

**Restricciones:** Solo se pueden usar productos del propio negocio.

### CU09 — Ofrecer Pedido y Confirmar Aceptación del Repartidor

**Descripción:** El dueño de negocio ofrece un pedido pendiente a un repartidor disponible, respetando su capacidad máxima; el repartidor debe aceptar o rechazar explícitamente antes de que el pedido quede formalmente asignado.

**Campos / pasos:**

- Pedido seleccionado

- Repartidor seleccionado (candidato con capacidad disponible)

- Respuesta del repartidor: aceptar / rechazar, dentro de una ventana de 90 segundos

**Validaciones:**

- El repartidor debe pertenecer al mismo negocio y no estar offline.

- El pedido debe estar en estado pending.

- El repartidor no debe haber alcanzado su límite máximo de pedidos activos, contando las ofertas pendientes (RF-005).

- Si acepta, el repartidor queda busy y el pedido sigue la regla de transición según trazabilidad.

- Si rechaza o no responde en la ventana de tiempo, el pedido vuelve a pending y se notifica al dueño. La expiración la ejecuta el servidor (RF-002).

**Restricciones:** Los pedidos con trazabilidad pasan a assigned tras la aceptación; los pedidos simples pasan directo a in_transit.

### CU10 — Ver Detalle / Timeline de Pedido

**Descripción:** El dueño consulta el detalle completo de un pedido, incluyendo el historial de escaneos y de cambios de estado si aplica.

**Campos / pasos:**

- Pedido seleccionado

**Validaciones:**

- Solo visible para pedidos del propio negocio.

- Los pedidos sin trazabilidad muestran un mensaje informativo en lugar del timeline.

**Restricciones:** El timeline se ordena cronológicamente (recogida antes que entrega).

### CU11 — Gestionar Inventario

**Descripción:** El dueño administra los productos de su negocio: creación, edición, eliminación y control de stock.

**Campos / pasos:**

- Nombre, precio, stock, stock mínimo, unidad, código de barras

**Validaciones:**

- El stock no puede ser negativo.

- Al cruzar el mínimo se genera una alerta persistente en el centro de notificaciones y por correo electrónico, una sola vez hasta que el stock se reponga (RF-008).

**Restricciones:** Solo visible si el negocio tiene el módulo de inventario activado.

### CU14 — Escanear Recogida

**Descripción:** El repartidor confirma la recogida de un paquete asociado a un pedido con trazabilidad.

**Campos / pasos:**

- Pedido seleccionado

- Ubicación GPS (opcional)

**Validaciones:**

- El pedido debe estar en estado assigned.

- El paquete cambia a picked_up y el pedido a in_transit.

- Se rechaza la operación si el pedido fue cancelado o está en incidencia (RF-001).

**Restricciones:** Solo el repartidor asignado puede ejecutar el escaneo.

### CU15 — Escanear Entrega

**Descripción:** El repartidor confirma la entrega de un paquete de un pedido con trazabilidad.

**Campos / pasos:**

- Pedido seleccionado

- Ubicación GPS (opcional)

**Validaciones:**

- El pedido debe estar en estado in_transit.

- El paquete cambia a delivered y el pedido a delivered.

**Restricciones:** Solo el repartidor asignado puede ejecutar el escaneo.

### CU16 — Marcar Pedido Entregado

**Descripción:** El repartidor confirma la entrega final del pedido, ya sea simple o con trazabilidad.

**Campos / pasos:**

- Pedido seleccionado

**Validaciones:**

- El pedido debe estar en estado in_transit.

- Se finaliza la reserva de stock.

- Se recalcula la disponibilidad del repartidor, respetando el límite máximo de pedidos activos (RF-005).

**Restricciones:** Dispara la notificación OrderDelivered al dueño del negocio.

### CU17 — Actualizar Ubicación GPS

**Descripción:** El cliente del repartidor envía su ubicación cada 60 segundos mientras tiene al menos un pedido en in_transit.

**Campos / pasos:**

- Latitud y longitud

**Validaciones:**

- Se actualiza last_seen_at.

- El envío se suspende sin pedidos in_transit y se reanuda automáticamente.

- Si un repartidor con pedidos assigned o in_transit supera el umbral de inactividad, se notifica al dueño (RF-011).

**Restricciones:** Solo el repartidor autenticado puede enviar su propia ubicación.

### CU18 — Cancelar Pedido

**Descripción:** El dueño cancela un pedido según las reglas de la matriz de estados.

**Campos / pasos:**

- Pedido seleccionado

- Motivo (obligatorio si el pedido está assigned)

**Validaciones:**

- Permitido solo en pending, offered y assigned.

- Libera la reserva de stock bajo bloqueo de fila.

- Si hay repartidor involucrado, se le notifica y recalcula su disponibilidad.

**Restricciones:** No se permite cancelar pedidos in_transit ni delivered; para ellos aplica CU19.

### CU19 — Registrar Incidencia / Devolución de Pedido

**Descripción:** El dueño o el repartidor registran una incidencia sobre un pedido en camino; el dueño confirma la devolución cuando el paquete regresa.

**Campos / pasos:**

- Pedido seleccionado

- Motivo de la incidencia

- Confirmación de devolución (dueño)

**Validaciones:**

- El pedido debe estar en in_transit (o delivered, para devoluciones posteriores).

- Al confirmar la devolución, el stock se reintegra bajo bloqueo de fila.

- Los pedidos in_transit de un negocio suspendido pueden pasar a incident por cierre (RF-003).

**Restricciones:** El estado incident es final.

### CU20 — Cerrar Pedidos en Ventana de Gracia

**Descripción:** El SuperAdmin cierra anticipadamente los pedidos assigned e in_transit de un negocio suspendido; el sistema lo hace de forma automática a las 24 horas.

**Campos / pasos:**

- Negocio suspendido seleccionado

- Motivo del cierre

**Validaciones:**

- Los pedidos assigned pasan a cancelled y los in_transit a incident.

- Se libera la reserva correspondiente y se notifica a los repartidores.

- Se registra autor, fecha y motivo.

**Restricciones:** Solo accesible por el rol SuperAdmin y solo para negocios suspendidos.

# 7. Historias de Usuario

### HU01 — Crear y administrar negocios

| **ID** | **Rol** | **Característica** | **Razón / Resultado** |
| --- | --- | --- | --- |
| HU01 | SuperAdmin | Crear y administrar negocios | Para incorporar nuevos negocios a la plataforma sin depender de registro público. |

**Escenario 1:** El SuperAdmin ingresa los datos del negocio y del dueño. → Envía el formulario "Nuevo Negocio". → El sistema crea el negocio y el usuario dueño, ambos activos.

**Escenario 2:** El SuperAdmin ingresa un correo ya registrado. → Envía el formulario con correo duplicado. → El sistema rechaza la creación y muestra un mensaje de error.

### HU02 — Suspender y reactivar negocios

| **ID** | **Rol** | **Característica** | **Razón / Resultado** |
| --- | --- | --- | --- |
| HU02 | SuperAdmin | Suspender y reactivar negocios | Para controlar el acceso de negocios que incumplen condiciones de la plataforma. |

**Escenario 1:** El SuperAdmin suspende un negocio activo con pedidos en curso. → Selecciona "Suspender". → El dueño queda bloqueado; los pedidos offered se cancelan, los pending se congelan y los assigned/in_transit conservan una ventana de gracia de hasta 24 horas (RF-003).

**Escenario 2:** El SuperAdmin reactiva un negocio suspendido. → Selecciona "Reactivar". → El negocio y el usuario vuelven a estado activo; el login funciona de nuevo.

**Escenario 3:** Un negocio suspendido tiene pedidos que el repartidor no cerró. → Transcurren 24 horas o el SuperAdmin selecciona "Cerrar pedidos en gracia". → Los assigned se cancelan, los in_transit pasan a incidencia y el token del repartidor deja de aceptar acciones.

### HU03 — Crear pedidos con reserva de inventario

| **ID** | **Rol** | **Característica** | **Razón / Resultado** |
| --- | --- | --- | --- |
| HU03 | Dueño de Negocio | Crear pedidos con reserva de inventario | Para registrar ventas sin descuadrar el stock disponible, incluso bajo ventas concurrentes. |

**Escenario 1:** El dueño selecciona productos con stock suficiente. → Envía el formulario de nuevo pedido. → El pedido se crea y el stock queda reservado bajo bloqueo de fila (RF-001).

**Escenario 2:** El dueño selecciona una cantidad mayor al stock disponible. → Envía el formulario. → El sistema rechaza el pedido y no descuenta inventario.

**Escenario 3:** Dos empleados intentan vender la última unidad de un producto al mismo tiempo. → Ambos envían el formulario casi simultáneamente. → El bloqueo de fila serializa las transacciones; solo la primera confirma la venta y la segunda recibe el rechazo por stock insuficiente.

### HU03b — Cancelar un pedido según su estado

| **ID** | **Rol** | **Característica** | **Razón / Resultado** |
| --- | --- | --- | --- |
| HU03b | Dueño de Negocio | Cancelar un pedido según su estado | Para poder revertir una venta sin generar conflictos logísticos ni inconsistencias de inventario. |

**Escenario 1:** El pedido está en estado pending. → El dueño cancela el pedido. → Se libera la reserva de stock de inmediato, sin notificar a ningún repartidor.

**Escenario 2:** El pedido está en estado offered. → El dueño cancela el pedido. → Se libera la reserva, la oferta desaparece del panel del repartidor y su capacidad se libera.

**Escenario 3:** El pedido está en estado assigned. → El dueño cancela el pedido indicando un motivo. → Se libera la reserva; se notifica al repartidor asignado, quien deja de ver el pedido en su lista y recalcula su disponibilidad.

**Escenario 4:** El pedido está en estado in_transit. → El dueño intenta cancelar el pedido. → El sistema no permite la cancelación directa; ofrece registrar una incidencia/devolución. Al confirmar la devolución del paquete, el stock se reintegra.

### HU04 — Ofrecer un pedido a un repartidor y esperar su aceptación

| **ID** | **Rol** | **Característica** | **Razón / Resultado** |
| --- | --- | --- | --- |
| HU04 | Dueño de Negocio | Ofrecer un pedido a un repartidor y esperar su aceptación | Para que solo repartidores realmente disponibles reciban y confirmen la entrega. |

**Escenario 1:** Hay un pedido pendiente y un repartidor disponible del mismo negocio, dentro de su límite de capacidad. → El dueño ofrece el pedido. → El repartidor ve la oferta en su panel (polling cada 10 s) y tiene 90 segundos para aceptar o rechazar.

**Escenario 2:** El repartidor acepta la oferta dentro del tiempo límite. → Confirma la aceptación. → El pedido cambia de estado según su tipo y el repartidor queda busy; se le notifica la asignación confirmada.

**Escenario 3:** El repartidor no responde dentro de la ventana de tiempo, o rechaza explícitamente. → Expira el tiempo límite (procesado por el servidor) o el repartidor rechaza. → El pedido vuelve a pending y se notifica al dueño para reasignar.

### HU05 — Consultar el detalle y timeline de un pedido

| **ID** | **Rol** | **Característica** | **Razón / Resultado** |
| --- | --- | --- | --- |
| HU05 | Dueño de Negocio | Consultar el detalle y timeline de un pedido | Para verificar el estado y la trazabilidad de una entrega. |

**Escenario 1:** El pedido tiene trazabilidad con escaneos registrados. → El dueño abre el detalle del pedido. → Se muestra el timeline cronológico de recogida y entrega.

**Escenario 2:** El pedido es simple (sin trazabilidad). → El dueño abre el detalle del pedido. → Se muestra un mensaje indicando que no hay trazabilidad detallada.

### HU06 — Escanear recogida y entrega de paquetes

| **ID** | **Rol** | **Característica** | **Razón / Resultado** |
| --- | --- | --- | --- |
| HU06 | Repartidor | Escanear recogida y entrega de paquetes | Para dejar evidencia del ciclo de vida físico del pedido. |

**Escenario 1:** El repartidor tiene un pedido en estado assigned. → Escanea la recogida. → El paquete pasa a picked_up y el pedido a in_transit.

**Escenario 2:** El repartidor tiene un pedido en estado in_transit. → Escanea la entrega. → El paquete pasa a delivered y se finaliza la reserva de stock.

**Escenario 3:** El repartidor intenta escanear un pedido que el dueño canceló. → Ejecuta el escaneo. → El sistema rechaza la operación con un mensaje explícito y retira el pedido de "Rutas de Hoy".

### HU07 — Actualizar disponibilidad automáticamente respetando un límite de pedidos activos

| **ID** | **Rol** | **Característica** | **Razón / Resultado** |
| --- | --- | --- | --- |
| HU07 | Repartidor | Actualizar disponibilidad automáticamente respetando un límite de pedidos activos | Para que el sistema refleje con precisión si el repartidor puede recibir más pedidos, sin sobrecargarlo. |

**Escenario 1:** El repartidor entrega su único pedido activo. → Marca el pedido como entregado. → El estado del repartidor cambia a available.

**Escenario 2:** El repartidor tiene más de un pedido activo y entrega uno, sin alcanzar aún el máximo. → Marca un pedido como entregado. → Permanece busy hasta entregar todos sus pedidos activos restantes.

**Escenario 3:** El repartidor ya tiene el número máximo configurado de pedidos activos (por defecto 3), contando las ofertas pendientes. → El dueño intenta ofrecerle un nuevo pedido. → El sistema no lo presenta como candidato hasta que libere capacidad.

### HU08 — Enviar ubicación GPS cada 60 segundos mientras hay una entrega en curso

| **ID** | **Rol** | **Característica** | **Razón / Resultado** |
| --- | --- | --- | --- |
| HU08 | Repartidor / Sistema | Enviar ubicación GPS cada 60 segundos mientras hay una entrega en curso | Para que el dueño conozca la posición aproximada del repartidor sin agotar la batería del dispositivo ni saturar el servidor. |

**Escenario 1:** El repartidor tiene la sesión activa y al menos un pedido in_transit. → El cliente móvil envía coordenadas cada 60 segundos. → El sistema actualiza latitud, longitud y last_seen_at.

**Escenario 2:** El repartidor no tiene pedidos in_transit. → No hay entregas en curso. → El envío periódico se suspende y se reanuda automáticamente cuando un pedido pase a in_transit.

### HU09 — Detectar un repartidor inactivo y reasignar sus pedidos

| **ID** | **Rol** | **Característica** | **Razón / Resultado** |
| --- | --- | --- | --- |
| HU09 | Dueño de Negocio | Detectar un repartidor inactivo y reasignar sus pedidos | Para que un pedido ya aceptado no quede bloqueado si el repartidor se desconecta o sufre una falla. |

**Escenario 1:** Un repartidor con pedidos assigned no envía actividad durante más tiempo que el umbral (15 min por defecto). → El sistema ejecuta la revisión programada. → Se notifica al dueño, el repartidor queda offline y el dueño puede reasignar los pedidos assigned (vuelven a pending, con motivo).

**Escenario 2:** Un repartidor con un pedido in_transit permanece inactivo más allá del umbral. → El sistema notifica al dueño. → El dueño puede registrar una incidencia sobre ese pedido, pues el paquete está con el repartidor.

**Escenario 3:** El repartidor retoma la conexión. → Envía ubicación o inicia sesión. → Su estado se recalcula: busy si conserva pedidos activos, available si no.

### HU10 — Recibir alertas de stock bajo sin repetición

| **ID** | **Rol** | **Característica** | **Razón / Resultado** |
| --- | --- | --- | --- |
| HU10 | Dueño de Negocio | Recibir alertas de stock bajo sin repetición | Para enterarme a tiempo de que debo reponer un producto sin recibir un correo por cada venta. |

**Escenario 1:** El stock de un producto pasa de igual o mayor al mínimo a menor que el mínimo. → Se confirma la venta. → Se genera una notificación persistente en el centro de notificaciones y se envía un correo.

**Escenario 2:** El producto ya está bajo el mínimo y se registra otra venta. → Se confirma la venta. → No se genera una nueva alerta ni correo.

**Escenario 3:** El dueño repone el stock hasta igualar o superar el mínimo y luego el stock vuelve a bajar del mínimo. → Se confirma la venta. → Se genera una nueva alerta.

# 8. Diagramas de Actividades

Los diagramas de actividades de cada rol (Figuras 2 a 6) incluyen los nodos de decisión vigentes en esta versión y se acompañan de su descripción paso a paso. Los flujos del dueño y del repartidor se dividen en dos figuras cada uno para mantener la legibilidad.

## 8.1 SuperAdmin

*Figura 2. Diagrama de actividades del SuperAdmin*

- Inicia sesión y accede a /superadmin/dashboard.

- Consulta las métricas globales y el listado de negocios.

- Decisión: ¿crear, suspender o reactivar un negocio?

- Crear: completa el formulario "Nuevo Negocio"; el sistema crea usuario y negocio en una transacción y el negocio queda activo.

- Suspender: el sistema bloquea al dueño, cancela las ofertas, congela los pedidos pendientes y abre la ventana de gracia de 24 horas.

- Decisión: ¿hay pedidos en ventana de gracia? Si el SuperAdmin selecciona "Cerrar pedidos en gracia" (o transcurren 24 horas), el sistema cancela los assigned y registra incidencia en los in_transit.

- Reactivar: el negocio y el dueño vuelven a estado activo.

## 8.2 Dueño de Negocio

*Figura 3. Diagrama de actividades del Dueño: creación y oferta de pedidos*

*Figura 4. Diagrama de actividades del Dueño: cancelación, incidencias e inactividad del repartidor*

- Inicia sesión y accede a /dashboard.

- Crea un pedido: el sistema bloquea las filas de los productos (orden de product_id), valida el stock y reserva. Decisión: ¿hay stock suficiente? No → rechaza el pedido; Sí → pedido en pending.

- Ofrece el pedido a un repartidor candidato (mismo negocio, no offline, con capacidad). El pedido pasa a offered.

- Decisión: ¿el repartidor acepta dentro de 90 segundos? No (rechaza o expira) → el pedido vuelve a pending y se notifica al dueño, que reasigna. Sí → el pedido pasa a assigned (con trazabilidad) o in_transit (simple).

- Decisión: ¿necesita cancelar? Si el pedido está en pending, offered o assigned, cancela (motivo obligatorio en assigned) y se libera la reserva. Si está in_transit, registra incidencia.

- Ante una notificación de inactividad del repartidor, reasigna los pedidos assigned o registra incidencia sobre los in_transit.

- Consulta el timeline, gestiona repartidores e inventario, atiende las alertas de stock y genera reportes PDF.

## 8.3 Repartidor

*Figura 5. Diagrama de actividades del Repartidor: respuesta a la oferta*

*Figura 6. Diagrama de actividades del Repartidor: recogida, GPS y entrega*

- Inicia sesión con teléfono y contraseña y accede a /driver/dashboard ("Rutas de Hoy").

- El panel consulta cada 10 segundos nuevas ofertas y cambios en sus pedidos.

- Decisión: ¿acepta la oferta dentro de 90 segundos? Sí → pasa a busy y el pedido queda assigned o in_transit; No → el pedido regresa a pending.

- Si el pedido tiene trazabilidad: escanea la recogida (assigned → in_transit). Decisión: ¿el pedido fue cancelado? Si es así, el sistema rechaza el escaneo y retira el pedido de la lista.

- Mientras exista al menos un pedido in_transit, el cliente envía la ubicación GPS cada 60 segundos.

- Escanea la entrega o marca el pedido como entregado; el sistema recalcula su disponibilidad (busy o available).

- Si hay un problema con el paquete, registra una incidencia.

# 9. Manual de Instalación

## Requisitos del Sistema

- PHP 8.4 o superior.

- Composer.

- MySQL 8 o superior.

- Node.js y npm.

## Pasos de Instalación

- Clonar el repositorio: git clone https://github.com/TheBug31/FastLogi-Laravel-App.git

- Instalar dependencias PHP: composer install

- Instalar dependencias Node.js: npm install

- Copiar el archivo de configuración: cp .env.example .env

- Generar la clave de la aplicación: php artisan key:generate

- Configurar la base de datos en .env (DB_DATABASE=fastlogi, DB_USERNAME=root, DB_PASSWORD=) y la cola (QUEUE_CONNECTION=database)

- Ejecutar migraciones y seeders: php artisan migrate:fresh --seed

- Compilar assets: npm run build

- Iniciar el servidor: php artisan serve

- En terminales aparte, iniciar el worker de colas (php artisan queue:work) y el scheduler (php artisan schedule:work); son necesarios para la expiración de ofertas, los correos de alertas y la detección de inactividad

La aplicación queda disponible en http://127.0.0.1:8000

## Credenciales de Prueba

- SuperAdmin: super@fastlogi.test / superadmin

- Dueño de negocio: owner@fastlogi.test / password123

- Repartidor: 3101112233 / driverpass

Estas credenciales son exclusivas del entorno de desarrollo generado por los seeders y deben eliminarse o cambiarse en cualquier despliegue real.

# 10. Manual de Usuario

## 10.1 SuperAdmin

Tras iniciar sesión, el SuperAdmin accede a /superadmin/dashboard, donde puede ver métricas globales, crear nuevos negocios desde el botón "Nuevo Negocio", consultar el listado de negocios y suspender o reactivar los existentes. Al suspender un negocio con operaciones en curso, el panel muestra cuántos pedidos assigned/in_transit quedan en ventana de gracia (máximo 24 horas) y ofrece la acción "Cerrar pedidos en gracia" para finalizarlos antes de ese plazo.

## 10.2 Dueño de Negocio

Tras iniciar sesión, el dueño accede a /dashboard, donde ve sus pedidos activos con KPIs de ventas, entregas y tiempo promedio. Desde "Nuevo Pedido" registra ventas seleccionando productos con stock visible. Puede ofrecer pedidos a repartidores disponibles (sujeto a su aceptación en 90 segundos), ver el detalle/timeline de cada pedido, cancelar pedidos en pending, offered o assigned, registrar incidencias o devoluciones sobre pedidos en camino, reasignar pedidos cuando un repartidor queda inactivo, configurar su negocio, gestionar repartidores (incluyendo su límite de capacidad) e inventario, y generar reportes en PDF. Las alertas de stock bajo aparecen en el centro de notificaciones del panel y por correo electrónico, una sola vez cada vez que un producto cruza su mínimo.

## 10.3 Repartidor

Tras iniciar sesión con su teléfono y contraseña, el repartidor accede a /driver/dashboard ("Rutas de Hoy"), donde ve sus pedidos activos y las ofertas pendientes de aceptar o rechazar, con una cuenta regresiva de 90 segundos; el panel se actualiza solo cada 10 segundos. Para pedidos con trazabilidad, escanea la recogida y luego la entrega; para pedidos simples, marca directamente como entregado. El sistema actualiza automáticamente su disponibilidad y envía su ubicación GPS cada 60 segundos mientras tenga una entrega en curso. Para que el envío no se interrumpa, conviene mantener la pantalla encendida y la pestaña abierta; si el navegador deja de enviar la ubicación, el panel muestra un aviso.

FastLogi — SRS v1.2   |   Página