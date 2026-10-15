@extends('layouts.owner')

@section('title', 'Dashboard')

@section('content')
<div class="flex flex-col w-full relative">
<!-- ==========================================
       TOP BAR DROPDOWN OVERLAY (CRITICAL SPEC)
       Positioned directly under the shell's top-right notification bell
       ========================================== -->
<div class="fixed top-14 right-20 w-96 z-50 transition-all duration-200" id="notification-popover">
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg shadow-xl overflow-hidden flex flex-col">
<!-- Header -->
<div class="px-space-md py-space-sm bg-surface-container-low border-b border-outline-variant flex items-center justify-between">
<div class="flex items-center gap-space-xs">
<span class="w-2 h-2 rounded-full bg-error animate-ping"></span>
<span class="font-headline-sm text-label-lg font-semibold text-on-surface">Notificaciones (3 no leídas)</span>
</div>
<button class="font-label-sm text-label-sm text-primary hover:text-on-primary-fixed-variant transition-colors underline decoration-primary/40 underline-offset-2" type="button">
          Marcar todas como leídas
        </button>
</div>
<!-- Items List -->
<div class="divide-y divide-outline-variant/60 max-h-[380px] overflow-y-auto">
<!-- Item 1: Stock Warning -->
<div class="p-space-sm hover:bg-surface-container-low transition-colors flex gap-space-sm items-start cursor-pointer group">
<div class="w-8 h-8 rounded-lg bg-tertiary-fixed text-on-tertiary-fixed flex items-center justify-center shrink-0 mt-0.5">
<span class="material-symbols-outlined text-[18px]">warning</span>
</div>
<div class="flex-1 min-w-0">
<div class="flex items-center justify-between gap-1">
<span class="font-headline-sm text-body-md font-semibold text-on-surface group-hover:text-primary transition-colors truncate">Stock bajo: Arroz 1kg</span>
<span class="font-label-sm text-label-sm text-on-surface-variant shrink-0">Hace 4 min</span>
</div>
<p class="font-body-sm text-body-sm text-on-surface-variant line-clamp-1 mt-0.5">Inventario crítico: quedan 3 unidades en bodega</p>
</div>
</div>
<!-- Item 2: Rejection Error -->
<div class="p-space-sm hover:bg-surface-container-low transition-colors flex gap-space-sm items-start cursor-pointer group">
<div class="w-8 h-8 rounded-lg bg-error-container text-on-error-container flex items-center justify-center shrink-0 mt-0.5">
<span class="material-symbols-outlined text-[18px]">cancel</span>
</div>
<div class="flex-1 min-w-0">
<div class="flex items-center justify-between gap-1">
<span class="font-headline-sm text-body-md font-semibold text-on-surface group-hover:text-primary transition-colors truncate">Oferta rechazada por Carlos</span>
<span class="font-label-sm text-label-sm text-on-surface-variant shrink-0">Hace 12 min</span>
</div>
<p class="font-body-sm text-body-sm text-on-surface-variant line-clamp-1 mt-0.5">Pedido #FL-1082 fue rechazado por el repartidor</p>
</div>
</div>
<!-- Item 3: Neutral telemetry drop -->
<div class="p-space-sm hover:bg-surface-container-low transition-colors flex gap-space-sm items-start cursor-pointer group">
<div class="w-8 h-8 rounded-lg bg-surface-container-high text-secondary flex items-center justify-center shrink-0 mt-0.5">
<span class="material-symbols-outlined text-[18px]">gps_off</span>
</div>
<div class="flex-1 min-w-0">
<div class="flex items-center justify-between gap-1">
<span class="font-headline-sm text-body-md font-semibold text-on-surface group-hover:text-primary transition-colors truncate">Repartidor sin conexión</span>
<span class="font-label-sm text-label-sm text-on-surface-variant shrink-0">Hace 25 min</span>
</div>
<p class="font-body-sm text-body-sm text-on-surface-variant line-clamp-1 mt-0.5">Julián Sierra perdió señal GPS en Calle 72</p>
</div>
</div>
</div>
<!-- Footer Link -->
<div class="p-space-xs bg-surface-container border-t border-outline-variant text-center">
<a class="font-label-md text-label-md text-primary font-semibold hover:text-on-primary-fixed-variant flex items-center justify-center gap-1 py-1" href="#">
<span>Ver todas las notificaciones</span>
<span class="material-symbols-outlined text-[16px]">arrow_forward</span>
</a>
</div>
</div>
</div>
<!-- ==========================================
       TOP ROW: 4 KPI CARDS
       Clean modern telemetry tiles with sharp hierarchy
       ========================================== -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-space-md mb-space-lg">
