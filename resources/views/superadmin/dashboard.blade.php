@extends('layouts.superadmin')

@section('title', 'SuperAdmin')

@section('content')
<div class="flex flex-col w-full gap-space-lg">
<!-- Active Sidebar Link Injector Script -->

<!-- Top Header Section -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-space-md p-space-lg bg-surface-container-lowest rounded-xl shadow-sm">
<div class="flex flex-col gap-space-xs">
<div class="flex items-center gap-space-xs">
<span class="w-2 h-2 rounded-full bg-primary-container animate-pulse"></span>
<span class="font-label-sm text-label-sm tracking-widest text-primary uppercase">Portal Global SuperAdmin · Tenencia Multi-Tenant Colombia</span>
</div>
<h1 class="font-headline-xl text-headline-xl text-on-surface tracking-tight">Gestión de Negocios y Plataforma</h1>
<p class="font-body-md text-body-md text-on-surface-variant max-w-3xl">
        Supervisión de comercios afiliados, control de estados de cuenta, liquidaciones y auditoría de ventanas de gracia operativas.
      </p>
</div>
<div class="flex items-center gap-space-sm flex-shrink-0">
<button class="inline-flex items-center gap-space-xs px-space-lg py-space-sm rounded-lg bg-primary text-on-primary font-label-lg text-label-lg shadow-sm hover:opacity-95 transition-all" onclick="document.getElementById('modal-nuevo-negocio').classList.remove('hidden')" type="button">
<span class="material-symbols-outlined text-[18px]">add_business</span>
<span>+ Nuevo Negocio</span>
</button>
</div>
</div>
<!-- Row of 4 Modern Admin KPI Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-md">
<!-- KPI 1 -->
<div class="flex flex-col justify-between p-space-md bg-surface-container-lowest rounded-xl shadow-sm hover:shadow-md transition-shadow">
<div class="flex items-start justify-between gap-space-sm">
<div class="flex flex-col">
<span class="font-label-md text-label-md text-on-surface-variant uppercase">Negocios Activos</span>
<span class="font-display-lg text-display-lg text-on-surface tracking-tight">142</span>
</div>
<div class="w-10 h-10 rounded-lg bg-primary-fixed flex items-center justify-center text-on-primary-fixed">
<span class="material-symbols-outlined text-[22px]">storefront</span>
</div>
</div>
<div class="flex items-center gap-space-xs mt-space-md">
<span class="inline-flex items-center px-space-xs py-0.5 rounded text-primary font-label-sm text-label-sm bg-primary-fixed">
<span class="material-symbols-outlined text-[14px]">trending_up</span>+8
        </span>
<span class="font-body-sm text-body-sm text-on-surface-variant">este mes (96.6% del total)</span>
</div>
</div>
<!-- KPI 2 -->
<div class="flex flex-col justify-between p-space-md bg-surface-container-lowest rounded-xl shadow-sm hover:shadow-md transition-shadow">
<div class="flex items-start justify-between gap-space-sm">
<div class="flex flex-col">
<span class="font-label-md text-label-md text-on-surface-variant uppercase">Negocios Suspendidos</span>
<span class="font-display-lg text-display-lg text-error tracking-tight">5</span>
</div>
<div class="w-10 h-10 rounded-lg bg-error-container flex items-center justify-center text-on-error-container">
<span class="material-symbols-outlined text-[22px]">domain_disabled</span>
</div>
</div>
<div class="flex items-center gap-space-xs mt-space-md">
<span class="inline-flex items-center px-space-xs py-0.5 rounded text-error font-label-sm text-label-sm bg-error-container">
          Mora / Voluntario
        </span>
