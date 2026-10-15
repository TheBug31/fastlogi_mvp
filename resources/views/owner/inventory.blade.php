@extends('layouts.owner')

@section('title', 'Inventario')

@section('content')
<div class="flex flex-col w-full pb-16">
<!-- Warning Banner -->
<div class="w-full bg-[#FEF3C7] text-[#B45309] border-b border-[#F59E0B]/30 px-space-lg py-space-sm flex items-center justify-between mb-space-md">
<div class="flex items-center gap-space-sm">
<span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">warning</span>
<span class="font-headline-sm text-headline-sm">Atención de inventario:</span>
<span class="font-body-md">2 productos se encuentran por debajo del stock mínimo establecido para la Sede Bogotá 93.</span>
</div>
<button class="font-label-sm uppercase text-on-surface hover:underline" onclick="this.parentElement.style.display='none'">Descartar</button>
</div>
<!-- Page Header & Actions -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-space-md mb-space-lg">
<div>
<h1 class="font-headline-xl text-headline-xl text-on-surface">Gestión de Inventario</h1>
<p class="font-body-md text-on-surface-variant">Control de existencias, precios y alertas de reabastecimiento en tiempo real.</p>
</div>
<div class="flex items-center gap-space-sm">
<button class="bg-[#14B8A6] text-[#0F172A] px-space-md py-space-sm font-label-lg flex items-center gap-space-xs hover:opacity-90 transition-opacity" onclick="openModal()">
<span class="material-symbols-outlined text-[18px]">add</span>
        Nuevo producto
      </button>
<button class="bg-surface-container-low text-on-surface border border-outline-variant px-space-md py-space-sm font-label-lg flex items-center gap-space-xs hover:bg-surface-container transition-colors">
<span class="material-symbols-outlined text-[18px]">download</span>
        Exportar CSV
      </button>
</div>
</div>
<!-- Filters & Search Bar -->
<div class="bg-surface-container-lowest p-space-md border border-outline-variant mb-space-md flex flex-wrap items-center justify-between gap-space-md">
<div class="flex items-center gap-space-sm flex-1 min-w-[280px]">
<span class="material-symbols-outlined text-outline">search</span>
<input class="w-full bg-transparent border-none text-on-surface placeholder:text-outline focus:outline-none font-body-md" placeholder="Buscar por nombre, SKU o código de barras..." type="text"/>
</div>
<div class="flex items-center gap-space-sm">
<select class="bg-surface-container-low border border-outline-variant px-space-sm py-space-xs font-body-sm text-on-surface focus:outline-none">
<option>Todas las categorías</option>
<option>Bebidas</option>
<option>Carnes y Cortes</option>
<option>Insumos de Cocina</option>
</select>
<select class="bg-surface-container-low border border-outline-variant px-space-sm py-space-xs font-body-sm text-on-surface focus:outline-none">
<option>Estado: Todos</option>
<option>Stock bajo</option>
<option>Normal</option>
<option>Agotado</option>
</select>
</div>
</div>
<!-- Inventory Table -->
<div class="bg-surface-container-lowest border border-outline-variant overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-low border-b border-outline-variant font-label-md text-on-surface-variant uppercase">
<th class="p-space-md">Producto</th>
<th class="p-space-md">Código de barras</th>
<th class="p-space-md">Precio (COP)</th>
<th class="p-space-md">Stock</th>
<th class="p-space-md">Mínimo</th>
<th class="p-space-md">Unidad</th>
<th class="p-space-md">Estado</th>
<th class="p-space-md text-right">Acciones</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant font-body-md">
<!-- Row 1 -->
<tr class="hover:bg-surface-container-low/50 transition-colors">
<td class="p-space-md flex items-center gap-space-sm">
<div class="w-10 h-10 bg-surface-container border border-outline-variant flex items-center justify-center font-headline-sm text-primary">🥩</div>
<div>
<div class="font-semibold text-on-surface">Lomo Fino de Res (Madurado 21 días)</div>
<div class="font-body-sm text-on-surface-variant">SKU: LOM-9921</div>
</div>
</td>
<td class="p-space-md font-data-mono text-on-surface-variant">7707312948102</td>
<td class="p-space-md font-data-mono text-on-surface">$ 48.500</td>
<td class="p-space-md">
<span class="inline-flex items-center gap-1 bg-[#FEF3C7] text-[#B45309] border border-[#F59E0B] px-2 py-0.5 font-label-sm uppercase">
<span class="w-1.5 h-1.5 rounded-full bg-[#B45309] animate-pulse"></span>
              3 kg (Stock bajo)
            </span>