<!-- KPI 1 -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-space-md shadow-sm relative overflow-hidden flex flex-col justify-between">
<div class="flex items-center justify-between">
<span class="font-label-md text-label-md uppercase tracking-wider text-on-surface-variant font-semibold">Ventas de hoy</span>
<div class="w-9 h-9 rounded-lg bg-surface-container flex items-center justify-center text-primary">
<span class="material-symbols-outlined text-[20px]">point_of_sale</span>
</div>
</div>
<div class="mt-space-sm">
<div class="font-headline-xl text-headline-xl text-on-surface tracking-tight font-bold">$ 1.845.000</div>
<div class="flex items-center gap-1 mt-1">
<span class="material-symbols-outlined text-primary text-[16px]">trending_up</span>
<span class="font-label-sm text-label-sm text-primary font-semibold">+12.4%</span>
<span class="font-body-sm text-body-sm text-on-surface-variant">vs ayer ($ 1.641.000)</span>
</div>
</div>
</div>
<!-- KPI 2 -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-space-md shadow-sm relative overflow-hidden flex flex-col justify-between">
<div class="flex items-center justify-between">
<span class="font-label-md text-label-md uppercase tracking-wider text-on-surface-variant font-semibold">Pedidos entregados</span>
<div class="w-9 h-9 rounded-lg bg-primary-fixed/30 flex items-center justify-center text-primary">
<span class="material-symbols-outlined text-[20px]">task_alt</span>
</div>
</div>
<div class="mt-space-sm">
<div class="flex items-baseline gap-2">
<span class="font-headline-xl text-headline-xl text-on-surface tracking-tight font-bold">38</span>
<span class="font-body-md text-body-md text-on-surface-variant">pedidos</span>
</div>
<div class="w-full bg-surface-container h-1.5 rounded-full overflow-hidden mt-2">
<div class="bg-primary h-full rounded-full" style="width: 84.4%;"></div>
</div>
<div class="flex justify-between items-center mt-1 font-body-sm text-body-sm text-on-surface-variant">
<span>Meta del día: 45</span>
<span class="font-label-sm text-label-sm font-semibold text-primary">84.4% cumplimiento</span>
</div>
</div>
</div>
<!-- KPI 3 -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-space-md shadow-sm relative overflow-hidden flex flex-col justify-between">
<div class="flex items-center justify-between">
<span class="font-label-md text-label-md uppercase tracking-wider text-on-surface-variant font-semibold">Tiempo promedio</span>
<div class="w-9 h-9 rounded-lg bg-secondary-container/40 flex items-center justify-center text-secondary">
<span class="material-symbols-outlined text-[20px]">timer</span>
</div>
</div>
<div class="mt-space-sm">
<div class="flex items-baseline gap-2">
<span class="font-headline-xl text-headline-xl text-on-surface tracking-tight font-bold">32</span>
<span class="font-body-md text-body-md text-on-surface-variant">min / despacho</span>
</div>
<div class="flex items-center gap-1 mt-1">
<span class="material-symbols-outlined text-primary text-[16px]">arrow_downward</span>
<span class="font-label-sm text-label-sm text-primary font-semibold">-5 min</span>
<span class="font-body-sm text-body-sm text-on-surface-variant">vs semana ant. (Meta: &lt;40 min)</span>
</div>
</div>
</div>
<!-- KPI 4 -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-space-md shadow-sm relative overflow-hidden flex flex-col justify-between">
<div class="flex items-center justify-between">
<span class="font-label-md text-label-md uppercase tracking-wider text-on-surface-variant font-semibold">Pedidos activos</span>
<div class="w-9 h-9 rounded-lg bg-surface-container-high flex items-center justify-center text-primary-container">
<span class="material-symbols-outlined text-[20px]">route</span>
</div>
</div>
<div class="mt-space-sm">
<div class="flex items-baseline gap-2">
<span class="font-headline-xl text-headline-xl text-primary font-bold">9</span>
<span class="font-body-md text-body-md text-on-surface-variant">en curso</span>
</div>
<div class="grid grid-cols-4 gap-1 text-center font-label-sm text-label-sm mt-2 text-on-surface-variant">
<div class="bg-surface-container px-1 py-0.5 rounded">2 pend</div>
<div class="bg-surface-container px-1 py-0.5 rounded">3 ofic</div>
<div class="bg-surface-container px-1 py-0.5 rounded">3 asig</div>
<div class="bg-surface-container px-1 py-0.5 rounded">1 ruta</div>
</div>
</div>
</div>
</div>
<!-- ==========================================
       MAIN TWO-COLUMN WORKSPACE
       Left/Center: Orders management (72%)
       Right: Fleet dispatch panel (28%)
       ========================================== -->