<span class="font-body-sm text-body-sm text-on-surface-variant">3 por mora, 2 voluntario</span>
</div>
</div>
<!-- KPI 3 -->
<div class="flex flex-col justify-between p-space-md bg-surface-container-lowest rounded-xl shadow-sm hover:shadow-md transition-shadow">
<div class="flex items-start justify-between gap-space-sm">
<div class="flex flex-col">
<span class="font-label-md text-label-md text-on-surface-variant uppercase">Pedidos Hoy</span>
<span class="font-display-lg text-display-lg text-on-surface tracking-tight">1.849</span>
</div>
<div class="w-10 h-10 rounded-lg bg-secondary-container flex items-center justify-center text-on-secondary-fixed">
<span class="material-symbols-outlined text-[22px]">local_shipping</span>
</div>
</div>
<div class="flex items-center justify-between mt-space-md">
<span class="font-body-sm text-body-sm text-on-surface-variant truncate">BOG · MDE · CLO · BAQ</span>
<span class="font-data-mono text-data-mono font-semibold text-primary">SLA 98.2%</span>
</div>
</div>
<!-- KPI 4 -->
<div class="flex flex-col justify-between p-space-md bg-surface-container-lowest rounded-xl shadow-sm hover:shadow-md transition-shadow">
<div class="flex items-start justify-between gap-space-sm">
<div class="flex flex-col">
<span class="font-label-md text-label-md text-tertiary uppercase">En Ventana de Gracia</span>
<span class="font-display-lg text-display-lg text-tertiary tracking-tight">12</span>
</div>
<div class="w-10 h-10 rounded-lg bg-tertiary-fixed flex items-center justify-center text-on-tertiary-fixed">
<span class="material-symbols-outlined text-[22px]">hourglass_top</span>
</div>
</div>
<div class="flex items-center gap-space-xs mt-space-md">
<span class="inline-flex items-center px-space-xs py-0.5 rounded text-on-tertiary-fixed font-label-sm text-label-sm bg-tertiary-fixed">
          Críticos
        </span>
<span class="font-body-sm text-body-sm text-on-surface-variant">2 comercios en proceso</span>
</div>
</div>
</div>
<!-- Main Content Card: Filtering Toolbar + Table -->
<div class="flex flex-col bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
<!-- Toolbar Area -->
<div class="p-space-lg flex flex-col gap-space-md bg-surface-container-low">
<!-- Top Filters / Tabs -->
<div class="flex flex-wrap items-center justify-between gap-space-md">
<!-- Status Tabs -->
<div class="inline-flex p-1 bg-surface-container rounded-lg gap-space-xs" id="status-filter-pills">
<button class="filter-btn active px-space-md py-1.5 rounded-lg font-label-md text-label-md bg-surface-container-lowest text-on-surface shadow-sm" onclick="filterTable('todos')" type="button">
            Todos (147)
          </button>
<button class="filter-btn px-space-md py-1.5 rounded-lg font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors" onclick="filterTable('activo')" type="button">
            Activos (142)
          </button>
<button class="filter-btn px-space-md py-1.5 rounded-lg font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors" onclick="filterTable('suspendido')" type="button">
            Suspendidos (5)
          </button>
