<div class="space-y-6">
    <x-ui.toast />

    @error('delete')
        <x-ui.alert type="error">{{ $message }}</x-ui.alert>
    @enderror

    <x-ui.page-header title="Inventario" description="Control de existencias, precios y alertas de reabastecimiento.">
        <x-slot:actions>
            <x-ui.button wire:click="openCreateModal">
                <x-icon name="plus" />
                Nuevo producto
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($lowStockCount > 0)
        <x-ui.alert type="warning">
            <p>
                <span class="font-semibold">Atención de inventario:</span>
                {{ $lowStockCount }} producto(s) por debajo del stock mínimo establecido.
            </p>
        </x-ui.alert>
    @endif

    <x-ui.card padding="none">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50/70 p-4">
            <div class="relative w-full sm:max-w-md">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400" aria-hidden="true">
                    <x-icon name="magnifying-glass" />
                </span>
                <input type="search" wire:model.live.debounce.400ms="search"
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

        <div class="overflow-x-auto">
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
                            $out = $product->stock <= 0;
                            $low = ! $out && $product->min_stock > 0 && $product->stock < $product->min_stock;
                            $badge = $out ? ['red', 'Agotado'] : ($low ? ['amber', 'Stock bajo'] : ['green', 'Normal']);
                        @endphp
                        <tr wire:key="product-{{ $product->id }}" class="hover:bg-slate-50/70">
                            <x-ui.table.td>
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-sm font-semibold text-slate-600" aria-hidden="true">
                                        {{ mb_strtoupper(mb_substr($product->name, 0, 2)) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-medium text-slate-900">{{ $product->name }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $product->barcode ?: 'Sin código de barras' }}</p>
                                    </div>
                                </div>
                            </x-ui.table.td>
                            <x-ui.table.td align="right">
                                <span class="font-semibold tabular-nums text-slate-900">{{ format_cop($product->price) }}</span>
                            </x-ui.table.td>
                            <x-ui.table.td align="right">
                                <span class="font-medium tabular-nums {{ $out ? 'text-red-600' : ($low ? 'text-amber-600' : 'text-slate-700') }}">
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
                                <x-ui.badge :color="$badge[0]">{{ $badge[1] }}</x-ui.badge>
                            </x-ui.table.td>
                            <x-ui.table.td align="right">
                                <div class="flex items-center justify-end gap-1">
                                    <x-ui.button variant="ghost" size="sm" class="w-8 px-0 hover:no-underline"
                                                 wire:click="openEditModal({{ $product->id }})"
                                                 aria-label="Editar {{ $product->name }}">
                                        <x-icon name="pencil-square" class="h-4 w-4" />
                                    </x-ui.button>
                                    <x-ui.button variant="ghost" size="sm" class="w-8 px-0 text-red-600 hover:text-red-700 hover:no-underline"
                                                 wire:click="delete({{ $product->id }})"
                                                 wire:confirm="¿Eliminar este producto? Esta acción no se puede deshacer."
                                                 aria-label="Eliminar {{ $product->name }}">
                                        <x-icon name="trash" class="h-4 w-4" />
                                    </x-ui.button>
                                </div>
                            </x-ui.table.td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-14 text-center text-sm text-slate-500">
                                No hay productos que coincidan con los filtros.
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
                <x-ui.button variant="secondary" @click="$wire.set('showModal', false)">Cancelar</x-ui.button>
                <x-ui.button type="submit" form="product-form">{{ $editingId !== null ? 'Guardar cambios' : 'Registrar producto' }}</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif
</div>