<div class="grid grid-cols-1 xl:grid-cols-12 gap-space-md items-start">
<!-- LEFT / CENTER COLUMN (72% on XL -> col-span-8 or 9) -->
<div class="xl:col-span-8 lg:col-span-7 flex flex-col gap-space-md">
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg shadow-sm overflow-hidden">
<!-- Section Header -->
<div class="p-space-md border-b border-outline-variant bg-surface-container-low/40 flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm">
<div>
<h2 class="font-headline-lg text-headline-lg font-bold text-on-surface tracking-tight">Pedidos activos</h2>
<p class="font-body-sm text-body-sm text-on-surface-variant">Supervisión en vivo de despachos y asignación de domiciliarios en Bogotá</p>
</div>
<div class="flex items-center gap-space-sm">
<button class="px-space-sm py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest font-body-sm text-body-sm text-on-surface hover:bg-surface-container flex items-center gap-1 transition-colors" type="button">
<span class="material-symbols-outlined text-[18px]">refresh</span>
<span>Sincronizar</span>
</button>
<button class="px-space-sm py-1.5 rounded-lg bg-primary text-on-primary font-body-sm text-body-sm font-semibold hover:bg-on-primary-fixed-variant transition-colors flex items-center gap-1" type="button">
<span class="material-symbols-outlined text-[18px]">add</span>
<span>Crear pedido</span>
</button>
</div>
</div>
<!-- Filter Status Tabs -->
<div class="px-space-md pt-space-sm border-b border-outline-variant bg-surface-container-lowest overflow-x-auto">
<div class="flex items-center gap-space-sm min-w-max">
<button class="px-space-md py-2 border-b-2 border-primary text-primary font-headline-sm text-body-md font-semibold flex items-center gap-1.5" type="button">
<span>Todos</span>
<span class="bg-primary-container text-on-primary-container font-label-sm text-label-sm px-1.5 py-0.5 rounded-full font-bold">9</span>
</button>
<button class="px-space-md py-2 border-b-2 border-transparent text-on-surface-variant hover:text-on-surface font-body-md text-body-md flex items-center gap-1.5 transition-colors" type="button">
<span>Pendientes</span>
<span class="bg-surface-container text-on-surface-variant font-label-sm text-label-sm px-1.5 py-0.5 rounded-full">2</span>
</button>
<button class="px-space-md py-2 border-b-2 border-transparent text-on-surface-variant hover:text-on-surface font-body-md text-body-md flex items-center gap-1.5 transition-colors" type="button">
<span>Ofrecidos</span>
<span class="bg-surface-container text-on-surface-variant font-label-sm text-label-sm px-1.5 py-0.5 rounded-full">3</span>
</button>
<button class="px-space-md py-2 border-b-2 border-transparent text-on-surface-variant hover:text-on-surface font-body-md text-body-md flex items-center gap-1.5 transition-colors" type="button">
<span>Asignados</span>
<span class="bg-surface-container text-on-surface-variant font-label-sm text-label-sm px-1.5 py-0.5 rounded-full">3</span>
</button>
<button class="px-space-md py-2 border-b-2 border-transparent text-on-surface-variant hover:text-on-surface font-body-md text-body-md flex items-center gap-1.5 transition-colors" type="button">
<span>En camino</span>
<span class="bg-surface-container text-on-surface-variant font-label-sm text-label-sm px-1.5 py-0.5 rounded-full">1</span>
</button>
</div>
</div>
<!-- Search Bar and Quick Filters -->
<div class="p-space-sm border-b border-outline-variant bg-surface-container-low/20 flex flex-col sm:flex-row gap-space-sm">
<div class="relative flex-1">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
<input class="w-full pl-9 pr-3 py-1.5 text-body-md font-body-md rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-on-surface focus:outline-none transition-colors" placeholder="Buscar por #pedido, cliente o dirección..." type="text"/>
</div>
<div class="flex items-center gap-space-xs">
<select class="px-space-sm py-1.5 text-body-sm font-body-sm rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none">
<option>Zona: Todas (Norte / Chico)</option>
<option>Zona: Chapinero</option>
<option>Zona: Usaquén</option>
</select>
<select class="px-space-sm py-1.5 text-body-sm font-body-sm rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none">
<option>Pago: Todos</option>
<option>Datafono</option>
<option>Efectivo</option>
<option>Wompi / Online</option>
</select>
</div>
</div>
<!-- Orders Table -->
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="border-b border-outline-variant bg-surface-container-low text-on-surface-variant font-label-md text-label-md uppercase tracking-wider">
<th class="py-2.5 px-space-md font-semibold">#Pedido</th>
<th class="py-2.5 px-space-sm font-semibold">Cliente</th>
<th class="py-2.5 px-space-sm font-semibold">Dirección</th>
<th class="py-2.5 px-space-sm font-semibold text-right">Total</th>
<th class="py-2.5 px-space-sm font-semibold">Repartidor</th>
<th class="py-2.5 px-space-sm font-semibold">Estado</th>
<th class="py-2.5 px-space-md font-semibold text-right">Acción</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/60 font-body-md text-body-md text-on-surface">
<!-- Row 1: Pending -->
<tr class="hover:bg-surface-container-low/40 transition-colors">
<td class="py-space-sm px-space-md font-data-mono text-data-mono font-semibold text-primary">
                  #FL-1085
                </td>