</div>
<!-- Export & City Selector -->
<div class="flex items-center gap-space-sm flex-wrap">
<div class="relative">
<select aria-label="Filtrar por ciudad" class="h-9 pl-space-md pr-space-xl bg-surface-container-lowest text-on-surface rounded-lg font-body-sm text-body-sm appearance-none cursor-pointer focus:outline-none focus:ring-1 focus:ring-primary">
<option value="">Ciudad: Todas (Bogotá, Medellín, Cali)</option>
<option value="bogota">Bogotá D.C.</option>
<option value="medellin">Medellín / AMVA</option>
<option value="cali">Cali</option>
<option value="barranquilla">Barranquilla</option>
</select>
<span class="material-symbols-outlined text-[18px] text-on-surface-variant absolute right-2.5 top-2 pointer-events-none">expand_more</span>
</div>
<button class="inline-flex items-center gap-space-xs h-9 px-space-md rounded-lg bg-surface-container-lowest text-on-surface font-label-md text-label-md hover:bg-surface-container transition-colors shadow-sm" type="button">
<span class="material-symbols-outlined text-[16px]">file_download</span>
<span>Exportar CSV</span>
</button>
</div>
</div>
<!-- Search Input -->
<div class="relative w-full">
<span class="material-symbols-outlined text-[20px] text-on-surface-variant absolute left-space-md top-2.5 pointer-events-none">search</span>
<input class="w-full h-11 pl-11 pr-space-md bg-surface-container-lowest text-on-surface placeholder:text-outline rounded-lg font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20" id="search-input" onkeyup="searchTable()" placeholder="Buscar negocio por nombre, NIT, dueño o teléfono..." type="text"/>
</div>
</div>
<!-- Table Responsive Container -->
<div class="w-full overflow-x-auto">
<table class="w-full text-left border-collapse" id="negocios-table">
<thead>
<tr class="bg-surface-container-low text-on-surface-variant font-label-md text-label-md uppercase">
<th class="py-space-md px-space-lg font-semibold">Nombre del Negocio</th>
<th class="py-space-md px-space-md font-semibold">Dueño / Representante</th>
<th class="py-space-md px-space-md font-semibold">Teléfono</th>
<th class="py-space-md px-space-md font-semibold">Estado</th>
<th class="py-space-md px-space-md font-semibold">Pedidos en Gracia</th>
<th class="py-space-md px-space-lg font-semibold text-right">Acciones</th>
</tr>
</thead>
<tbody class="divide-y divide-surface-container text-body-md text-on-surface font-body-md">
<!-- Row 1: Suspendido with Gracia -->
<tr class="hover:bg-surface-container-low/50 transition-colors tenant-row" data-status="suspendido">
<td class="py-space-md px-space-lg">
<div class="flex items-center gap-space-sm">
<div class="w-9 h-9 rounded-lg bg-surface-container-highest text-primary flex items-center justify-center font-display-lg text-[14px] font-bold">
                  PD
                </div>
<div class="flex flex-col">
<span class="font-label-lg text-label-lg text-on-surface">Pizzería Da Nicola Chicó</span>
<span class="font-body-sm text-body-sm text-on-surface-variant">NIT: 900.412.871-3 · Bogotá</span>
</div>
</div>
</td>
<td class="py-space-md px-space-md text-on-surface">Giancarlo Rossi</td>
<td class="py-space-md px-space-md font-data-mono text-data-mono text-on-surface">+57 315 882 1040</td>
<td class="py-space-md px-space-md">
<span class="inline-flex items-center gap-1 px-space-sm py-0.5 rounded-full bg-error-container text-on-error-container font-label-sm text-label-sm">
<span class="w-1.5 h-1.5 rounded-full bg-error"></span>
                Suspendido
              </span>
</td>
<td class="py-space-md px-space-md">
<div class="flex items-center gap-space-xs flex-wrap">
<span class="inline-flex items-center gap-1 px-space-sm py-1 rounded-full bg-tertiary-fixed text-on-tertiary-container font-label-sm text-label-sm font-semibold">
<span class="material-symbols-outlined text-[14px]">timer</span>
                  3 pedidos en gracia · vence en 18 h
                </span>
<button class="px-space-xs py-0.5 rounded bg-surface-container text-on-tertiary-container hover:bg-tertiary-fixed font-label-sm text-label-sm transition-colors" title="Forzar cierre de ventana de gracia" type="button">
                  Cerrar gracia
                </button>
