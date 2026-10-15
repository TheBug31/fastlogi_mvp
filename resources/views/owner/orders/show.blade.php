@extends('layouts.owner')

@section('title', 'Detalle de pedido')

@section('content')
<div class="flex flex-col w-full pb-16">
<!-- Backdrop overlay simulating blurred dashboard -->
<div class="fixed inset-0 bg-[#0B1C30]/40 backdrop-blur-sm z-40 flex justify-end transition-opacity duration-300">
<!-- Drawer Container -->
<div class="w-full max-w-2xl bg-surface-container-lowest h-full shadow-2xl flex flex-col z-50 transform transition-transform duration-300 translate-x-0 overflow-hidden">
<!-- Drawer Header -->
<div class="px-space-lg py-space-md border-b border-outline-variant bg-surface-container-low flex items-center justify-between">
<div class="flex items-center gap-space-md">
<div class="w-10 h-10 bg-primary-container text-on-primary-container flex items-center justify-center font-headline-lg text-lg font-bold">
            FL
          </div>
<div>
<div class="flex items-center gap-space-sm">
<h2 class="font-headline-lg text-headline-lg text-on-surface">Detalle de Pedido #FL-1042</h2>
<span class="px-space-sm py-space-xs bg-amber-100 text-amber-800 border border-amber-300 font-label-sm uppercase rounded-full">EN TRÁNSITO</span>
</div>
<p class="font-body-sm text-on-surface-variant">Creado el 24 de Octubre, 2023 • 08:30 AM</p>
</div>
</div>
<div class="flex items-center gap-space-sm">
<button class="px-space-md py-space-xs bg-surface-container-lowest text-error border border-error hover:bg-error hover:text-on-error font-label-lg transition-colors flex items-center gap-space-xs" type="button">
<span class="material-symbols-outlined text-[18px]">report</span>
            Registrar incidencia
          </button>
<button class="p-space-sm text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-colors rounded-lg" onclick="alert('Cerrar drawer')" type="button">
<span class="material-symbols-outlined text-[24px]">close</span>
</button>
</div>
</div>
<!-- Drawer Scrollable Content -->
<div class="flex-1 overflow-y-auto p-space-lg space-y-space-lg">
<!-- 1. Customer Data Section -->
<div class="bg-surface-container-low p-space-md rounded-lg border border-outline-variant">
<div class="flex items-center justify-between mb-space-sm">
<span class="font-label-md text-on-surface-variant uppercase tracking-wider flex items-center gap-space-xs">
<span class="material-symbols-outlined text-[16px] text-primary">person</span> Datos del Cliente
            </span>
<span class="font-label-sm text-primary uppercase bg-primary-container/20 px-space-sm py-0.5 rounded-full">Cliente Frecuente</span>
</div>
<div class="grid grid-cols-2 gap-space-md">
<div>
<p class="font-body-sm text-on-surface-variant">Nombre Completo</p>
<p class="font-body-md font-semibold text-on-surface">Mariana Gómez</p>
</div>
<div>
<p class="font-body-sm text-on-surface-variant">Teléfono de Contacto</p>
<p class="font-body-md font-semibold text-on-surface font-data-mono">+57 310 492 8173</p>
</div>
<div class="col-span-2">
<p class="font-body-sm text-on-surface-variant">Dirección de Destino</p>
<p class="font-body-md font-semibold text-on-surface flex items-center gap-space-xs">
<span class="material-symbols-outlined text-[16px] text-error">location_on</span>
                Calle 94 # 11-30, Chicó, Bogotá
              </p>
</div>
</div>
</div>
<!-- 2. Products Table -->
<div class="border border-outline-variant rounded-lg overflow-hidden">
<div class="bg-surface-container-low px-space-md py-space-sm border-b border-outline-variant flex items-center justify-between">
<span class="font-label-md text-on-surface-variant uppercase tracking-wider flex items-center gap-space-xs">
<span class="material-symbols-outlined text-[16px] text-primary">shopping_cart</span> Resumen de Productos
            </span>