<td class="py-space-sm px-space-sm font-medium">Mariana Gómez</td>
<td class="py-space-sm px-space-sm text-on-surface-variant text-body-sm">
<div class="truncate max-w-[170px]" title="Calle 94 # 11-30, Chico">Calle 94 # 11-30, Chicó</div>
</td>
<td class="py-space-sm px-space-sm font-data-mono text-data-mono text-right font-medium">$ 38.000</td>
<td class="py-space-sm px-space-sm text-body-sm text-on-surface-variant italic">
                  Sin asignar
                </td>
<td class="py-space-sm px-space-sm">
<span class="inline-flex items-center px-2 py-0.5 rounded text-label-sm font-label-sm font-semibold bg-surface-container text-on-surface-variant uppercase">
                    Pendiente
                  </span>
</td>
<td class="py-space-sm px-space-md text-right">
<button class="px-2.5 py-1 rounded bg-primary text-on-primary font-label-sm text-label-sm font-semibold hover:bg-on-primary-fixed-variant transition-colors whitespace-nowrap shadow-sm" type="button">
                    + Ofrecer a repartidor
                  </button>
</td>
</tr>
<!-- Row 2: Pending -->
<tr class="hover:bg-surface-container-low/40 transition-colors">
<td class="py-space-sm px-space-md font-data-mono text-data-mono font-semibold text-primary">
                  #FL-1084
                </td>
<td class="py-space-sm px-space-sm font-medium">Andrés Felipe Ruiz</td>
<td class="py-space-sm px-space-sm text-on-surface-variant text-body-sm">
<div class="truncate max-w-[170px]" title="Cra 15 # 88-21 Apto 402">Cra 15 # 88-21 Apto 402</div>
</td>
<td class="py-space-sm px-space-sm font-data-mono text-data-mono text-right font-medium">$ 64.500</td>
<td class="py-space-sm px-space-sm text-body-sm text-on-surface-variant italic">
                  Sin asignar
                </td>
<td class="py-space-sm px-space-sm">
<span class="inline-flex items-center px-2 py-0.5 rounded text-label-sm font-label-sm font-semibold bg-surface-container text-on-surface-variant uppercase">
                    Pendiente
                  </span>
</td>
<td class="py-space-sm px-space-md text-right">
<button class="px-2.5 py-1 rounded bg-primary text-on-primary font-label-sm text-label-sm font-semibold hover:bg-on-primary-fixed-variant transition-colors whitespace-nowrap shadow-sm" type="button">
                    + Ofrecer a repartidor
                  </button>