</div>
</td>
<td class="py-space-md px-space-lg text-right">
<div class="inline-flex items-center gap-space-xs">
<button class="inline-flex items-center gap-1 px-space-sm py-1 rounded-lg bg-primary-container text-on-primary-container font-label-sm text-label-sm hover:opacity-90 transition-opacity" type="button">
<span class="material-symbols-outlined text-[14px]">replay</span>
<span>Reactivar</span>
</button>
<button class="w-7 h-7 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-on-surface" title="Ver detalles" type="button">
<span class="material-symbols-outlined text-[18px]">visibility</span>
</button>
</div>
</td>
</tr>
<!-- Row 2: Suspendido with Gracia -->
<tr class="hover:bg-surface-container-low/50 transition-colors tenant-row" data-status="suspendido">
<td class="py-space-md px-space-lg">
<div class="flex items-center gap-space-sm">
<div class="w-9 h-9 rounded-lg bg-surface-container-highest text-secondary flex items-center justify-center font-display-lg text-[14px] font-bold">
                  DS
                </div>
<div class="flex flex-col">
<span class="font-label-lg text-label-lg text-on-surface">Distribuidora San Jerónimo</span>
<span class="font-body-sm text-body-sm text-on-surface-variant">NIT: 811.049.200-1 · Medellín</span>
</div>
</div>
</td>
<td class="py-space-md px-space-md text-on-surface">Martha Liliana Gómez</td>
<td class="py-space-md px-space-md font-data-mono text-data-mono text-on-surface">+57 301 229 4588</td>
<td class="py-space-md px-space-md">
<span class="inline-flex items-center gap-1 px-space-sm py-0.5 rounded-full bg-error-container text-on-error-container font-label-sm text-label-sm">
<span class="w-1.5 h-1.5 rounded-full bg-error"></span>
                Suspendido
              </span>
</td>
<td class="py-space-md px-space-md">
<div class="flex items-center gap-space-xs flex-wrap">
<span class="inline-flex items-center gap-1 px-space-sm py-1 rounded-full bg-tertiary-fixed text-on-tertiary-container font-label-sm text-label-sm font-semibold">
<span class="material-symbols-outlined text-[14px]">timer</span>
                  2 pedidos en gracia · vence en 6 h
                </span>
<button class="px-space-xs py-0.5 rounded bg-surface-container text-on-tertiary-container hover:bg-tertiary-fixed font-label-sm text-label-sm transition-colors" title="Forzar cierre de ventana de gracia" type="button">
                  Cerrar gracia
                </button>
</div>
</td>
<td class="py-space-md px-space-lg text-right">
<div class="inline-flex items-center gap-space-xs">
<button class="inline-flex items-center gap-1 px-space-sm py-1 rounded-lg bg-primary-container text-on-primary-container font-label-sm text-label-sm hover:opacity-90 transition-opacity" type="button">
<span class="material-symbols-outlined text-[14px]">replay</span>
<span>Reactivar</span>
</button>
<button class="w-7 h-7 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-on-surface" title="Ver detalles" type="button">
<span class="material-symbols-outlined text-[18px]">visibility</span>
</button>
</div>
</td>
</tr>
<!-- Row 3: Activo -->
<tr class="hover:bg-surface-container-low/50 transition-colors tenant-row" data-status="activo">
<td class="py-space-md px-space-lg">
<div class="flex items-center gap-space-sm">
<div class="w-9 h-9 rounded-lg bg-surface-container-highest text-primary flex items-center justify-center font-display-lg text-[14px] font-bold">
                  DL
                </div>
<div class="flex flex-col">
<span class="font-label-lg text-label-lg text-on-surface">Droguería La Rebaja Calle 85</span>
<span class="font-body-sm text-body-sm text-on-surface-variant">NIT: 860.034.590-9 · Bogotá</span>
</div>
</div>
</td>
<td class="py-space-md px-space-md text-on-surface">Carlos Eduardo Mora</td>
<td class="py-space-md px-space-md font-data-mono text-data-mono text-on-surface">+57 310 458 9201</td>
<td class="py-space-md px-space-md">
<span class="inline-flex items-center gap-1 px-space-sm py-0.5 rounded-full bg-primary-fixed text-on-primary-fixed font-label-sm text-label-sm">
<span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                Activo
              </span>