</td>
<td class="p-space-md font-data-mono">5 kg</td>
<td class="p-space-md text-on-surface-variant">Kilogramo</td>
<td class="p-space-md"><span class="bg-[#FEF3C7] text-[#B45309] font-label-sm px-2 py-1 uppercase">Alerta</span></td>
<td class="p-space-md text-right">
<div class="flex items-center justify-end gap-space-xs">
<button class="p-1 hover:bg-surface-container text-on-surface-variant hover:text-on-surface" title="Editar"><span class="material-symbols-outlined text-[18px]">edit</span></button>
<button class="p-1 hover:bg-error-container text-error" title="Eliminar"><span class="material-symbols-outlined text-[18px]">delete</span></button>
</div>
</td>
</tr>
<!-- Row 2 -->
<tr class="hover:bg-surface-container-low/50 transition-colors">
<td class="p-space-md flex items-center gap-space-sm">
<div class="w-10 h-10 bg-surface-container border border-outline-variant flex items-center justify-center font-headline-sm text-primary">🍷</div>
<div>
<div class="font-semibold text-on-surface">Vino Tinto Reserva Malbec 750ml</div>
<div class="font-body-sm text-on-surface-variant">SKU: VIN-4412</div>
</div>
</td>
<td class="p-space-md font-data-mono text-on-surface-variant">7701122334455</td>
<td class="p-space-md font-data-mono text-on-surface">$ 85.000</td>
<td class="p-space-md">
<span class="inline-flex items-center gap-1 bg-[#CCFBF1] text-[#0F766E] border border-[#14B8A6] px-2 py-0.5 font-label-sm uppercase">
              42 un
            </span>
</td>
<td class="p-space-md font-data-mono">10 un</td>
<td class="p-space-md text-on-surface-variant">Unidad</td>
<td class="p-space-md"><span class="bg-[#CCFBF1] text-[#0F766E] font-label-sm px-2 py-1 uppercase">Normal</span></td>
<td class="p-space-md text-right">
<div class="flex items-center justify-end gap-space-xs">
<button class="p-1 hover:bg-surface-container text-on-surface-variant hover:text-on-surface" title="Editar"><span class="material-symbols-outlined text-[18px]">edit</span></button>
<button class="p-1 hover:bg-error-container text-error" title="Eliminar"><span class="material-symbols-outlined text-[18px]">delete</span></button>
</div>
</td>
</tr>
<!-- Row 3 -->
<tr class="hover:bg-surface-container-low/50 transition-colors">
<td class="p-space-md flex items-center gap-space-sm">
<div class="w-10 h-10 bg-surface-container border border-outline-variant flex items-center justify-center font-headline-sm text-primary">🧀</div>
<div>
<div class="font-semibold text-on-surface">Queso Paipa Artesanal Madurado</div>
<div class="font-body-sm text-on-surface-variant">SKU: QUES-8812</div>
</div>
</td>
<td class="p-space-md font-data-mono text-on-surface-variant">7709988776655</td>
<td class="p-space-md font-data-mono text-on-surface">$ 32.000</td>
<td class="p-space-md">
<span class="inline-flex items-center gap-1 bg-[#FFDAD6] text-[#93000A] border border-[#BA1A1A] px-2 py-0.5 font-label-sm uppercase">
              0 un
            </span>