<span class="font-label-sm text-on-surface">2 Ítems</span>
</div>
<table class="w-full text-left border-collapse">
<thead>
<tr class="border-b border-outline-variant bg-surface-container/50 font-label-sm text-on-surface-variant">
<th class="p-space-sm">PRODUCTO</th>
<th class="p-space-sm text-center">CANT.</th>
<th class="p-space-sm text-right">VALOR UNIT.</th>
<th class="p-space-sm text-right">TOTAL</th>
</tr>
</thead>
<tbody class="font-body-md divide-y divide-outline-variant">
<tr>
<td class="p-space-sm font-medium text-on-surface">Arroz Premium 1kg</td>
<td class="p-space-sm text-center font-data-mono">x2</td>
<td class="p-space-sm text-right font-data-mono">$18.500 COP</td>
<td class="p-space-sm text-right font-data-mono font-semibold">$37.000 COP</td>
</tr>
<tr>
<td class="p-space-sm font-medium text-on-surface">Aceite de Girasol 3L</td>
<td class="p-space-sm text-center font-data-mono">x1</td>
<td class="p-space-sm text-right font-data-mono">$32.000 COP</td>
<td class="p-space-sm text-right font-data-mono font-semibold">$32.000 COP</td>
</tr>
</tbody>
</table>
<div class="p-space-md bg-surface-container-low border-t border-outline-variant space-y-space-xs font-body-sm">
<div class="flex justify-between text-on-surface-variant">
<span>Subtotal Productos</span>
<span class="font-data-mono">$69.000 COP</span>
</div>
<div class="flex justify-between text-on-surface-variant">
<span>Costo de Envío</span>
<span class="font-data-mono">$8.500 COP</span>
</div>
<div class="flex justify-between font-headline-sm text-on-surface pt-space-xs border-t border-outline-variant">
<span>Total a Pagar</span>
<span class="font-data-mono text-primary">$77.500 COP</span>
</div>
</div>
</div>
<!-- 3. Payment Method -->
<div class="bg-surface-container-low p-space-md rounded-lg border border-outline-variant flex items-center justify-between">
<div class="flex items-center gap-space-md">
<div class="w-10 h-10 bg-purple-100 text-purple-800 rounded flex items-center justify-center font-bold">N</div>
<div>
<p class="font-body-sm text-on-surface-variant">Método de Pago</p>
<p class="font-body-md font-semibold text-on-surface">Nequi (Contra Entrega)</p>
</div>
</div>
<span class="px-space-sm py-space-xs bg-emerald-100 text-emerald-800 border border-emerald-300 font-label-sm uppercase rounded-full flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">check_circle</span> Pagado
          </span>
</div>
<!-- 4. Assigned Driver -->
<div class="border border-outline-variant rounded-lg p-space-md bg-surface-container-lowest">
<div class="flex items-center justify-between mb-space-sm">
<span class="font-label-md text-on-surface-variant uppercase tracking-wider flex items-center gap-space-xs">
<span class="material-symbols-outlined text-[16px] text-primary">two_wheeler</span> Repartidor Asignado
            </span>
<span class="px-space-sm py-0.5 bg-emerald-100 text-emerald-800 font-label-sm uppercase rounded-full">DISPONIBLE</span>
</div>
<div class="flex items-center gap-space-md">
<div class="w-12 h-12 bg-surface-container-high rounded-full overflow-hidden border border-outline-variant flex items-center justify-center">
<span class="material-symbols-outlined text-on-surface-variant text-[28px]">person</span>
</div>
<div class="flex-1">
<h4 class="font-headline-sm text-on-surface">Carlos Mendoza</h4>
<p class="font-body-sm text-on-surface-variant">Moto Yamaha YBR • Placa <span class="font-data-mono font-bold text-on-surface">HGT-82F</span></p>
</div>
<div class="text-right">
<span class="font-label-sm text-on-surface-variant block">Carga Actual</span>
<span class="font-data-mono font-bold text-primary">1/3 pedidos activos</span>
</div>
</div>
</div>
<!-- 5. Vertical Timeline "Trazabilidad" -->
<div class="border border-outline-variant rounded-lg p-space-md bg-surface-container-lowest">
<h3 class="font-headline-sm text-on-surface mb-space-md flex items-center gap-space-xs">
<span class="material-symbols-outlined text-primary">timeline</span> Trazabilidad del Pedido
          </h3>
