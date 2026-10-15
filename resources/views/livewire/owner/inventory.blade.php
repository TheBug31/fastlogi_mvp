<div class="flex flex-col w-full pb-16">
    @if (session('success'))
        <div class="w-full bg-primary-fixed text-on-primary-fixed px-space-lg py-space-sm flex items-center gap-space-sm mb-space-md">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span class="font-body-md text-body-md">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="w-full bg-error-container text-on-error-container px-space-lg py-space-sm flex items-center gap-space-sm mb-space-md">
            <span class="material-symbols-outlined text-[20px]">error</span>
            <span class="font-body-md text-body-md">{{ session('error') }}</span>
        </div>
    @endif

    @error('delete')
        <div class="w-full bg-error-container text-on-error-container px-space-lg py-space-sm flex items-center gap-space-sm mb-space-md">
            <span class="material-symbols-outlined text-[20px]">error</span>
            <span class="font-body-md text-body-md">{{ $message }}</span>
        </div>
    @enderror

    <!-- Warning Banner -->
    @if ($lowStockCount > 0)
        <div class="w-full bg-[#FEF3C7] text-[#B45309] border-b border-[#F59E0B]/30 px-space-lg py-space-sm flex items-center justify-between mb-space-md">
            <div class="flex items-center gap-space-sm">
                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">warning</span>
                <span class="font-headline-sm text-headline-sm">Atención de inventario:</span>
                <span class="font-body-md">{{ $lowStockCount }} producto(s) por debajo del stock mínimo establecido.</span>
            </div>
        </div>
    @endif

    <!-- Page Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-space-md mb-space-lg">
        <div>
            <h1 class="font-headline-xl text-headline-xl text-on-surface">Gestión de Inventario</h1>
            <p class="font-body-md text-on-surface-variant">Control de existencias, precios y alertas de reabastecimiento en tiempo real.</p>
        </div>
        <div class="flex items-center gap-space-sm">
            <button class="bg-[#14B8A6] text-[#0F172A] px-space-md py-space-sm font-label-lg flex items-center gap-space-xs hover:opacity-90 transition-opacity"
                    type="button" wire:click="openCreateModal">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Nuevo producto
            </button>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-surface-container-lowest p-space-md border border-outline-variant mb-space-md flex flex-wrap items-center justify-between gap-space-md">
        <div class="flex items-center gap-space-sm flex-1 min-w-[280px]">
            <span class="material-symbols-outlined text-outline">search</span>
            <input class="w-full bg-transparent border-none text-on-surface placeholder:text-outline focus:outline-none font-body-md"
                   placeholder="Buscar por nombre o código de barras..." type="text"
                   wire:model.live.debounce.400ms="search"/>
        </div>
        <div class="flex items-center gap-space-sm">
            <select class="bg-surface-container-low border border-outline-variant px-space-sm py-space-xs font-body-sm text-on-surface focus:outline-none"
                    wire:model.live="statusFilter">
                <option value="todos">Estado: Todos</option>
                <option value="bajo">Stock bajo</option>
                <option value="normal">Normal</option>
                <option value="agotado">Agotado</option>
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
                @forelse ($products as $product)
                    @php
                        $out = $product->stock <= 0;
                        $low = $product->min_stock > 0 && $product->stock < $product->min_stock;
                    @endphp
                    <tr class="hover:bg-surface-container-low/50 transition-colors" wire:key="product-{{ $product->id }}">
                        <td class="p-space-md">
                            <div class="flex items-center gap-space-sm">
                                <div class="w-10 h-10 bg-surface-container border border-outline-variant flex items-center justify-center font-headline-sm text-primary">
                                    {{ mb_substr($product->name, 0, 2) }}
                                </div>
                                <div class="font-semibold text-on-surface">{{ $product->name }}</div>
                            </div>
                        </td>
                        <td class="p-space-md font-data-mono text-on-surface-variant">{{ $product->barcode ?: '—' }}</td>
                        <td class="p-space-md font-data-mono text-on-surface">$ {{ number_format($product->price, 0, ',', '.') }}</td>
                        <td class="p-space-md">
                            @if ($out)
                                <span class="inline-flex items-center gap-1 bg-[#FFDAD6] text-[#93000A] border border-[#BA1A1A] px-2 py-0.5 font-label-sm uppercase">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#93000A]"></span>
                                    {{ $product->stock }} {{ $product->unit }}
                                </span>
                            @elseif ($low)
                                <span class="inline-flex items-center gap-1 bg-[#FEF3C7] text-[#B45309] border border-[#F59E0B] px-2 py-0.5 font-label-sm uppercase">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#B45309] animate-pulse"></span>
                                    {{ $product->stock }} {{ $product->unit }} (Stock bajo)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 bg-[#CCFBF1] text-[#0F766E] border border-[#14B8A6] px-2 py-0.5 font-label-sm uppercase">
                                    {{ $product->stock }} {{ $product->unit }}
                                </span>
                            @endif
                        </td>
                        <td class="p-space-md font-data-mono">{{ $product->min_stock }} {{ $product->unit }}</td>
                        <td class="p-space-md text-on-surface-variant capitalize">{{ $product->unit }}</td>
                        <td class="p-space-md">
                            @if ($out)
                                <span class="bg-[#FFDAD6] text-[#93000A] font-label-sm px-2 py-1 uppercase">Agotado</span>
                            @elseif ($low)
                                <span class="bg-[#FEF3C7] text-[#B45309] font-label-sm px-2 py-1 uppercase">Alerta</span>
                            @else
                                <span class="bg-[#CCFBF1] text-[#0F766E] font-label-sm px-2 py-1 uppercase">Normal</span>
                            @endif
                        </td>
                        <td class="p-space-md text-right">
                            <div class="flex items-center justify-end gap-space-xs">
                                <button class="p-1 hover:bg-surface-container text-on-surface-variant hover:text-on-surface" title="Editar"
                                        type="button" wire:click="openEditModal({{ $product->id }})">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </button>
                                <button class="p-1 hover:bg-error-container text-error" title="Eliminar"
                                        type="button" wire:click="delete({{ $product->id }})"
                                        wire:confirm="¿Eliminar este producto? Esta acción no se puede deshacer.">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="p-space-lg text-center text-on-surface-variant" colspan="8">
                            No hay productos que coincidan con los filtros aplicados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Table Footer / Pagination -->
        <div class="p-space-md bg-surface-container-low border-t border-outline-variant flex items-center justify-between font-body-sm text-on-surface-variant">
            <span>Mostrando {{ $products->firstItem() ?? 0 }} a {{ $products->lastItem() ?? 0 }} de {{ $products->total() }} productos registrados</span>
            <div class="flex items-center gap-space-xs">
                <button class="px-space-sm py-1 border border-outline-variant bg-surface-container-lowest disabled:opacity-50"
                        type="button" wire:click="previousPage" @disabled($products->onFirstPage())>Anterior</button>
                <span class="px-space-sm font-data-mono">Página {{ $products->currentPage() }} de {{ max($products->lastPage(), 1) }}</span>
                <button class="px-space-sm py-1 border border-outline-variant bg-surface-container-lowest disabled:opacity-50"
                        type="button" wire:click="nextPage" @disabled(! $products->hasMorePages())>Siguiente</button>
            </div>
        </div>
    </div>

    <!-- MODAL: Producto (crear / editar) -->
    @if ($showModal)
        <div class="fixed inset-0 bg-[#0B1C30]/60 backdrop-blur-sm z-50 flex items-center justify-center p-space-md" role="dialog" aria-modal="true">
            <div class="bg-surface-container-lowest border border-outline-variant w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col">
                <div class="bg-surface-container-low px-space-lg py-space-md border-b border-outline-variant flex items-center justify-between">
                    <div class="flex items-center gap-space-sm">
                        <span class="material-symbols-outlined text-primary">inventory_2</span>
                        <h3 class="font-headline-sm text-headline-sm text-on-surface">
                            {{ $editingId !== null ? 'Editar Producto' : 'Registrar Nuevo Producto' }}
                        </h3>
                    </div>
                    <button class="text-on-surface-variant hover:text-on-surface" type="button" wire:click="closeModal">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form class="p-space-lg space-y-space-md" wire:submit="save">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                        <div class="flex flex-col gap-space-xs md:col-span-2">
                            <label class="font-label-md text-on-surface-variant uppercase">Nombre del producto *</label>
                            <input class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md focus:outline-none focus:border-on-surface"
                                   placeholder="Ej. Chorizo Artesanal Antioqueño" type="text" wire:model="name"/>
                            @error('name') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-space-xs">
                            <label class="font-label-md text-on-surface-variant uppercase">Código de barras / SKU</label>
                            <input class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-data-mono focus:outline-none focus:border-on-surface"
                                   placeholder="7700000000000" type="text" wire:model="barcode"/>
                            @error('barcode') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-space-xs">
                            <label class="font-label-md text-on-surface-variant uppercase">Precio de venta (COP) *</label>
                            <input class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-data-mono focus:outline-none focus:border-on-surface"
                                   placeholder="15000" type="number" min="0" step="0.01" wire:model="price"/>
                            @error('price') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-space-xs">
                            <label class="font-label-md text-on-surface-variant uppercase">Unidad de medida *</label>
                            <select class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md focus:outline-none focus:border-on-surface"
                                    wire:model="unit">
                                @foreach ($units as $option)
                                    <option value="{{ $option }}">{{ ucfirst($option) }}</option>
                                @endforeach
                            </select>
                            @error('unit') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-space-xs">
                            <label class="font-label-md text-on-surface-variant uppercase">Stock actual *</label>
                            <input class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-data-mono focus:outline-none focus:border-on-surface"
                                   type="number" min="0" wire:model="stock"/>
                            @error('stock') <span class="font-body-sm text-error flex items-center gap-1 mt-0.5">
                                <span class="material-symbols-outlined text-[14px]">error</span>{{ $message }}
                            </span> @enderror
                        </div>
                        <div class="flex flex-col gap-space-xs">
                            <label class="font-label-md text-on-surface-variant uppercase">Stock mínimo de alerta *</label>
                            <input class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-data-mono focus:outline-none focus:border-on-surface"
                                   type="number" min="0" wire:model="min_stock"/>
                            @error('min_stock') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="pt-space-md border-t border-outline-variant flex items-center justify-end gap-space-sm">
                        <button class="bg-surface border border-outline-variant px-space-md py-space-sm font-label-lg text-on-surface hover:bg-surface-container transition-colors"
                                type="button" wire:click="closeModal">
                            Cancelar
                        </button>
                        <button class="bg-[#14B8A6] text-[#0F172A] px-space-lg py-space-sm font-label-lg hover:opacity-90 transition-opacity" type="submit">
                            {{ $editingId !== null ? 'Guardar cambios' : 'Guardar producto' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
