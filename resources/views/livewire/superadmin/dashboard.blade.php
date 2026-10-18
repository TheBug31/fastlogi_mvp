<div class="flex flex-col gap-space-lg">

    @if (session('success'))
        <x-ui.alert variant="success" class="flex items-start gap-space-sm">
            <x-icon name="check-circle" class="h-5 w-5 shrink-0" />
            <p class="font-body-sm text-body-sm">{{ session('success') }}</p>
        </x-ui.alert>
    @endif

    <!-- Header -->
    <div class="flex flex-col gap-space-md rounded-xl bg-white p-space-lg shadow-sm">
        <div class="flex flex-col items-start justify-between gap-space-md md:flex-row md:items-end">
            <div class="flex flex-col gap-space-xs">
                <div class="flex items-center gap-space-xs">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-teal-600"></span>
                    <span class="font-label-sm text-label-sm uppercase tracking-widest text-teal-700">
                        Portal SuperAdmin · Multi-Tenant
                    </span>
                </div>
                <h1 class="font-headline-xl text-headline-xl tracking-tight text-slate-900">
                    Gestión de Negocios y Plataforma
                </h1>
                <p class="max-w-3xl font-body-md text-body-md text-slate-600">
                    Supervisión de comercios afiliados, control de estados y auditoría de ventanas de gracia operativas.
                </p>
            </div>
            <div class="flex shrink-0 items-center gap-space-sm">
                <x-ui.button type="button" wire:click="openCreateModal">
                    <x-icon name="plus" class="h-4 w-4" />
                    Nuevo Negocio
                </x-ui.button>
            </div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 gap-space-md sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.kpi label="Negocios Activos" icon="building-storefront">
            {{ $activeBusinesses }}
            <x-slot:trend>de {{ $totalBusinesses }} registrados en la plataforma</x-slot:trend>
        </x-ui.kpi>
        <x-ui.kpi label="Negocios Suspendidos" icon="no-symbol">
            {{ $suspendedBusinesses }}
            <x-slot:trend>Acceso del dueño bloqueado</x-slot:trend>
        </x-ui.kpi>
        <x-ui.kpi label="Pedidos Hoy" icon="truck">
            {{ $ordersToday }}
            <x-slot:trend>Totales creados hoy</x-slot:trend>
        </x-ui.kpi>
        <x-ui.kpi label="En Ventana de Gracia" icon="clock">
            {{ $graceOrders }}
            <x-slot:trend>Pedidos assigned / in_transit</x-slot:trend>
        </x-ui.kpi>
    </div>

    <!-- Tabla de negocios -->
    <x-ui.card>
        <div class="flex flex-col gap-space-md">
            <!-- Filtros -->
            <div class="flex flex-wrap items-center justify-between gap-space-md">
                <div class="inline-flex gap-space-xs rounded-lg bg-slate-100 p-1" id="status-filter-pills">
                    <button
                        type="button"
                        wire:click="$set('filter', 'todos')"
                        class="rounded-lg px-space-md py-1.5 font-label-md text-label-md transition-colors {{ $filter === 'todos' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                    >
                        Todos ({{ $totalBusinesses }})
                    </button>
                    <button
                        type="button"
                        wire:click="$set('filter', 'activos')"
                        class="rounded-lg px-space-md py-1.5 font-label-md text-label-md transition-colors {{ $filter === 'activos' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                    >
                        Activos ({{ $activeBusinesses }})
                    </button>
                    <button
                        type="button"
                        wire:click="$set('filter', 'suspendidos')"
                        class="rounded-lg px-space-md py-1.5 font-label-md text-label-md transition-colors {{ $filter === 'suspendidos' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                    >
                        Suspendidos ({{ $suspendedBusinesses }})
                    </button>
                </div>
            </div>

            <!-- Búsqueda -->
            <div class="relative w-full">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute left-space-md top-2.5 h-5 w-5 text-slate-500" />
                <input
                    type="text"
                    placeholder="Buscar negocio por nombre, dueño o teléfono..."
                    wire:model.live.debounce.400ms="search"
                    class="h-11 w-full rounded-lg border border-slate-200 bg-white pl-11 pr-space-md font-body-md text-body-md text-slate-900 placeholder:text-slate-400 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20"
                />
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600">
                            <x-ui.table.th>Nombre del Negocio</x-ui.table.th>
                            <x-ui.table.th>Dueño / Representante</x-ui.table.th>
                            <x-ui.table.th>Teléfono</x-ui.table.th>
                            <x-ui.table.th>Estado</x-ui.table.th>
                            <x-ui.table.th>Pedidos en Gracia</x-ui.table.th>
                            <x-ui.table.th align="right">Acciones</x-ui.table.th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-900">
                        @forelse ($businesses as $business)
                            @php
                                $owner = $business->users->first();
                                $suspended = $business->isSuspended();
                                $grace = $business->grace_orders_count;
                                $hoursLeft = null;
                                if ($suspended && $business->suspended_at) {
                                    $hoursLeft = (int) ceil(now()->diffInHours($business->suspended_at->copy()->addHours(24), false));
                                }
                            @endphp
                            <tr class="transition-colors hover:bg-slate-50/60" wire:key="business-{{ $business->id }}">
                                <x-ui.table.td>
                                    <div class="flex items-center gap-space-sm">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-50 text-sm font-bold uppercase text-teal-700">
                                            {{ mb_substr($business->name, 0, 2) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-label-lg text-label-lg text-slate-900">{{ $business->name }}</span>
                                            <span class="font-body-sm text-body-sm text-slate-600">{{ $business->address }}</span>
                                        </div>
                                    </div>
                                </x-ui.table.td>
                                <x-ui.table.td>{{ $owner?->name ?? '—' }}</x-ui.table.td>
                                <x-ui.table.td class="font-mono text-sm text-slate-900">{{ $business->phone }}</x-ui.table.td>
                                <x-ui.table.td>
                                    @if ($suspended)
                                        <x-ui.badge color="red">
                                            Suspendido
                                        </x-ui.badge>
                                    @else
                                        <x-ui.badge color="green">
                                            Activo
                                        </x-ui.badge>
                                    @endif
                                </x-ui.table.td>
                                <x-ui.table.td>
                                    @if ($suspended && $grace > 0)
                                        <div class="flex flex-wrap items-center gap-space-xs">
                                            <x-ui.badge color="amber">
                                                <x-icon name="clock" class="h-3.5 w-3.5" />
                                                {{ $grace }} pedido(s) · @if ($hoursLeft !== null && $hoursLeft > 0) vence en {{ $hoursLeft }} h @else plazo vencido @endif
                                            </x-ui.badge>
                                            <x-ui.button
                                                variant="secondary"
                                                size="sm"
                                                type="button"
                                                wire:click="closeGrace({{ $business->id }})"
                                                wire:confirm="¿Cerrar ahora los pedidos en ventana de gracia de este negocio?"
                                            >
                                                Cerrar gracia
                                            </x-ui.button>
                                        </div>
                                    @else
                                        <span class="font-body-sm text-body-sm text-slate-600">{{ $grace }} pedido(s)</span>
                                    @endif
                                </x-ui.table.td>
                                <x-ui.table.td align="right">
                                    <div class="inline-flex items-center gap-space-xs">
                                        @if ($suspended)
                                            <x-ui.button
                                                variant="secondary"
                                                size="sm"
                                                type="button"
                                                wire:click="reactivate({{ $business->id }})"
                                            >
                                                <x-icon name="arrow-path" class="h-4 w-4" />
                                                Reactivar
                                            </x-ui.button>
                                        @else
                                            <x-ui.button
                                                variant="danger"
                                                size="sm"
                                                type="button"
                                                wire:click="openSuspendModal({{ $business->id }})"
                                            >
                                                <x-icon name="no-symbol" class="h-4 w-4" />
                                                Suspender
                                            </x-ui.button>
                                        @endif
                                    </div>
                                </x-ui.table.td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-space-lg py-space-lg text-center text-slate-600" colspan="6">
                                    No se encontraron negocios con los filtros aplicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @include('partials.pagination', ['paginator' => $businesses, 'label' => 'negocios'])
        </div>
    </x-ui.card>

    <!-- Modal: Nuevo Negocio -->
    @if ($showCreateModal)
        <x-ui.modal property="showCreateModal" title="Crear Nuevo Negocio">
            <div class="flex flex-col gap-space-md">
                <p class="font-body-sm text-body-sm text-slate-600">
                    Al registrar el comercio se creará su tenant aislado y sus credenciales de acceso para la sede principal.
                </p>

                <div class="flex flex-col gap-space-xs">
                    <label class="font-label-md text-label-md text-slate-900">Nombre del Negocio *</label>
                    <input
                        type="text"
                        placeholder="Ej. Distribuidora Los Alpes"
                        wire:model="name"
                        class="h-10 w-full rounded-lg border border-slate-200 bg-white px-space-md font-body-sm text-body-sm text-slate-900 placeholder:text-slate-400 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20"
                    />
                    @error('name')<span class="font-body-sm text-body-sm text-red-600">{{ $message }}</span>@enderror
                </div>

                <div class="grid grid-cols-1 gap-space-md md:grid-cols-2">
                    <div class="flex flex-col gap-space-xs">
                        <label class="font-label-md text-label-md text-slate-900">Teléfono *</label>
                        <input
                            type="tel"
                            placeholder="310 000 0000"
                            wire:model="phone"
                            class="h-10 w-full rounded-lg border border-slate-200 bg-white px-space-md font-body-sm text-body-sm text-slate-900 placeholder:text-slate-400 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20"
                        />
                        @error('phone')<span class="font-body-sm text-body-sm text-red-600">{{ $message }}</span>@enderror
                    </div>
                    <div class="flex flex-col gap-space-xs">
                        <label class="font-label-md text-label-md text-slate-900">Dirección *</label>
                        <input
                            type="text"
                            placeholder="Calle 100 # 15-20"
                            wire:model="address"
                            class="h-10 w-full rounded-lg border border-slate-200 bg-white px-space-md font-body-sm text-body-sm text-slate-900 placeholder:text-slate-400 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20"
                        />
                        @error('address')<span class="font-body-sm text-body-sm text-red-600">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-space-md md:grid-cols-2">
                    <div class="flex flex-col gap-space-xs">
                        <label class="font-label-md text-label-md text-slate-900">Nombre del Dueño *</label>
                        <input
                            type="text"
                            placeholder="Nombre y apellido"
                            wire:model="owner_name"
                            class="h-10 w-full rounded-lg border border-slate-200 bg-white px-space-md font-body-sm text-body-sm text-slate-900 placeholder:text-slate-400 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20"
                        />
                        @error('owner_name')<span class="font-body-sm text-body-sm text-red-600">{{ $message }}</span>@enderror
                    </div>
                    <div class="flex flex-col gap-space-xs">
                        <label class="font-label-md text-label-md text-slate-900">Correo Electrónico *</label>
                        <input
                            type="email"
                            placeholder="contacto@negocio.com"
                            wire:model="owner_email"
                            class="h-10 w-full rounded-lg border border-slate-200 bg-white px-space-md font-body-sm text-body-sm text-slate-900 placeholder:text-slate-400 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20"
                        />
                        @error('owner_email')<span class="font-body-sm text-body-sm text-red-600">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="flex flex-col gap-space-xs">
                    <label class="font-label-md text-label-md text-slate-900">Contraseña Inicial *</label>
                    <div class="relative" x-data="{ show: false }">
                        <input
                            :type="show ? 'text' : 'password'"
                            placeholder="Mínimo 8 caracteres"
                            wire:model="password"
                            class="h-10 w-full rounded-lg border border-slate-200 bg-white px-space-md pr-10 font-body-sm text-body-sm text-slate-900 placeholder:text-slate-400 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20"
                        />
                        <button
                            type="button"
                            x-on:click="show = ! show"
                            class="absolute right-2.5 top-2.5 text-slate-500 hover:text-slate-900"
                        >
                            <x-icon name="eye" class="h-4 w-4" x-show="!show" />
                            <x-icon name="eye-slash" class="h-4 w-4" x-show="show" />
                        </button>
                    </div>
                    @error('password')<span class="font-body-sm text-body-sm text-red-600">{{ $message }}</span>@enderror
                    <span class="font-body-sm text-body-sm text-slate-600">Debe contener al menos 8 caracteres.</span>
                </div>
            </div>

            <x-slot:footer>
                <div class="flex items-center justify-end gap-space-sm">
                    <x-ui.button variant="secondary" type="button" wire:click="closeCreateModal">
                        Cancelar
                    </x-ui.button>
                    <x-ui.button type="submit" wire:click="createBusiness">
                        Crear Negocio
                    </x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    <!-- Modal: Confirmar Suspensión -->
    @if ($showSuspendModal)
        <x-ui.modal property="showSuspendModal" title="Confirmar Suspensión">
            <div class="flex flex-col gap-space-md">
                <div class="flex items-start gap-space-sm">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600">
                        <x-icon name="exclamation-triangle" class="h-6 w-6" />
                    </div>
                    <div class="flex flex-col">
                        <h3 class="font-headline-sm text-headline-sm text-slate-900">
                            ¿Confirmas la suspensión de {{ $suspendTargetName }}?
                        </h3>
                        <p class="font-body-sm text-body-sm text-slate-600">
                            La plataforma activará los mecanismos de contingencia.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-space-xs rounded-lg bg-slate-50 p-space-md text-slate-900">
                    <span class="font-label-sm text-label-sm font-bold uppercase text-red-600">Al suspender este negocio:</span>
                    <ul class="list-inside list-disc space-y-1 font-body-sm text-body-sm text-slate-700">
                        <li>El dueño queda bloqueado de inmediato.</li>
                        <li>Las ofertas pendientes se cancelan y liberan su reserva de stock.</li>
                        <li>Los pedidos pendientes quedan congelados.</li>
                        <li>Se activa una <strong>ventana de gracia de 24 horas</strong> para los pedidos asignados o en tránsito.</li>
                    </ul>
                </div>

                <label class="flex cursor-pointer select-none items-start gap-space-sm rounded-lg bg-slate-50 p-space-sm">
                    <input
                        type="checkbox"
                        wire:model="suspendConfirmed"
                        class="mt-0.5 h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-0"
                    />
                    <span class="font-body-sm text-body-sm font-medium leading-tight text-slate-900">
                        Entiendo las implicaciones operativas de la ventana de gracia de 24 horas
                    </span>
                </label>
                @error('suspendConfirmed')<span class="font-body-sm text-body-sm text-red-600">{{ $message }}</span>@enderror
            </div>

            <x-slot:footer>
                <div class="flex items-center justify-end gap-space-sm">
                    <x-ui.button variant="secondary" type="button" wire:click="closeSuspendModal">
                        Cancelar
                    </x-ui.button>
                    <x-ui.button variant="danger-solid" type="button" wire:click="suspend">
                        <x-icon name="lock-closed" class="h-4 w-4" />
                        Confirmar Suspensión (Iniciar gracia 24h)
                    </x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.modal>
    @endif

</div>