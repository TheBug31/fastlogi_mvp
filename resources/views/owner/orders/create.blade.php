@extends('layouts.owner')

@section('title', 'Nuevo pedido')

@section('content')
<div class="flex flex-col w-full">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-space-lg">
<!-- Columna Izquierda: Formulario y Detalles -->
<div class="lg:col-span-8 space-y-space-lg">
<!-- Tarjeta de Datos del Cliente -->
<div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm border border-outline-variant/50">
<div class="flex items-center gap-space-sm mb-space-md pb-space-sm border-b border-outline-variant/30">
<span class="material-symbols-outlined text-primary text-[22px]">person_pin</span>
<h2 class="font-headline-lg text-headline-sm text-on-surface">Datos del Cliente y Destino</h2>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
<div class="space-y-space-xs">
<label class="font-label-md text-on-surface-variant uppercase tracking-wider">Nombre del Cliente</label>
<div class="relative">
<input class="w-full bg-surface border border-outline-variant rounded-lg px-space-md py-space-sm text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" type="text" value="María Camila Rodríguez"/>
<span class="absolute right-3 top-2.5 material-symbols-outlined text-on-surface-variant text-[18px]">badge</span>
</div>
</div>
<div class="space-y-space-xs">
<label class="font-label-md text-on-surface-variant uppercase tracking-wider">Teléfono de Contacto</label>
<div class="relative">
<input class="w-full bg-surface border border-outline-variant rounded-lg px-space-md py-space-sm text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" type="text" value="+57 310 492 8173"/>
<span class="absolute right-3 top-2.5 material-symbols-outlined text-on-surface-variant text-[18px]">phone_iphone</span>
</div>
</div>
<div class="md:col-span-2 space-y-space-xs">
<label class="font-label-md text-on-surface-variant uppercase tracking-wider">Dirección de Entrega (Bogotá D.C.)</label>
<div class="relative">
<input class="w-full bg-surface border border-outline-variant rounded-lg px-space-md py-space-sm text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" type="text" value="Calle 93 # 15-45, Apto 302, Chicó Norte"/>
<span class="absolute right-3 top-2.5 material-symbols-outlined text-primary text-[18px]">location_on</span>
</div>
<p class="font-body-sm text-on-surface-variant mt-1">Zona de cobertura activa: Sede Bogotá 93 (Tarifa plana local)</p>
</div>
</div>
</div>
<!-- Buscador y Selector de Productos -->
<div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm border border-outline-variant/50">
<div class="flex items-center justify-between mb-space-md pb-space-sm border-b border-outline-variant/30">
<div class="flex items-center gap-space-sm">
<span class="material-symbols-outlined text-primary text-[22px]">inventory_2</span>
<h2 class="font-headline-lg text-headline-sm text-on-surface">Agregar Productos al Pedido</h2>
</div>
<span class="font-label-sm px-space-sm py-space-xs bg-surface-container-low text-primary rounded-md uppercase">Inventario en tiempo real</span>
</div>
<div class="relative mb-space-md">
<span class="absolute left-3 top-3 material-symbols-outlined text-on-surface-variant text-[20px]">search</span>
<input class="w-full bg-surface border border-outline-variant rounded-lg pl-10 pr-space-md py-2.5 text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" placeholder="Buscar por nombre, código SKU o categoría (ej. Camiseta, Arroz)..." type="text"/>
</div>
<!-- Sugerencias de stock rápido -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-space-sm">
<button class="flex flex-col p-space-sm rounded-lg border border-outline-variant/50 bg-surface hover:bg-surface-container-low text-left transition-all group" type="button">
<span class="font-body-md font-medium text-on-surface group-hover:text-primary">Arroz Premium 1kg</span>
<span class="font-body-sm text-on-surface-variant">Stock: <strong class="text-primary">45 disp.</strong></span>
</button>
<button class="flex flex-col p-space-sm rounded-lg border border-outline-variant/50 bg-surface hover:bg-surface-container-low text-left transition-all group" type="button">
<span class="font-body-md font-medium text-on-surface group-hover:text-primary">Camiseta Oversized M</span>
<span class="font-body-sm text-error">Stock: <strong>2 disp. (Bajo)</strong></span>
</button>
<button class="flex flex-col p-space-sm rounded-lg border border-outline-variant/50 bg-surface hover:bg-surface-container-low text-left transition-all group" type="button">
<span class="font-body-md font-medium text-on-surface group-hover:text-primary">Aceite de Girasol 3L</span>
<span class="font-body-sm text-on-surface-variant">Stock: <strong class="text-primary">12 disp.</strong></span>
</button>
</div>
</div>
<!-- Tabla de Líneas de Pedido -->
<div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm border border-outline-variant/50">
<div class="flex items-center justify-between mb-space-md pb-space-sm border-b border-outline-variant/30">
<div class="flex items-center gap-space-sm">
<span class="material-symbols-outlined text-primary text-[22px]">list_alt</span>
<h2 class="font-headline-lg text-headline-sm text-on-surface">Líneas Seleccionadas</h2>
</div>
<span class="font-label-sm bg-primary/10 text-primary px-space-sm py-space-xs rounded font-bold">2 ÍTEMS</span>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="border-b border-outline-variant/50 text-on-surface-variant font-label-md uppercase tracking-wider">
<th class="py-space-sm px-space-sm">Producto</th>
<th class="py-space-sm px-space-sm">Cantidad</th>
<th class="py-space-sm px-space-sm">Precio Unitario</th>
<th class="py-space-sm px-space-sm">Subtotal</th>
<th class="py-space-sm px-space-sm text-right">Acción</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/30 font-body-md text-on-surface">
<!-- Item 1 -->
<tr>
<td class="py-space-md px-space-sm">
<div class="font-medium">Arroz Premium 1kg</div>
<div class="font-body-sm text-on-surface-variant">SKU: ARR-001</div>
</td>
<td class="py-space-md px-space-sm">
<div class="inline-flex items-center border border-outline-variant rounded bg-surface">
<button class="px-2 py-1 text-on-surface-variant hover:bg-surface-container" type="button">-</button>
<span class="px-3 font-data-mono font-semibold">4</span>
<button class="px-2 py-1 text-on-surface-variant hover:bg-surface-container" type="button">+</button>
</div>
</td>
<td class="py-space-md px-space-sm font-data-mono">$ 18.500 COP</td>
<td class="py-space-md px-space-sm font-data-mono font-semibold">$ 74.000 COP</td>
<td class="py-space-md px-space-sm text-right">
<button class="p-2 text-on-surface-variant hover:text-error transition-colors" type="button">
<span class="material-symbols-outlined text-[18px]">delete</span>
</button>
</td>
</tr>
<!-- Item 2 con Error Stock -->
<tr class="bg-error-container/20">
<td class="py-space-md px-space-sm">
<div class="font-medium">Camiseta Oversized M</div>
<div class="font-body-sm text-on-surface-variant">SKU: CAM-M-02</div>
<div class="flex items-center gap-1 mt-1 text-error font-body-sm">
<span class="material-symbols-outlined text-[14px]">error</span>
<span>Stock insuficiente para Camiseta M (disponible: 2)</span>
</div>
</td>
<td class="py-space-md px-space-sm">
<div class="inline-flex items-center border border-error rounded bg-surface">
<button class="px-2 py-1 text-on-surface-variant hover:bg-surface-container" type="button">-</button>
<span class="px-3 font-data-mono font-semibold text-error">4</span>
<button class="px-2 py-1 text-on-surface-variant hover:bg-surface-container" type="button">+</button>
</div>
</td>
<td class="py-space-md px-space-sm font-data-mono">$ 15.000 COP</td>
<td class="py-space-md px-space-sm font-data-mono font-semibold">$ 60.000 COP</td>
<td class="py-space-md px-space-sm text-right">
<button class="p-2 text-error hover:opacity-80 transition-opacity" type="button">
<span class="material-symbols-outlined text-[18px]">delete</span>
</button>
</td>
</tr>
</tbody>
</table>
</div>
</div>
<!-- Opciones Avanzadas -->
<div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm border border-outline-variant/50">
<div class="flex items-center justify-between">
<div class="space-y-space-xs">
<span class="font-headline-sm text-headline-sm text-on-surface">¿Requiere trazabilidad estricta?</span>
<p class="font-body-sm text-on-surface-variant">Se creará un paquete con escaneo de recogida y entrega mediante código QR de seguridad.</p>
</div>
<!-- Custom Toggle Switch -->
<label class="relative inline-flex items-center cursor-pointer">
<input checked="" class="sr-only peer" type="checkbox" value=""/>
<div class="w-11 h-6 bg-surface-container-high peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-outline-variant after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
</label>
</div>
</div>
</div>
<!-- Columna Derecha (Sticky Resumen de Facturación) -->
<div class="lg:col-span-4">
<div class="sticky top-24 bg-surface-container-lowest p-space-lg rounded-xl shadow-md border border-outline-variant/60 space-y-space-lg">
<div class="flex items-center gap-space-sm pb-space-sm border-b border-outline-variant/30">
<span class="material-symbols-outlined text-primary text-[22px]">receipt_long</span>
<h2 class="font-headline-lg text-headline-sm text-on-surface">Resumen de Facturación</h2>
</div>
<div class="space-y-space-sm">
<div class="flex justify-between text-body-md text-on-surface-variant">
<span>Subtotal productos</span>
<span class="font-data-mono text-on-surface">$ 134.000 COP</span>
</div>
<div class="flex justify-between text-body-md text-on-surface-variant">
<span>Costo de envío (Local Bogotá)</span>
<span class="font-data-mono text-on-surface">$ 8.500 COP</span>
</div>
<div class="flex justify-between text-body-md text-on-surface-variant">
<span>Descuentos aplicados</span>
<span class="font-data-mono text-primary">$ 0 COP</span>
</div>
<div class="pt-space-sm border-t border-outline-variant/30 flex justify-between items-baseline">
<span class="font-headline-sm text-headline-sm text-on-surface">Total</span>
<span class="font-headline-xl text-primary font-bold font-data-mono">$ 142.500 COP</span>
</div>
</div>
<!-- Selector de Método de Pago -->
<div class="space-y-space-xs">
<label class="font-label-md text-on-surface-variant uppercase tracking-wider">Método de Pago</label>
<div class="grid grid-cols-3 gap-space-xs">
<button class="py-2.5 px-2 text-center text-body-sm font-medium border border-primary bg-primary/10 text-primary rounded-lg transition-all" type="button">
              Efectivo
            </button>
