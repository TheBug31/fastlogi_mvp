<div class="space-y-6">
    @error('delete')
        <x-ui.alert type="error">{{ $message }}</x-ui.alert>
    @enderror

    <x-ui.page-header title="Inventario" description="Control de existencias, precios, stock mínimo y alertas de reabastecimiento.">
        <x-slot:actions>
            <x-ui.button wire:click="openCreateModal" type="button">
                <x-icon name="plus" class="h-5 w-5" />
                Nuevo producto
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <x-ui.kpi label="Total Productos" icon="archive-box">
            {{ number_format($totalProductCount) }}
            <x-slot:trend>Registrados en tu inventario</x-slot:trend>
        </x-ui.kpi>
        <x-ui.kpi label="Stock Bajo" icon="exclamation-triangle">
            <span class="text-amber-600">{{ number_format($lowStockCount) }}</span>
            <x-slot:trend>Por debajo del mínimo</x-slot:trend>
        </x-ui.kpi>
        <x-ui.kpi label="Agotados" icon="x-circle">
            <span class="text-red-600">{{ number_format($outOfStockCount) }}</span>
            <x-slot:trend>Sin unidades disponibles</x-slot:trend>
        </x-ui.kpi>
    </div>

    @if ($lowStockCount > 0 || $outOfStockCount > 0)
        <x-ui.alert type="warning">
            <p>
                <span class="font-semibold">Atención de inventario:</span>
                {{ $lowStockCount }} producto(s) por debajo del mínimo y {{ $outOfStockCount }} producto(s) agotado(s).
            </p>
        </x-ui.alert>
    @endif

    <x-ui.card padding="none">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50/70 p-4">
            <div class="relative w-full sm:max-w-md">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400" aria-hidden="true">
                    <x-icon name="magnifying-glass" class="h-5 w-5" />
                </span>
                <input type="search" wire:model.live.debounce.250ms="search"
                       aria-label="Buscar por nombre o código de barras"
                       placeholder="Buscar por nombre o código de barras…"
                       class="h-10 w-full rounded-lg border border-slate-300 bg-white pl-10 pr-3.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20"/>
            </div>

            <div class="w-full sm:w-52">
                <x-ui.select name="statusFilter" aria-label="Filtrar por estado" wire:model.live="statusFilter">
                    <option value="todos">Estado: todos</option>
                    <option value="bajo">Stock bajo</option>
                    <option value="normal">Normal</option>
                    <option value="agotado">Agotado</option>
                </x-ui.select>
            </div>
        </div>

        <div class="divide-y divide-slate-200 lg:hidden">
            @forelse ($products as $product)
                @php
                    $badge = $product->status_badge;
                    $badgeColor = match ($badge['color'] ?? 'green') {
                        'red' => 'red',
                        'amber' => 'amber',
                        default => 'green',
                    };
                @endphp
                <article wire:key="product-card-{{ $product->id }}" class="space-y-4 bg-white p-4">
                    <div class="flex min-w-0 items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-sm font-semibold text-slate-600 ring-1 ring-inset ring-slate-200" aria-hidden="true">
                                {{ mb_strtoupper(mb_substr($product->name, 0, 2)) }}
                            </span>
                            <div class="min-w-0">
                                <h2 class="break-words text-sm font-semibold text-slate-900">{{ $product->name }}</h2>
                                <p class="mt-0.5 break-all text-xs text-slate-500">{{ $product->barcode ?: 'Sin código de barras' }}</p>
                            </div>
                        </div>
                        <x-ui.badge :color="$badgeColor">{{ $badge['label'] ?? 'Normal' }}</x-ui.badge>
                    </div>

                    <dl class="grid grid-cols-2 gap-3 rounded-lg bg-slate-50 p-3 text-sm">
                        <div>
                            <dt class="text-xs text-slate-500">Precio</dt>
                            <dd class="mt-0.5 font-semibold tabular-nums text-slate-900">{{ format_cop($product->price) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Stock actual</dt>
                            <dd class="mt-0.5 font-semibold tabular-nums {{ $product->stock <= 0 ? 'text-red-600' : (($product->min_stock > 0 && $product->stock < $product->min_stock) ? 'text-amber-600' : 'text-slate-900') }}">
                                {{ $product->stock }} <span class="font-normal capitalize text-slate-500">{{ $product->unit }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Stock mínimo</dt>
                            <dd class="mt-0.5 tabular-nums text-slate-900">{{ $product->min_stock }} {{ $product->unit }}</dd>
                        </div>
                    </dl>

                    <div class="flex gap-2">
                        <button type="button" wire:click="openEditModal({{ $product->id }})" wire:loading.attr="disabled"
                                class="inline-flex min-h-10 flex-1 items-center justify-center gap-2 rounded-lg border border-slate-300 px-3 text-sm font-medium text-slate-700 hover:bg-teal-50 hover:text-teal-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-600"
                                aria-label="Editar {{ $product->name }}">
                            <x-icon name="pencil-square" class="h-4 w-4" /> Editar
                        </button>
                        <button type="button" wire:click="delete({{ $product->id }})" wire:loading.attr="disabled"
                                wire:confirm="¿Eliminar '{{ $product->name }}'? Esta acción no se puede deshacer."
                                class="inline-flex min-h-10 flex-1 items-center justify-center gap-2 rounded-lg border border-red-200 px-3 text-sm font-medium text-red-700 hover:bg-red-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600"
                                aria-label="Eliminar {{ $product->name }}">
                            <x-icon name="trash" class="h-4 w-4" /> Eliminar
                        </button>
                    </div>
                </article>
            @empty
                <div class="px-4 py-10 text-center text-sm text-slate-500">No hay productos que coincidan con los filtros.</div>
            @endforelse
        </div>

        <div class="hidden overflow-x-auto lg:block">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <x-ui.table.th>Producto</x-ui.table.th>
                        <x-ui.table.th align="right">Precio</x-ui.table.th>
                        <x-ui.table.th align="right">Stock</x-ui.table.th>
                        <x-ui.table.th align="right">Mínimo</x-ui.table.th>
                        <x-ui.table.th>Unidad</x-ui.table.th>
                        <x-ui.table.th>Estado</x-ui.table.th>
                        <x-ui.table.th align="right">Acciones</x-ui.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $product)
                        @php
                            $badge = $product->status_badge;
                            $badgeColor = match ($badge['color'] ?? 'green') {
                                'red' => 'red',
                                'amber' => 'amber',
                                default => 'green',
                            };
                        @endphp
                        <tr wire:key="product-{{ $product->id }}" class="transition-colors hover:bg-slate-50/70">
                            <x-ui.table.td>
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-sm font-semibold text-slate-600 ring-1 ring-inset ring-slate-200" aria-hidden="true">
                                        {{ mb_strtoupper(mb_substr($product->name, 0, 2)) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="whitespace-normal break-words font-medium text-slate-900">{{ $product->name }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $product->barcode ?: 'Sin código de barras' }}</p>
                                    </div>
                                </div>
                            </x-ui.table.td>
                            <x-ui.table.td align="right">
                                <span class="font-semibold tabular-nums text-slate-900">{{ format_cop($product->price) }}</span>
                            </x-ui.table.td>
                            <x-ui.table.td align="right">
                                <span class="font-medium tabular-nums {{ ($product->is_out_of_stock ?? $product->stock <= 0) ? 'text-red-600' : (($product->is_low_stock ?? ($product->min_stock > 0 && $product->stock > 0 && $product->stock < $product->min_stock)) ? 'text-amber-600' : 'text-slate-700') }}">
                                    {{ $product->stock }}
                                </span>
                            </x-ui.table.td>
                            <x-ui.table.td align="right">
                                <span class="tabular-nums text-slate-700">{{ $product->min_stock }}</span>
                            </x-ui.table.td>
                            <x-ui.table.td>
                                <span class="capitalize text-slate-700">{{ $product->unit }}</span>
                            </x-ui.table.td>
                            <x-ui.table.td>
                                <x-ui.badge :color="$badgeColor" class="ring-1 ring-inset {{ $badgeColor === 'green' ? 'ring-green-600/20' : ($badgeColor === 'amber' ? 'ring-amber-600/20' : 'ring-red-600/20') }}">{{ $badge['label'] ?? 'Normal' }}</x-ui.badge>
                            </x-ui.table.td>
                            <x-ui.table.td align="right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button"
                                            wire:click="openEditModal({{ $product->id }})"
                                            wire:loading.attr="disabled"
                                            aria-label="Editar {{ $product->name }}"
                                            title="Editar producto"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 text-slate-500 transition-colors hover:bg-teal-50 hover:text-teal-600 focus:outline-none disabled:opacity-50">
                                        <x-icon name="pencil-square" class="h-4 w-4" />
                                    </button>
                                    <button type="button"
                                            wire:click="delete({{ $product->id }})"
                                            wire:loading.attr="disabled"
                                            wire:confirm="¿Eliminar '{{ $product->name }}'? Esta acción no se puede deshacer."
                                            aria-label="Eliminar {{ $product->name }}"
                                            title="Eliminar producto"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 text-slate-500 transition-colors hover:bg-red-50 hover:text-red-600 focus:outline-none disabled:opacity-50">
                                        <x-icon name="trash" class="h-4 w-4" />
                                    </button>
                                </div>
                            </x-ui.table.td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center gap-3">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200">
                                        <x-icon name="magnifying-glass" class="h-6 w-6" aria-hidden="true" />
                                    </span>
                                    <div>
                                        <p class="text-base font-semibold text-slate-900">Sin resultados</p>
                                        <p class="mt-1 text-sm text-slate-500">No encontramos productos que coincidan con tu búsqueda o filtros aplicados.</p>
                                    </div>
                                    <x-ui.button variant="secondary" type="button" wire:click="clearFilters">
                                        Limpiar filtros
                                    </x-ui.button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $products->links('partials.pagination', ['label' => 'productos']) }}
    </x-ui.card>

    @if ($showModal)
        <x-ui.modal property="showModal"
                    :title="$editingId !== null ? 'Editar producto' : 'Nuevo producto'"
                    :subtitle="$editingId !== null ? 'Actualiza los datos y el stock del producto.' : 'Registra un producto en tu inventario.'">
            <form id="product-form" wire:submit="save" class="space-y-5">
                <x-ui.input name="name" label="Nombre del producto" required wire:model="name"
                            placeholder="Ej. Chorizo artesanal antioqueño" />

                <x-ui.input name="barcode" label="Código de barras / SKU" wire:model="barcode"
                            placeholder="7700000000000" help="Opcional." />

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-ui.input name="price" type="number" min="0" step="0.01" label="Precio de venta (COP)"
                                required wire:model="price" />

                    <x-ui.select name="unit" label="Unidad de medida" required wire:model="unit">
                        @foreach ($units as $option)
                            <option value="{{ $option }}">{{ ucfirst($option) }}</option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.input name="stock" type="number" min="0" label="Stock actual" required
                                wire:model="stock" help="El stock no puede ser negativo." />

                    <x-ui.input name="min_stock" type="number" min="0" label="Stock mínimo de alerta" required
                                wire:model="min_stock" help="Recibirás un aviso al bajar de este valor." />
                </div>
            </form>

            <x-slot:footer>
                <x-ui.button variant="secondary" type="button" wire:click="closeModal" wire:loading.attr="disabled" wire:target="save">Cancelar</x-ui.button>
                <x-ui.button type="submit" form="product-form" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $editingId !== null ? 'Guardar cambios' : 'Registrar producto' }}</span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif
</div>