</td>
</tr>
<!-- Row 3: Offered -->
<tr class="bg-surface-container-low/20 hover:bg-surface-container-low/50 transition-colors">
<td class="py-space-sm px-space-md font-data-mono text-data-mono font-semibold text-primary">
                  #FL-1083
                </td>
<td class="py-space-sm px-space-sm font-medium">Juan Camilo Torres</td>
<td class="py-space-sm px-space-sm text-on-surface-variant text-body-sm">
<div class="truncate max-w-[170px]" title="Calle 85 # 14-08">Calle 85 # 14-08</div>
</td>
<td class="py-space-sm px-space-sm font-data-mono text-data-mono text-right font-medium">$ 52.000</td>
<td class="py-space-sm px-space-sm text-body-sm">
<span class="font-medium">Mateo Ríos</span>
<span class="text-label-sm text-label-sm text-secondary block">(Ofrecido)</span>
</td>
<td class="py-space-sm px-space-sm">
<span class="inline-flex items-center px-2 py-0.5 rounded text-label-sm font-label-sm font-semibold bg-secondary-fixed text-on-secondary-fixed uppercase">
                    Ofrecido
                  </span>
</td>
<td class="py-space-sm px-space-md text-right">
<div class="flex items-center justify-end gap-2">
<span class="inline-flex items-center gap-1 font-label-sm text-label-sm bg-surface-container-lowest border border-outline-variant px-1.5 py-0.5 rounded text-error animate-pulse">
                      ⏳ responde en 0:54
                    </span>
<button class="font-label-sm text-label-sm text-error hover:underline" type="button">
                      Retirar oferta
                    </button>
</div>
</td>
</tr>
<!-- Row 4: Offered -->
<tr class="bg-surface-container-low/20 hover:bg-surface-container-low/50 transition-colors">
<td class="py-space-sm px-space-md font-data-mono text-data-mono font-semibold text-primary">
                  #FL-1082
                </td>
<td class="py-space-sm px-space-sm font-medium">Valentina Restrepo</td>
<td class="py-space-sm px-space-sm text-on-surface-variant text-body-sm">
<div class="truncate max-w-[170px]" title="Cra 11 # 93-45 Of. 601">Cra 11 # 93-45 Of. 601</div>
</td>
<td class="py-space-sm px-space-sm font-data-mono text-data-mono text-right font-medium">$ 27.500</td>
<td class="py-space-sm px-space-sm text-body-sm">
<span class="font-medium">Diego Morales</span>
<span class="text-label-sm text-label-sm text-secondary block">(Ofrecido)</span>
</td>
<td class="py-space-sm px-space-sm">
<span class="inline-flex items-center px-2 py-0.5 rounded text-label-sm font-label-sm font-semibold bg-secondary-fixed text-on-secondary-fixed uppercase">
                    Ofrecido
                  </span>
</td>
<td class="py-space-sm px-space-md text-right">
<div class="flex items-center justify-end gap-2">
<span class="inline-flex items-center gap-1 font-label-sm text-label-sm bg-surface-container-lowest border border-outline-variant px-1.5 py-0.5 rounded text-error animate-pulse">
                      ⏳ responde en 0:28
                    </span>
<button class="font-label-sm text-label-sm text-error hover:underline" type="button">
                      Retirar oferta
                    </button>
</div>
</td>
</tr>
<!-- Row 5: Assigned -->
<tr class="hover:bg-surface-container-low/40 transition-colors">
<td class="py-space-sm px-space-md font-data-mono text-data-mono font-semibold text-primary">
                  #FL-1081
                </td>
<td class="py-space-sm px-space-sm font-medium">Carlos Mendoza</td>
<td class="py-space-sm px-space-sm text-on-surface-variant text-body-sm">
<div class="truncate max-w-[170px]" title="Calle 90 # 19-40">Calle 90 # 19-40</div>
</td>
<td class="py-space-sm px-space-sm font-data-mono text-data-mono text-right font-medium">$ 89.000</td>
<td class="py-space-sm px-space-sm text-body-sm">
<span class="font-medium text-on-surface">Carlos Pérez</span>
</td>
<td class="py-space-sm px-space-sm">
<span class="inline-flex items-center px-2 py-0.5 rounded text-label-sm font-label-sm font-semibold bg-surface-container-highest text-primary uppercase">
                    Asignado
                  </span>