</td>
<td class="py-space-md px-space-md text-on-surface-variant font-body-sm">
              0 pedidos activos
            </td>
<td class="py-space-md px-space-lg text-right">
<div class="inline-flex items-center gap-space-xs">
<button class="inline-flex items-center gap-1 px-space-sm py-1 rounded-lg bg-surface-container text-error font-label-sm text-label-sm hover:bg-error hover:text-on-error transition-colors" onclick="openSuspendModal('Droguería La Rebaja Calle 85')" type="button">
<span class="material-symbols-outlined text-[14px]">block</span>
<span>Suspender</span>
</button>
<button class="w-7 h-7 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-on-surface" title="Editar comercio" type="button">
<span class="material-symbols-outlined text-[18px]">edit</span>
</button>
</div>
</td>
</tr>
<!-- Row 4: Activo -->
<tr class="hover:bg-surface-container-low/50 transition-colors tenant-row" data-status="activo">
<td class="py-space-md px-space-lg">
<div class="flex items-center gap-space-sm">
<div class="w-9 h-9 rounded-lg bg-surface-container-highest text-secondary flex items-center justify-center font-display-lg text-[14px] font-bold">
                  HC
                </div>
<div class="flex flex-col">
<span class="font-label-lg text-label-lg text-on-surface">Hamburguesas El Corralito Pepe Sierra</span>
<span class="font-body-sm text-body-sm text-on-surface-variant">NIT: 830.012.339-2 · Bogotá</span>
</div>
</div>
</td>
<td class="py-space-md px-space-md text-on-surface">Andrés Felipe Ruiz</td>
<td class="py-space-md px-space-md font-data-mono text-data-mono text-on-surface">+57 318 902 3341</td>
<td class="py-space-md px-space-md">
<span class="inline-flex items-center gap-1 px-space-sm py-0.5 rounded-full bg-primary-fixed text-on-primary-fixed font-label-sm text-label-sm">
<span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                Activo
              </span>
</td>
<td class="py-space-md px-space-md text-on-surface-variant font-body-sm">
              0 pedidos activos
            </td>
<td class="py-space-md px-space-lg text-right">
<div class="inline-flex items-center gap-space-xs">
<button class="inline-flex items-center gap-1 px-space-sm py-1 rounded-lg bg-surface-container text-error font-label-sm text-label-sm hover:bg-error hover:text-on-error transition-colors" onclick="openSuspendModal('Hamburguesas El Corralito Pepe Sierra')" type="button">
<span class="material-symbols-outlined text-[14px]">block</span>
<span>Suspender</span>
</button>
<button class="w-7 h-7 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-on-surface" title="Editar comercio" type="button">
<span class="material-symbols-outlined text-[18px]">edit</span>
</button>
</div>
</td>
</tr>
<!-- Row 5: Activo -->
<tr class="hover:bg-surface-container-low/50 transition-colors tenant-row" data-status="activo">
<td class="py-space-md px-space-lg">
<div class="flex items-center gap-space-sm">
<div class="w-9 h-9 rounded-lg bg-surface-container-highest text-primary flex items-center justify-center font-display-lg text-[14px] font-bold">
                  FT
                </div>
<div class="flex flex-col">
<span class="font-label-lg text-label-lg text-on-surface">Ferretería El Tornillo Dorado</span>
<span class="font-body-sm text-body-sm text-on-surface-variant">NIT: 901.782.115-0 · Cali</span>
</div>
</div>
</td>
<td class="py-space-md px-space-md text-on-surface">Paola Restrepo</td>
<td class="py-space-md px-space-md font-data-mono text-data-mono text-on-surface">+57 312 774 1928</td>
<td class="py-space-md px-space-md">
<span class="inline-flex items-center gap-1 px-space-sm py-0.5 rounded-full bg-primary-fixed text-on-primary-fixed font-label-sm text-label-sm">
<span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                Activo
              </span>
</td>
<td class="py-space-md px-space-md text-on-surface-variant font-body-sm">
              0 pedidos activos
            </td>