<button class="py-2.5 px-2 text-center text-body-sm font-medium border border-outline-variant bg-surface hover:bg-surface-container-low text-on-surface transition-all" type="button">
              Nequi / Davi
            </button>
<button class="py-2.5 px-2 text-center text-body-sm font-medium border border-outline-variant bg-surface hover:bg-surface-container-low text-on-surface transition-all" type="button">
              Tarjeta
            </button>
</div>
</div>
<!-- Notas adicionales -->
<div class="space-y-space-xs">
<label class="font-label-md text-on-surface-variant uppercase tracking-wider">Notas para el Repartidor</label>
<textarea class="w-full bg-surface border border-outline-variant rounded-lg p-space-sm text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" placeholder="Instrucciones especiales de entrega..." rows="2"></textarea>
</div>
<!-- Botón de Acción Principal -->
<button class="w-full py-3.5 px-space-md bg-[#3B3FD9] hover:bg-[#3235b8] text-white font-headline-sm text-headline-sm rounded-lg shadow-md transition-all flex items-center justify-center gap-space-sm relative overflow-hidden group" type="button">
<span class="absolute right-0 top-0 w-2 h-full bg-[#14B8A6]"></span>
<span class="material-symbols-outlined text-[20px]">local_shipping</span>
<span>Crear Pedido</span>
</button>
<div class="flex items-center justify-center gap-1 text-center font-body-sm text-on-surface-variant">
<span class="material-symbols-outlined text-[16px] text-primary">verified_user</span>
<span>Transacción encriptada y respaldada por FastLogi</span>
</div>
</div>
</div>
</div>
</div>
@endsection