</td>
<td class="py-space-sm px-space-md text-right">
<div class="inline-flex items-center gap-space-sm">
<button class="font-label-sm text-label-sm text-primary font-semibold hover:underline" type="button">Ver detalle</button>
<span class="text-outline-variant">|</span>
<button class="font-label-sm text-label-sm text-on-surface-variant hover:text-on-surface" type="button">Reasignar</button>
</div>
</td>
</tr>
<!-- Row 6: In Transit -->
<tr class="hover:bg-surface-container-low/40 transition-colors">
<td class="py-space-sm px-space-md font-data-mono text-data-mono font-semibold text-primary">
                  #FL-1080
                </td>
<td class="py-space-sm px-space-sm font-medium">Laura Jimena Vega</td>
<td class="py-space-sm px-space-sm text-on-surface-variant text-body-sm">
<div class="truncate max-w-[170px]" title="Cra 7 # 116-50">Cra 7 # 116-50</div>
</td>
<td class="py-space-sm px-space-sm font-data-mono text-data-mono text-right font-medium">$ 42.000</td>
<td class="py-space-sm px-space-sm text-body-sm">
<span class="font-medium text-on-surface">Carlos Pérez</span>
</td>
<td class="py-space-sm px-space-sm">
<span class="inline-flex items-center px-2 py-0.5 rounded text-label-sm font-label-sm font-semibold bg-tertiary-fixed text-tertiary uppercase">
                    En camino
                  </span>
</td>
<td class="py-space-sm px-space-md text-right">
<button class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-surface-container text-primary hover:bg-surface-container-high transition-colors font-label-sm text-label-sm font-semibold" type="button">
<span class="material-symbols-outlined text-[16px]">fmd_good</span>
<span>Rastrear</span>
</button>
</td>
</tr>
</tbody>
</table>
</div>
<!-- Table Footer / Pagination -->
<div class="p-space-sm border-t border-outline-variant bg-surface-container-lowest flex items-center justify-between text-body-sm text-on-surface-variant">
<span>Mostrando <strong class="text-on-surface">6 de 9</strong> pedidos activos</span>
<div class="flex items-center gap-1">
<button class="w-7 h-7 rounded border border-outline-variant flex items-center justify-center hover:bg-surface-container disabled:opacity-40" disabled="" type="button">
<span class="material-symbols-outlined text-[16px]">chevron_left</span>
</button>
<span class="px-2 font-label-sm text-label-sm font-bold text-on-surface">1</span>
<button class="w-7 h-7 rounded border border-outline-variant flex items-center justify-center hover:bg-surface-container" type="button">
<span class="material-symbols-outlined text-[16px]">chevron_right</span>
</button>
</div>
</div>
</div>
<!-- Quick Delivery Map Telemetry Preview -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-space-md shadow-sm">
<div class="flex items-center justify-between mb-space-sm">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-primary text-[20px]">map</span>
<h3 class="font-headline-sm text-headline-sm font-semibold text-on-surface">Corredor de Entregas Chico - Chapinero</h3>
</div>
<span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-medium">GPS Telemetría Activa</span>
</div>
<div class="w-full h-44 rounded-lg bg-cover bg-center border border-outline-variant relative overflow-hidden flex items-end p-space-sm" data-location="Bogota, Colombia" style="">
<div class="bg-surface-container-lowest/95 backdrop-blur px-space-sm py-1 rounded text-body-sm font-body-sm text-on-surface border border-outline-variant flex items-center gap-2">
<span class="w-2 h-2 rounded-full bg-primary-container"></span>
<span>Carlos Pérez en tránsito por Av. Carrera 7 hacia Calle 116</span>
</div>
</div>
</div>
</div>
<!-- RIGHT SIDE PANEL (28% width -> col-span-4 or 5) -->
<div class="xl:col-span-4 lg:col-span-5 flex flex-col gap-space-md">
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg shadow-sm overflow-hidden flex flex-col">
<!-- Panel Header -->
<div class="p-space-md border-b border-outline-variant bg-surface-container-low/40 flex items-center justify-between">
<div>
<div class="flex items-center gap-2">
<h3 class="font-headline-sm text-headline-sm font-bold text-on-surface">Repartidores (5)</h3>
<span class="inline-flex items-center gap-1 font-label-sm text-label-sm font-semibold px-2 py-0.5 rounded-full bg-primary-fixed/40 text-primary">
<span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                3 disponibles
              </span>