<td class="py-space-md px-space-lg text-right">
<div class="inline-flex items-center gap-space-xs">
<button class="inline-flex items-center gap-1 px-space-sm py-1 rounded-lg bg-surface-container text-error font-label-sm text-label-sm hover:bg-error hover:text-on-error transition-colors" onclick="openSuspendModal('Ferretería El Tornillo Dorado')" type="button">
<span class="material-symbols-outlined text-[14px]">block</span>
<span>Suspender</span>
</button>
<button class="w-7 h-7 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-on-surface" title="Editar comercio" type="button">
<span class="material-symbols-outlined text-[18px]">edit</span>
</button>
</div>
</td>
</tr>
</tbody>
</table>
</div>
<!-- Table Footer / Pagination Status -->
<div class="p-space-md flex flex-col sm:flex-row items-center justify-between gap-space-sm bg-surface-container-low text-body-sm text-on-surface-variant">
<span>Mostrando 5 de 147 comercios afiliados registrados en FastLogi</span>
<div class="flex items-center gap-space-xs">
<button class="px-space-sm py-1 rounded bg-surface-container-lowest text-on-surface hover:bg-surface-container font-label-sm text-label-sm shadow-sm" disabled="" type="button">Anterior</button>
<span class="px-space-sm font-data-mono text-data-mono">Página 1 de 30</span>
<button class="px-space-sm py-1 rounded bg-surface-container-lowest text-on-surface hover:bg-surface-container font-label-sm text-label-sm shadow-sm" type="button">Siguiente</button>
</div>
</div>
</div>
<!-- Interactive Sandbox & Direct Preview Modals Grid -->

<!-- Interactive Dynamic Modal 1: Nuevo Negocio (Pop-up Overlay) -->
<div aria-modal="true" class="fixed inset-0 z-50 flex items-center justify-center p-space-md bg-black/40 backdrop-blur-sm hidden" id="modal-nuevo-negocio" role="dialog">
<div class="flex flex-col w-full max-w-xl bg-surface-container-lowest rounded-xl shadow-xl overflow-hidden animate-in fade-in zoom-in duration-150">
<div class="p-space-md bg-surface-container-low flex items-center justify-between">
<div class="flex items-center gap-space-xs text-primary">
<span class="material-symbols-outlined text-[22px]">add_business</span>
<span class="font-headline-sm text-headline-sm text-on-surface">Crear Nuevo Negocio en FastLogi</span>
</div>
<button class="w-8 h-8 rounded-lg flex items-center justify-center text-on-surface-variant hover:bg-surface-container" onclick="document.getElementById('modal-nuevo-negocio').classList.add('hidden')" type="button">
<span class="material-symbols-outlined text-[20px]">close</span>
</button>
</div>
<div class="p-space-lg flex flex-col gap-space-md max-h-[75vh] overflow-y-auto">
<p class="font-body-sm text-body-sm text-on-surface-variant">
          Al registrar el comercio se creará su tenant aislado y sus credenciales de acceso para la sede principal.
        </p>
