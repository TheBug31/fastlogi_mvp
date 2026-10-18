<div class="space-y-6">
    <x-ui.toast />

    @php
        $threshold = auth()->user()?->business?->inactivity_threshold_minutes ?? 15;
        $disconnectedWithOrders = $drivers->filter(
            fn ($driver) => $driver->availability === \App\Enums\DriverAvailability::Offline && $driver->active_orders_count > 0,
        );
    @endphp

    <x-ui.page-header title="Repartidores" description="Supervisión de la flota, disponibilidad y límites de carga.">
        <x-slot:actions>
            <x-ui.button wire:click="openCreateModal">
                <x-icon name="plus" />
                Nuevo repartidor
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($disconnectedWithOrders->isNotEmpty())
        <x-ui.alert type="error">
            <p>
                <span class="font-semibold">Sin conexión con pedidos asignados:</span>
                {{ $disconnectedWithOrders->count() }} repartidor(es) desconectado(s) con pedidos activos.
            </p>
            <x-slot:actions>
                <a href="{{ route('owner.orders.index') }}" wire:navigate class="font-medium underline">Ver pedidos</a>
            </x-slot:actions>
        </x-ui.alert>
    @endif

    <x-ui.card padding="none">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50/70 p-4">
            <div class="relative w-full sm:max-w-md">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400" aria-hidden="true">
                    <x-icon name="magnifying-glass" />
                </span>
                <input type="search" wire:model.live.debounce.400ms="search"
                       aria-label="Buscar por nombre, correo o teléfono"
                       placeholder="Buscar por nombre, correo o teléfono…"
                       class="h-10 w-full rounded-lg border border-slate-300 bg-white pl-10 pr-3.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20"/>
            </div>

            <div class="w-full sm:w-52">
                <x-ui.select name="availabilityFilter" aria-label="Filtrar por disponibilidad" wire:model.live="availabilityFilter">
                    <option value="todos">Disponibilidad: todas</option>
                    @foreach ($availabilities as $availability)
                        <option value="{{ $availability->value }}">{{ $availability->label() }}</option>
                    @endforeach
                </x-ui.select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <x-ui.table.th>Repartidor</x-ui.table.th>
                        <x-ui.table.th>Disponibilidad</x-ui.table.th>
                        <x-ui.table.th>Pedidos activos</x-ui.table.th>
                        <x-ui.table.th>Última conexión</x-ui.table.th>
                        <x-ui.table.th align="right">Límite de pedidos</x-ui.table.th>
                        <x-ui.table.th align="right">Acciones</x-ui.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($drivers as $driver)
                        @php
                            $offline = $driver->availability === \App\Enums\DriverAvailability::Offline;
                            $stale = $driver->last_seen_at === null || $driver->last_seen_at->lt(now()->subMinutes($threshold));
                        @endphp
                        <tr wire:key="driver-{{ $driver->id }}" class="hover:bg-slate-50/70">
                            <x-ui.table.td>
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar :name="$driver->user?->name ?? ''" />
                                    <div class="min-w-0">
                                        <p class="font-medium text-slate-900">{{ $driver->user?->name }}</p>
                                        <p class="mt-0.5 text-xs tabular-nums text-slate-500">{{ $driver->user?->phone ?: 'Sin teléfono' }}</p>
                                    </div>
                                </div>
                            </x-ui.table.td>
                            <x-ui.table.td>
                                <x-ui.availability-badge :availability="$driver->availability" />
                            </x-ui.table.td>
                            <x-ui.table.td>
                                <div class="w-40">
                                    <x-ui.capacity-bar :active="$driver->active_orders_count" :max="$driver->max_active_orders" />
                                </div>
                            </x-ui.table.td>
                            <x-ui.table.td>
                                <span class="text-xs {{ $stale ? 'font-medium text-amber-600' : 'text-slate-500' }}">
                                    {{ $driver->last_seen_at ? $driver->last_seen_at->diffForHumans() : 'Sin señal' }}
                                </span>
                            </x-ui.table.td>
                            <x-ui.table.td align="right">
                                <span class="tabular-nums text-slate-700">{{ $driver->max_active_orders }}</span>
                                <span class="text-slate-500">pedidos</span>
                            </x-ui.table.td>
                            <x-ui.table.td align="right">
                                <div class="flex items-center justify-end gap-1">
                                    <x-ui.button variant="ghost" size="sm" class="w-8 px-0 hover:no-underline"
                                                 wire:click="openEditModal({{ $driver->id }})"
                                                 aria-label="Editar {{ $driver->user?->name }}">
                                        <x-icon name="pencil-square" class="h-4 w-4" />
                                    </x-ui.button>
                                    <x-ui.button variant="ghost" size="sm"
                                                 class="w-8 px-0 hover:no-underline {{ $offline ? 'text-teal-700' : 'text-slate-500' }}"
                                                 wire:click="toggleAvailability({{ $driver->id }})"
                                                 aria-label="{{ $offline ? 'Marcar disponible' : 'Marcar desconectado' }}"
                                                 title="{{ $offline ? 'Marcar disponible' : 'Marcar desconectado' }}">
                                        <x-icon name="power" class="h-4 w-4" />
                                    </x-ui.button>
                                </div>
                            </x-ui.table.td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-14 text-center text-sm text-slate-500">
                                No hay repartidores que coincidan con el filtro.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $drivers->links('partials.pagination', ['label' => 'repartidores']) }}
    </x-ui.card>

    @if ($showCreateModal)
        <x-ui.modal property="showCreateModal" title="Nuevo repartidor"
                    subtitle="El repartidor podrá iniciar sesión con estas credenciales.">
            <form id="driver-create-form" wire:submit="createDriver" class="space-y-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-ui.input name="name" label="Nombre" required wire:model="name" />
                    <x-ui.input name="phone" label="Teléfono" type="tel" required wire:model="phone" />
                    <div class="sm:col-span-2">
                        <x-ui.input name="email" type="email" label="Correo" required wire:model="email" autocomplete="off" />
                    </div>
                    <div class="sm:col-span-2" x-data="{ show: false }">
                        <x-ui.input name="password" label="Contraseña" type="password" required wire:model="password"
                                    autocomplete="new-password" help="Mínimo 8 caracteres.">
                            <x-slot:suffix>
                                <button type="button" @click="show = !show"
                                        :aria-label="show ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                        class="flex items-center rounded text-slate-400 transition-colors hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600">
                                    <x-icon name="eye" x-show="!show" />
                                    <x-icon name="eye-slash" x-show="show" x-cloak />
                                </button>
                            </x-slot:suffix>
                        </x-ui.input>
                    </div>
                </div>

                <div>
                    <label for="max_active_orders" class="mb-1.5 block text-sm font-medium text-slate-700">Máximo de pedidos activos</label>
                    <div class="flex items-center gap-4">
                        <input id="max_active_orders" type="range" min="1" max="8" step="1" wire:model.live="max_active_orders"
                               class="w-full cursor-pointer accent-teal-600"/>
                        <span class="w-24 shrink-0 text-right text-sm font-medium tabular-nums text-slate-700">{{ $max_active_orders }} pedido(s)</span>
                    </div>
                    @error('max_active_orders') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                </div>
            </form>

            <x-slot:footer>
                <x-ui.button variant="secondary" @click="$wire.set('showCreateModal', false)">Cancelar</x-ui.button>
                <x-ui.button type="submit" form="driver-create-form">Vincular repartidor</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    @if ($showEditModal)
        <x-ui.modal property="showEditModal" title="Editar repartidor"
                    subtitle="Actualiza los datos y el límite de carga del repartidor.">
            <form id="driver-edit-form" wire:submit="updateDriver" class="space-y-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-ui.input name="edit_name" label="Nombre" required wire:model="edit_name" />
                    <x-ui.input name="edit_phone" label="Teléfono" type="tel" required wire:model="edit_phone" />
                    <div class="sm:col-span-2">
                        <x-ui.input name="edit_email" type="email" label="Correo" required wire:model="edit_email" />
                    </div>
                </div>

                <div>
                    <label for="edit_max_active_orders" class="mb-1.5 block text-sm font-medium text-slate-700">Máximo de pedidos activos</label>
                    <div class="flex items-center gap-4">
                        <input id="edit_max_active_orders" type="range" min="1" max="8" step="1" wire:model.live="edit_max_active_orders"
                               class="w-full cursor-pointer accent-teal-600"/>
                        <span class="w-24 shrink-0 text-right text-sm font-medium tabular-nums text-slate-700">{{ $edit_max_active_orders }} pedido(s)</span>
                    </div>
                    @error('edit_max_active_orders') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                </div>
            </form>

            <x-slot:footer>
                <x-ui.button variant="secondary" @click="$wire.set('showEditModal', false)">Cancelar</x-ui.button>
                <x-ui.button type="submit" form="driver-edit-form">Guardar cambios</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif
</div>