</div>
<p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">Control de flota y carga operativa</p>
</div>
<button class="p-1 rounded hover:bg-surface-container text-primary font-label-sm text-label-sm font-semibold flex items-center gap-0.5" type="button">
<span class="material-symbols-outlined text-[18px]">person_add</span>
</button>
</div>
<!-- Drivers List -->
<div class="divide-y divide-outline-variant/60">
<!-- Driver 1 -->
<div class="p-space-sm hover:bg-surface-container-low/30 transition-colors">
<div class="flex items-start justify-between">
<div class="flex items-center gap-space-sm">
<div class="w-8 h-8 rounded-full bg-surface-container-high text-on-surface flex items-center justify-center font-bold text-body-sm">
                  CP
                </div>
<div>
<h4 class="font-headline-sm text-body-md font-semibold text-on-surface leading-tight">Carlos Pérez</h4>
<span class="font-body-sm text-body-sm text-on-surface-variant flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">two_wheeler</span>
                    Moto Pulsar 180 (Placa HGT-82F)
                  </span>
</div>
</div>
<span class="inline-flex items-center px-1.5 py-0.5 rounded text-label-sm font-label-sm font-semibold bg-primary-fixed/30 text-primary uppercase">
                Disponible
              </span>
</div>
<div class="mt-2 flex items-center justify-between text-body-sm font-body-sm">
<span class="text-on-surface-variant">Carga de pedidos:</span>
<span class="font-data-mono text-data-mono font-medium text-on-surface">1/3 pedidos</span>
</div>
<div class="w-full bg-surface-container h-1 rounded-full overflow-hidden mt-1">
<div class="bg-primary h-full rounded-full" style="width: 33.3%;"></div>
</div>
</div>
<!-- Driver 2 -->
<div class="p-space-sm hover:bg-surface-container-low/30 transition-colors">
<div class="flex items-start justify-between">
<div class="flex items-center gap-space-sm">
<div class="w-8 h-8 rounded-full bg-surface-container-high text-on-surface flex items-center justify-center font-bold text-body-sm">
                  MR
                </div>
<div>
<h4 class="font-headline-sm text-body-md font-semibold text-on-surface leading-tight">Mateo Ríos</h4>
<span class="font-body-sm text-body-sm text-on-surface-variant flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">two_wheeler</span>
                    Moto AKT 125 (Placa MKB-14D)
                  </span>
</div>
</div>
<span class="inline-flex items-center px-1.5 py-0.5 rounded text-label-sm font-label-sm font-semibold bg-tertiary-fixed text-tertiary uppercase">
                Ocupado
              </span>
</div>
<div class="mt-2 flex items-center justify-between text-body-sm font-body-sm">
<span class="text-on-surface-variant">Carga de pedidos:</span>
<span class="font-data-mono text-data-mono font-medium text-on-surface">2/3 pedidos</span>
</div>
<div class="w-full bg-surface-container h-1 rounded-full overflow-hidden mt-1">
<div class="bg-tertiary h-full rounded-full" style="width: 66.6%;"></div>
</div>
<div class="mt-1.5 font-label-sm text-label-sm text-tertiary flex items-center gap-1">
<span class="w-1.5 h-1.5 rounded-full bg-tertiary animate-pulse"></span>
<span>Esperando confirmación oferta #FL-1083</span>
</div>
</div>
<!-- Driver 3 -->
<div class="p-space-sm hover:bg-surface-container-low/30 transition-colors">
<div class="flex items-start justify-between">
<div class="flex items-center gap-space-sm">
<div class="w-8 h-8 rounded-full bg-surface-container-high text-on-surface flex items-center justify-center font-bold text-body-sm">
                  DM
                </div>
<div>
<h4 class="font-headline-sm text-body-md font-semibold text-on-surface leading-tight">Diego Morales</h4>
<span class="font-body-sm text-body-sm text-on-surface-variant flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">pedal_bike</span>
                    Bici de carga eléctrica
                  </span>
</div>
</div>
<span class="inline-flex items-center px-1.5 py-0.5 rounded text-label-sm font-label-sm font-semibold bg-primary-fixed/30 text-primary uppercase">
                Disponible
              </span>