<form class="flex flex-col gap-space-md">
<div class="flex flex-col gap-space-xs">
<label class="font-label-md text-label-md text-on-surface">Nombre del Negocio *</label>
<input class="h-10 px-space-md bg-surface-container-low rounded-lg text-on-surface font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary" placeholder="Ej. Distribuidora Los Alpes" type="text"/>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
<div class="flex flex-col gap-space-xs">
<label class="font-label-md text-label-md text-on-surface">Teléfono *</label>
<input class="h-10 px-space-md bg-surface-container-low rounded-lg text-on-surface font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary" placeholder="310 000 0000" type="tel"/>
</div>
<div class="flex flex-col gap-space-xs">
<label class="font-label-md text-label-md text-on-surface">Dirección *</label>
<input class="h-10 px-space-md bg-surface-container-low rounded-lg text-on-surface font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary" placeholder="Calle 100 # 15-20" type="text"/>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
<div class="flex flex-col gap-space-xs">
<label class="font-label-md text-label-md text-on-surface">Nombre del Dueño *</label>
<input class="h-10 px-space-md bg-surface-container-low rounded-lg text-on-surface font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary" placeholder="Nombre y apellido" type="text"/>
</div>
<div class="flex flex-col gap-space-xs">
<label class="font-label-md text-label-md text-on-surface">Correo Electrónico *</label>
<input class="h-10 px-space-md bg-surface-container-low rounded-lg text-on-surface font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary" placeholder="contacto@@negocio.com" type="email"/>
</div>
</div>
<div class="flex flex-col gap-space-xs">
<label class="font-label-md text-label-md text-on-surface">Contraseña Inicial *</label>
<div class="relative">
<input class="w-full h-10 px-space-md pr-10 bg-surface-container-low rounded-lg text-on-surface font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary" id="pwd-field-modal" placeholder="Mínimo 8 caracteres" type="password"/>
<button class="absolute right-2.5 top-2.5 text-on-surface-variant hover:text-on-surface" onclick="togglePasswordVisibility('pwd-field-modal', this)" type="button">
<span class="material-symbols-outlined text-[18px]">visibility</span>
</button>
</div>
<span class="font-body-sm text-body-sm text-on-surface-variant">Debe contener al menos 8 caracteres alfanuméricos.</span>
</div>
</form>
</div>
<div class="p-space-md bg-surface-container-low flex items-center justify-end gap-space-sm">
<button class="px-space-md py-space-sm rounded-lg bg-surface-container text-on-surface font-label-md text-label-md hover:bg-surface-container-high transition-colors" onclick="document.getElementById('modal-nuevo-negocio').classList.add('hidden')" type="button">
          Cancelar
        </button>
<button class="px-space-lg py-space-sm rounded-lg bg-primary text-on-primary font-label-md text-label-md shadow-sm hover:opacity-90 transition-opacity" onclick="document.getElementById('modal-nuevo-negocio').classList.add('hidden')" type="button">
          Crear Negocio
        </button>
</div>
</div>
</div>
<!-- Interactive Dynamic Modal 2: Confirmar Suspensión (Pop-up Overlay) -->
<div aria-modal="true" class="fixed inset-0 z-50 flex items-center justify-center p-space-md bg-black/40 backdrop-blur-sm hidden" id="modal-suspender-negocio" role="dialog">
<div class="flex flex-col w-full max-w-lg bg-surface-container-lowest rounded-xl shadow-xl overflow-hidden animate-in fade-in zoom-in duration-150">
<div class="p-space-md bg-surface-container-low flex items-center justify-between">
<div class="flex items-center gap-space-xs text-error">
<span class="material-symbols-outlined text-[20px]">warning</span>
<span class="font-headline-sm text-headline-sm text-on-surface">Confirmar Suspensión</span>
</div>
<button class="w-8 h-8 rounded-lg flex items-center justify-center text-on-surface-variant hover:bg-surface-container" onclick="document.getElementById('modal-suspender-negocio').classList.add('hidden')" type="button">
<span class="material-symbols-outlined text-[20px]">close</span>
</button>
</div>
<div class="p-space-lg flex flex-col gap-space-md">
<div class="flex items-start gap-space-sm">
<div class="w-10 h-10 rounded-lg bg-error-container text-error flex items-center justify-center flex-shrink-0">
<span class="material-symbols-outlined text-[24px]">front_hand</span>
</div>
<div class="flex flex-col">
<h3 class="font-headline-sm text-headline-sm text-on-surface" id="suspend-modal-target-title">¿Confirmas la suspensión?</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant">La plataforma activará los mecanismos de contingencia.</p>
</div>
</div>
<div class="p-space-md rounded-lg bg-surface-container-low flex flex-col gap-space-xs text-body-sm text-on-surface">
<span class="font-label-sm text-label-sm uppercase text-error font-bold">Al suspender este negocio:</span>
<ul class="list-disc list-inside space-y-1 text-on-surface-variant font-body-sm">
<li>Todas las ofertas y promociones públicas quedarán canceladas de inmediato.</li>
<li>Los nuevos pedidos entrantes serán bloqueados y los pendientes quedarán congelados.</li>
<li>Se activará una <strong>ventana de gracia de 24 horas</strong> para despachar los pedidos que ya están en ruta o preparación antes del bloqueo total del acceso.</li>
</ul>
</div>
<label class="flex items-start gap-space-sm p-space-sm rounded-lg bg-surface-container cursor-pointer select-none">
<input class="mt-0.5 h-4 w-4 rounded text-primary focus:ring-0" id="suspend-checkbox-confirm" type="checkbox"/>
<span class="font-body-sm text-body-sm text-on-surface font-medium leading-tight">
            Entiendo las implicaciones operativas de la ventana de gracia de 24 horas
          </span>