<div class="relative pl-space-lg space-y-space-md before:absolute before:left-[7px] before:top-2 before:bottom-2 before:w-[2px] before:bg-outline-variant">
<div class="relative flex items-start gap-space-md">
<div class="absolute -left-[23px] top-1 w-3 h-3 rounded-full bg-primary ring-4 ring-surface-container-lowest"></div>
<div>
<p class="font-body-md font-semibold text-on-surface">Pedido creado</p>
<p class="font-body-sm text-on-surface-variant font-data-mono">08:30 AM</p>
</div>
</div>
<div class="relative flex items-start gap-space-md">
<div class="absolute -left-[23px] top-1 w-3 h-3 rounded-full bg-primary ring-4 ring-surface-container-lowest"></div>
<div>
<p class="font-body-md font-semibold text-on-surface">Oferta enviada a Carlos Mendoza</p>
<p class="font-body-sm text-on-surface-variant font-data-mono">08:32 AM</p>
</div>
</div>
<div class="relative flex items-start gap-space-md">
<div class="absolute -left-[23px] top-1 w-3 h-3 rounded-full bg-primary ring-4 ring-surface-container-lowest"></div>
<div>
<p class="font-body-md font-semibold text-on-surface">Aceptado por repartidor</p>
<p class="font-body-sm text-on-surface-variant font-data-mono">08:33 AM</p>
</div>
</div>
<div class="relative flex items-start gap-space-md">
<div class="absolute -left-[23px] top-1 w-3 h-3 rounded-full bg-primary ring-4 ring-surface-container-lowest"></div>
<div>
<p class="font-body-md font-semibold text-on-surface">Recogida escaneada</p>
<p class="font-body-sm text-on-surface-variant font-data-mono">08:45 AM • Sede Principal Bogotá 85 - Código QR verificado</p>
</div>
</div>
<div class="relative flex items-start gap-space-md">
<div class="absolute -left-[23px] top-1 w-3 h-3 rounded-full bg-primary-container ring-4 ring-primary animate-pulse"></div>
<div>
<p class="font-body-md font-bold text-primary">En ruta hacia destino</p>
<p class="font-body-sm text-primary font-data-mono">Actual • Telemetría activa</p>
</div>
</div>
</div>
</div>
<!-- 6. Additional States / Variants Section -->
<div class="border border-outline-variant rounded-lg p-space-md bg-surface-container-low space-y-space-md">
<div class="flex items-center justify-between">
<span class="font-label-md text-on-surface-variant uppercase tracking-wider">Variantes y Opciones del Sistema</span>
<span class="font-label-sm text-primary">Modo Debug / Operativo</span>
</div>
<!-- Informative Box for non-traceable variant -->
<div class="p-space-sm bg-surface-container-lowest border border-outline-variant rounded flex items-start gap-space-sm">
<span class="material-symbols-outlined text-primary text-[20px]">info</span>
<div>
<p class="font-body-sm font-semibold text-on-surface">Nota informativa de variante:</p>
<p class="font-body-sm text-on-surface-variant">Este pedido no tiene trazabilidad en tiempo real extendida (Modo Aliado Externo).</p>
</div>
</div>
<!-- Action button for incident / return confirmation -->
<div class="pt-space-xs flex justify-end">
<button class="px-space-md py-space-sm bg-primary text-on-primary font-label-lg hover:bg-primary/90 transition-colors" type="button">
              Confirmar devolución de inventario
            </button>
</div>
</div>
</div>
<!-- Drawer Footer -->
<div class="px-space-lg py-space-md border-t border-outline-variant bg-surface-container-low flex items-center justify-between">
<span class="font-body-sm text-on-surface-variant">ID Operación: <span class="font-data-mono">COL-BOG-93-1042</span></span>
<div class="flex items-center gap-space-sm">
<button class="px-space-md py-space-sm bg-surface-container-lowest border border-outline-variant text-on-surface hover:bg-surface-container font-label-lg transition-colors" type="button">
            Cerrar
          </button>
<button class="px-space-md py-space-sm bg-primary text-on-primary font-label-lg hover:bg-primary/90 transition-colors flex items-center gap-space-xs" type="button">
<span class="material-symbols-outlined text-[18px]">print</span>
            Imprimir Colilla
          </button>
</div>
</div>
</div>
</div>
</div>
@endsection