</td>
<td class="p-space-md font-data-mono">8 un</td>
<td class="p-space-md text-on-surface-variant">Unidad</td>
<td class="p-space-md"><span class="bg-[#FFDAD6] text-[#93000A] font-label-sm px-2 py-1 uppercase">Agotado</span></td>
<td class="p-space-md text-right">
<div class="flex items-center justify-end gap-space-xs">
<button class="p-1 hover:bg-surface-container text-on-surface-variant hover:text-on-surface" title="Editar"><span class="material-symbols-outlined text-[18px]">edit</span></button>
<button class="p-1 hover:bg-error-container text-error" title="Eliminar"><span class="material-symbols-outlined text-[18px]">delete</span></button>
</div>
</td>
</tr>
<!-- Row 4 -->
<tr class="hover:bg-surface-container-low/50 transition-colors">
<td class="p-space-md flex items-center gap-space-sm">
<div class="w-10 h-10 bg-surface-container border border-outline-variant flex items-center justify-center font-headline-sm text-primary">🥔</div>
<div>
<div class="font-semibold text-on-surface">Papa Criolla Seleccionada</div>
<div class="font-body-sm text-on-surface-variant">SKU: PAP-1029</div>
</div>
</td>
<td class="p-space-md font-data-mono text-on-surface-variant">7705544332211</td>
<td class="p-space-md font-data-mono text-on-surface">$ 6.500</td>
<td class="p-space-md">
<span class="inline-flex items-center gap-1 bg-[#FEF3C7] text-[#B45309] border border-[#F59E0B] px-2 py-0.5 font-label-sm uppercase">
              10 kg (Stock bajo)
            </span>
</td>
<td class="p-space-md font-data-mono">25 kg</td>
<td class="p-space-md text-on-surface-variant">Kilogramo</td>
<td class="p-space-md"><span class="bg-[#FEF3C7] text-[#B45309] font-label-sm px-2 py-1 uppercase">Alerta</span></td>
<td class="p-space-md text-right">
<div class="flex items-center justify-end gap-space-xs">
<button class="p-1 hover:bg-surface-container text-on-surface-variant hover:text-on-surface" title="Editar"><span class="material-symbols-outlined text-[18px]">edit</span></button>
<button class="p-1 hover:bg-error-container text-error" title="Eliminar"><span class="material-symbols-outlined text-[18px]">delete</span></button>
</div>
</td>
</tr>
</tbody>
</table>
<!-- Table Footer / Pagination -->
<div class="p-space-md bg-surface-container-low border-t border-outline-variant flex items-center justify-between font-body-sm text-on-surface-variant">
<span>Mostrando 4 de 128 productos registrados</span>
<div class="flex items-center gap-space-xs">
<button class="px-space-sm py-1 border border-outline-variant bg-surface-container-lowest disabled:opacity-50" disabled="">Anterior</button>
<button class="px-space-sm py-1 border border-outline-variant bg-primary text-on-primary">1</button>
<button class="px-space-sm py-1 border border-outline-variant bg-surface-container-lowest">2</button>
<button class="px-space-sm py-1 border border-outline-variant bg-surface-container-lowest">3</button>
<button class="px-space-sm py-1 border border-outline-variant bg-surface-container-lowest">Siguiente</button>
</div>
</div>
</div>
<!-- MODAL: Nuevo Producto Overlay -->
<div class="fixed inset-0 bg-[#0B1C30]/60 backdrop-blur-sm z-50 flex items-center justify-center p-space-md" id="nuevoProductoModal">
<div class="bg-surface-container-lowest border border-outline-variant w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col">
<!-- Modal Header -->
<div class="bg-surface-container-low px-space-lg py-space-md border-b border-outline-variant flex items-center justify-between">
<div class="flex items-center gap-space-sm">
<span class="material-symbols-outlined text-primary">inventory_2</span>
<h3 class="font-headline-sm text-headline-sm text-on-surface">Registrar Nuevo Producto</h3>
</div>
<button class="text-on-surface-variant hover:text-on-surface" onclick="closeModal()">
<span class="material-symbols-outlined">close</span>
</button>
</div>
<!-- Modal Body -->
<form class="p-space-lg space-y-space-md">
<div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
<!-- Nombre -->
<div class="flex flex-col gap-space-xs">
<label class="font-label-md text-on-surface-variant uppercase">Nombre del producto *</label>
<input class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md focus:outline-none focus:border-on-surface" placeholder="Ej. Chorizo Artesanal Antioqueño" required="" type="text"/>
</div>
<!-- Código de barras -->
<div class="flex flex-col gap-space-xs">
<label class="font-label-md text-on-surface-variant uppercase">Código de barras / SKU *</label>
<input class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-data-mono focus:outline-none focus:border-on-surface" placeholder="7700000000000" required="" type="text"/>
</div>
<!-- Precio -->
<div class="flex flex-col gap-space-xs">
<label class="font-label-md text-on-surface-variant uppercase">Precio de venta (COP) *</label>
<input class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-data-mono focus:outline-none focus:border-on-surface" placeholder="15000" required="" type="number"/>
</div>
<!-- Unidad -->
<div class="flex flex-col gap-space-xs">
<label class="font-label-md text-on-surface-variant uppercase">Unidad de medida *</label>
<select class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md focus:outline-none focus:border-on-surface">
<option>Unidad (un)</option>
<option>Kilogramo (kg)</option>
<option>Litro (lt)</option>
<option>Porción (pza)</option>
</select>
</div>
<!-- Stock (Con error validation simulado) -->
<div class="flex flex-col gap-space-xs">
<label class="font-label-md text-on-surface-variant uppercase">Stock inicial *</label>
<input class="bg-surface border border-error px-space-sm py-space-xs text-on-surface font-data-mono focus:outline-none" type="number" value="-2"/>
<span class="font-body-sm text-error flex items-center gap-1 mt-0.5">
<span class="material-symbols-outlined text-[14px]">error</span>
              El stock no puede ser negativo
            </span>