</label>
</div>
<div class="p-space-md bg-surface-container-low flex items-center justify-end gap-space-sm">
<button class="px-space-md py-space-sm rounded-lg bg-surface-container text-on-surface font-label-md text-label-md hover:bg-surface-container-high transition-colors" onclick="document.getElementById('modal-suspender-negocio').classList.add('hidden')" type="button">
          Cancelar
        </button>
<button class="inline-flex items-center gap-1 px-space-md py-space-sm rounded-lg bg-error text-on-error font-label-md text-label-md shadow-sm hover:opacity-90 transition-opacity" onclick="confirmSuspendAction()" type="button">
<span class="material-symbols-outlined text-[16px]">lock_clock</span>
<span>Confirmar Suspensión (Iniciar gracia 24h)</span>
</button>
</div>
</div>
</div>
<!-- Inline Micro-interactions Script -->

</div>
@endsection

@push('scripts')
<script>
function filterTable(status) {
      const rows = document.querySelectorAll('#negocios-table tbody tr.tenant-row');
      const buttons = document.querySelectorAll('#status-filter-pills .filter-btn');
      
      buttons.forEach(btn => {
        btn.className = 'filter-btn px-space-md py-1.5 rounded-lg font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors';
      });
      event.currentTarget.className = 'filter-btn active px-space-md py-1.5 rounded-lg font-label-md text-label-md bg-surface-container-lowest text-on-surface shadow-sm';

      rows.forEach(row => {
        if (status === 'todos') {
          row.style.display = '';
        } else {
          row.style.display = (row.getAttribute('data-status') === status) ? '' : 'none';
        }
      });
    }

    function searchTable() {
      const filter = document.getElementById('search-input').value.toLowerCase();
      const rows = document.querySelectorAll('#negocios-table tbody tr.tenant-row');

      rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    }

    function openSuspendModal(businessName) {
      document.getElementById('suspend-modal-target-title').innerText = '¿Confirmas la suspensión de ' + businessName + '?';
      document.getElementById('suspend-checkbox-confirm').checked = false;
      document.getElementById('modal-suspender-negocio').classList.remove('hidden');
    }

    function confirmSuspendAction() {
      const checkbox = document.getElementById('suspend-checkbox-confirm');
      if (!checkbox.checked) {
        alert('Por favor marca la casilla indicando que entiendes las implicaciones de la ventana de gracia.');
        return;
      }
      document.getElementById('modal-suspender-negocio').classList.add('hidden');
    }

    function togglePasswordVisibility(fieldId, btn) {
      const input = document.getElementById(fieldId);
      const icon = btn.querySelector('.material-symbols-outlined');
      if (input.type === 'password') {
        input.type = 'text';
        icon.innerText = 'visibility_off';
      } else {
        input.type = 'password';
        icon.innerText = 'visibility';
      }
    }
</script>
@endpush
