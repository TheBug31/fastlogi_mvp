<div class="flex flex-col w-full pb-16">
    @if (session('success'))
        <div class="w-full bg-primary-fixed text-on-primary-fixed px-space-lg py-space-sm flex items-center gap-space-sm mb-space-md rounded-lg">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span class="font-body-md">{{ session('success') }}</span>
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-space-md mb-space-lg">
        <div>
            <h1 class="font-headline-xl text-headline-xl text-on-surface">Gestión de Repartidores</h1>
            <p class="font-body-md text-on-surface-variant">Supervisión de la flota, disponibilidad y límites de carga</p>
        </div>
        <button type="button" wire:click="openCreateModal"
                class="px-space-md py-space-sm bg-primary text-on-primary font-label-lg rounded-lg flex items-center gap-space-xs hover:opacity-90 transition-opacity">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Nuevo repartidor
        </button>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-space-md mb-space-lg">
        @foreach ([
            ['Total flota', $kpis['total'], 'two_wheeler'],
            ['Disponibles', $kpis['disponibles'], 'check_circle'],
            ['Ocupados', $kpis['ocupados'], 'pending_actions'],
            ['Desconectados', $kpis['offline'], 'wifi_off'],
        ] as [$label, $value, $icon])
            <div class="p-space-md bg-surface-container-lowest border border-outline-variant rounded-xl flex items-center justify-between">
                <div>
                    <p class="font-label-md text-label-md text-on-surface-variant uppercase">{{ $label }}</p>
                    <h3 class="font-headline-lg text-headline-lg text-on-surface font-data-mono">{{ $value }}</h3>
                </div>
                <span class="material-symbols-outlined text-[26px] text-primary">{{ $icon }}</span>
            </div>
        @endforeach
    </div>

    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-space-md mb-space-md flex flex-wrap items-center justify-between gap-space-md">
        <div class="flex items-center gap-space-sm flex-1 min-w-[260px]">
            <span class="material-symbols-outlined text-outline">search</span>
            <input class="w-full bg-transparent border-none text-on-surface placeholder:text-outline focus:outline-none font-body-md"
                   placeholder="Buscar por nombre, correo o teléfono..."
                   type="text" wire:model.live.debounce.400ms="search"/>
        </div>
        <select class="bg-surface-container-low border border-outline-variant px-space-sm py-space-xs font-body-sm text-on-surface rounded-lg focus:outline-none"
                wire:model.live="availabilityFilter">
            <option value="todos">Disponibilidad: Todas</option>
            @foreach ($availabilities as $availability)
                <option value="{{ $availability->value }}">{{ $availability->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="font-label-md text-on-surface-variant uppercase border-b border-outline-variant">
                    <th class="px-space-lg py-space-sm font-medium">Repartidor</th>
                    <th class="px-space-sm py-space-sm font-medium">Contacto</th>
                    <th class="px-space-sm py-space-sm font-medium">Disponibilidad</th>
                    <th class="px-space-sm py-space-sm font-medium">Capacidad</th>
                    <th class="px-space-sm py-space-sm font-medium">Última señal</th>
                    <th class="px-space-lg py-space-sm font-medium text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @forelse ($drivers as $driver)
                    @php
                        $badge = match ($driver->availability) {
                            \App\Enums\DriverAvailability::Available => 'bg-[#DCFCE7] text-[#15803D]',
                            \App\Enums\DriverAvailability::Busy => 'bg-[#FEF3C7] text-[#B45309]',
                            \App\Enums\DriverAvailability::Offline => 'bg-surface-container-high text-on-surface-variant',
                        };
                    @endphp
                    <tr wire:key="driver-{{ $driver->id }}" class="font-body-md">
                        <td class="px-space-lg py-space-sm">
                            <div class="font-medium text-on-surface">{{ $driver->user?->name }}</div>
                            <div class="font-label-sm text-label-sm text-on-surface-variant">Repartidor #{{ $driver->id }}</div>
                        </td>
                        <td class="px-space-sm py-space-sm text-on-surface-variant">
                            <div>{{ $driver->user?->email }}</div>
                            <div class="font-label-sm text-label-sm">{{ $driver->user?->phone }}</div>
                        </td>
                        <td class="px-space-sm py-space-sm">
                            <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm {{ $badge }}">
                                {{ $driver->availability->label() }}
                            </span>
                        </td>
                        <td class="px-space-sm py-space-sm">
                            <span class="font-data-mono text-on-surface">{{ $driver->active_orders_count }}</span>
                            <span class="text-on-surface-variant">/ {{ $driver->max_active_orders }}</span>
                        </td>
                        <td class="px-space-sm py-space-sm text-on-surface-variant">
                            {{ $driver->last_seen_at ? $driver->last_seen_at->diffForHumans() : 'Sin señal' }}
                        </td>
                        <td class="px-space-lg py-space-sm">
                            <div class="flex items-center justify-end gap-space-xs">
                                <button type="button" wire:click="openEditModal({{ $driver->id }})"
                                        class="p-1.5 text-on-surface-variant hover:text-on-surface hover:bg-surface-container rounded-lg" title="Editar">
                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                </button>
                                <button type="button" wire:click="toggleAvailability({{ $driver->id }})"
                                        class="p-1.5 rounded-lg {{ $driver->availability === \App\Enums\DriverAvailability::Offline ? 'text-primary hover:bg-surface-container' : 'text-tertiary hover:bg-surface-container' }}"
                                        title="{{ $driver->availability === \App\Enums\DriverAvailability::Offline ? 'Marcar disponible' : 'Marcar desconectado' }}">
                                    <span class="material-symbols-outlined text-[20px]">
                                        {{ $driver->availability === \App\Enums\DriverAvailability::Offline ? 'power_settings_new' : 'power_off' }}
                                    </span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-space-lg py-space-xl text-center font-body-md text-on-surface-variant">
                            No hay repartidores que coincidan con el filtro.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-space-md">
        {{ $drivers->links() }}
    </div>

    <!-- MODAL: Nuevo repartidor -->
    @if ($showCreateModal)
        <div class="fixed inset-0 bg-[#0B1C30]/60 backdrop-blur-sm z-50 flex items-center justify-center p-space-md" role="dialog" aria-modal="true">
            <div class="bg-surface-container-lowest border border-outline-variant w-full max-w-lg shadow-2xl overflow-hidden">
                <div class="bg-surface-container-low px-space-lg py-space-md border-b border-outline-variant flex items-center justify-between">
                    <h3 class="font-headline-sm text-headline-sm text-on-surface">Nuevo repartidor</h3>
                    <button class="text-on-surface-variant hover:text-on-surface" type="button" wire:click="$set('showCreateModal', false)">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form class="p-space-lg space-y-space-md" wire:submit="createDriver">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
                        <div class="flex flex-col gap-space-xs">
                            <label class="font-label-md text-on-surface-variant uppercase">Nombre</label>
                            <input class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md rounded-lg focus:outline-none focus:border-on-surface"
                                   type="text" wire:model="name"/>
                            @error('name') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-space-xs">
                            <label class="font-label-md text-on-surface-variant uppercase">Teléfono</label>
                            <input class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md rounded-lg focus:outline-none focus:border-on-surface"
                                   type="text" wire:model="phone"/>
                            @error('phone') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-space-xs sm:col-span-2">
                            <label class="font-label-md text-on-surface-variant uppercase">Correo</label>
                            <input class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md rounded-lg focus:outline-none focus:border-on-surface"
                                   type="email" wire:model="email"/>
                            @error('email') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-space-xs">
                            <label class="font-label-md text-on-surface-variant uppercase">Contraseña</label>
                            <input class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md rounded-lg focus:outline-none focus:border-on-surface"
                                   type="password" wire:model="password" autocomplete="new-password"/>
                            @error('password') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-space-xs">
                            <label class="font-label-md text-on-surface-variant uppercase">Límite de pedidos activos</label>
                            <select class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md rounded-lg focus:outline-none focus:border-on-surface"
                                    wire:model="max_active_orders">
                                @for ($i = 1; $i <= 8; $i++)
                                    <option value="{{ $i }}">{{ $i }} pedido(s)</option>
                                @endfor
                            </select>
                            @error('max_active_orders') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-space-sm">
                        <button class="border border-outline-variant px-space-md py-space-sm font-label-lg text-on-surface rounded-lg"
                                type="button" wire:click="$set('showCreateModal', false)">Cancelar</button>
                        <button class="bg-primary text-on-primary px-space-lg py-space-sm font-label-lg rounded-lg" type="submit">Vincular repartidor</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL: Editar repartidor -->
    @if ($showEditModal)
        <div class="fixed inset-0 bg-[#0B1C30]/60 backdrop-blur-sm z-50 flex items-center justify-center p-space-md" role="dialog" aria-modal="true">
            <div class="bg-surface-container-lowest border border-outline-variant w-full max-w-lg shadow-2xl overflow-hidden">
                <div class="bg-surface-container-low px-space-lg py-space-md border-b border-outline-variant flex items-center justify-between">
                    <h3 class="font-headline-sm text-headline-sm text-on-surface">Editar repartidor</h3>
                    <button class="text-on-surface-variant hover:text-on-surface" type="button" wire:click="$set('showEditModal', false)">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form class="p-space-lg space-y-space-md" wire:submit="updateDriver">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
                        <div class="flex flex-col gap-space-xs">
                            <label class="font-label-md text-on-surface-variant uppercase">Nombre</label>
                            <input class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md rounded-lg focus:outline-none focus:border-on-surface"
                                   type="text" wire:model="edit_name"/>
                            @error('edit_name') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-space-xs">
                            <label class="font-label-md text-on-surface-variant uppercase">Teléfono</label>
                            <input class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md rounded-lg focus:outline-none focus:border-on-surface"
                                   type="text" wire:model="edit_phone"/>
                            @error('edit_phone') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-space-xs sm:col-span-2">
                            <label class="font-label-md text-on-surface-variant uppercase">Correo</label>
                            <input class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md rounded-lg focus:outline-none focus:border-on-surface"
                                   type="email" wire:model="edit_email"/>
                            @error('edit_email') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-space-xs sm:col-span-2">
                            <label class="font-label-md text-on-surface-variant uppercase">Límite de pedidos activos</label>
                            <select class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md rounded-lg focus:outline-none focus:border-on-surface"
                                    wire:model="edit_max_active_orders">
                                @for ($i = 1; $i <= 8; $i++)
                                    <option value="{{ $i }}">{{ $i }} pedido(s)</option>
                                @endfor
                            </select>
                            @error('edit_max_active_orders') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-space-sm">
                        <button class="border border-outline-variant px-space-md py-space-sm font-label-lg text-on-surface rounded-lg"
                                type="button" wire:click="$set('showEditModal', false)">Cancelar</button>
                        <button class="bg-primary text-on-primary px-space-lg py-space-sm font-label-lg rounded-lg" type="submit">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