</div>
<!-- Stock mínimo -->
<div class="flex flex-col gap-space-xs">
<label class="font-label-md text-on-surface-variant uppercase">Stock mínimo de alerta *</label>
<input class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-data-mono focus:outline-none focus:border-on-surface" type="number" value="10"/>
</div>
</div>
<!-- Sede assignment -->
<div class="pt-space-sm">
<label class="font-label-md text-on-surface-variant uppercase block mb-space-xs">Asignar a Sede</label>
<div class="flex items-center gap-space-md">
<label class="flex items-center gap-space-xs font-body-md cursor-pointer">
<input checked="" class="w-4 h-4 rounded-none accent-primary" type="checkbox"/> Bogotá 93
            </label>
<label class="flex items-center gap-space-xs font-body-md cursor-pointer">
<input class="w-4 h-4 rounded-none accent-primary" type="checkbox"/> Chía Centro
            </label>
<label class="flex items-center gap-space-xs font-body-md cursor-pointer">
<input class="w-4 h-4 rounded-none accent-primary" type="checkbox"/> Medellín Poblado
            </label>
</div>
</div>
<!-- Modal Footer Actions -->
<div class="pt-space-md border-t border-outline-variant flex items-center justify-end gap-space-sm">
<button class="bg-surface border border-outline-variant px-space-md py-space-sm font-label-lg text-on-surface hover:bg-surface-container transition-colors" onclick="closeModal()" type="button">
            Cancelar
          </button>
<button class="bg-[#14B8A6] text-[#0F172A] px-space-lg py-space-sm font-label-lg hover:opacity-90 transition-opacity" type="button">
            Guardar producto
          </button>
</div>
</form>
</div>
</div>

</div>
@endsection

@push('scripts')
<script>
function closeModal() {
      document.getElementById('nuevoProductoModal').style.display = 'none';
    }
    function openModal() {
      document.getElementById('nuevoProductoModal').style.display = 'flex';
    }
</script>
@endpush