</div>
<div class="mt-2 flex items-center justify-between text-body-sm font-body-sm">
<span class="text-on-surface-variant">Carga de pedidos:</span>
<span class="font-data-mono text-data-mono font-medium text-on-surface">0/3 pedidos</span>
</div>
<div class="w-full bg-surface-container h-1 rounded-full overflow-hidden mt-1">
<div class="bg-primary h-full rounded-full" style="width: 0%;"></div>
</div>
</div>
<!-- Driver 4 -->
<div class="p-space-sm hover:bg-surface-container-low/30 transition-colors">
<div class="flex items-start justify-between">
<div class="flex items-center gap-space-sm">
<div class="w-8 h-8 rounded-full bg-surface-container-high text-on-surface flex items-center justify-center font-bold text-body-sm">
                  SM
                </div>
<div>
<h4 class="font-headline-sm text-body-md font-semibold text-on-surface leading-tight">Sebastián Mora</h4>
<span class="font-body-sm text-body-sm text-on-surface-variant flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">two_wheeler</span>
                    Moto Boxer CT 100
                  </span>
</div>
</div>
<span class="inline-flex items-center px-1.5 py-0.5 rounded text-label-sm font-label-sm font-semibold bg-tertiary-fixed text-tertiary uppercase">
                Ocupado
              </span>
</div>
<div class="mt-2 flex items-center justify-between text-body-sm font-body-sm">
<span class="text-on-surface-variant">Carga de pedidos:</span>
<span class="font-data-mono text-data-mono font-bold text-error">3/3 pedidos (Carga máxima)</span>
</div>
<div class="w-full bg-surface-container h-1 rounded-full overflow-hidden mt-1">
<div class="bg-error h-full rounded-full" style="width: 100%;"></div>
</div>
</div>
<!-- Driver 5 -->
<div class="p-space-sm hover:bg-surface-container-low/30 transition-colors opacity-75">
<div class="flex items-start justify-between">
<div class="flex items-center gap-space-sm">
<div class="w-8 h-8 rounded-full bg-surface-container text-on-surface-variant flex items-center justify-center font-bold text-body-sm">
                  JS
                </div>
<div>
<h4 class="font-headline-sm text-body-md font-semibold text-on-surface leading-tight">Julián Sierra</h4>
<span class="font-body-sm text-body-sm text-on-surface-variant flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">wifi_off</span>
                    Última conexión: Hace 25 min
                  </span>
</div>
</div>
<span class="inline-flex items-center px-1.5 py-0.5 rounded text-label-sm font-label-sm font-semibold bg-surface-container text-on-surface-variant uppercase">
                Desconectado
              </span>
</div>
<div class="mt-2 flex items-center justify-between text-body-sm font-body-sm">
<span class="text-on-surface-variant">Carga:</span>
<span class="font-data-mono text-data-mono font-medium text-on-surface-variant">0/3 pedidos</span>
</div>
</div>
</div>
<!-- Summary box at bottom of panel -->
<div class="p-space-md border-t border-outline-variant bg-surface-container-low/50">
<div class="flex items-center justify-between mb-1.5">
<span class="font-label-md text-label-md uppercase tracking-wider text-on-surface font-semibold">Capacidad de entrega</span>
<span class="font-data-mono text-data-mono font-bold text-primary">6 / 15 pedidos</span>
</div>
<div class="w-full bg-surface-container h-2 rounded-full overflow-hidden">
<div class="bg-primary h-full rounded-full transition-all duration-500" style="width: 40%;"></div>
</div>
<div class="flex items-center justify-between mt-2 text-body-sm font-body-sm text-on-surface-variant">
<span>Ocupación de sede:</span>
<strong class="text-on-surface">40% en operación</strong>
</div>
<button class="w-full mt-space-sm py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest hover:bg-surface-container font-label-sm text-label-sm font-semibold text-on-surface transition-colors flex items-center justify-center gap-1" type="button">
<span class="material-symbols-outlined text-[16px]">add_circle_outline</span>
<span>+ Vincular repartidor de apoyo</span>
</button>
</div>
</div>
</div>
</div>

</div>
@endsection

@push('scripts')
<script>
// Micro-interaction: Dismiss notification popover when clicking anywhere outside
    document.addEventListener('click', function(e) {
      const popover = document.getElementById('notification-popover');
      if (popover && !popover.contains(e.target) && !e.target.closest('[data-notification-toggle]')) {
        // Keeps user interaction alive if they wish to interact with orders
      }
    });
</script>
@endpush
