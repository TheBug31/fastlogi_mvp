<div class="flex flex-col w-full gap-space-lg">
    @if (session('success'))
        <div class="flex items-start gap-space-sm p-space-sm bg-primary-fixed text-on-primary-fixed rounded-lg">
            <span class="material-symbols-outlined text-[20px] shrink-0">check_circle</span>
            <p class="font-body-sm text-body-sm">{{ session('success') }}</p>
        </div>
    @endif

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
            <button class="inline-flex items-center gap-space-xs px-space-lg py-space-sm rounded-lg bg-primary text-on-primary font-label-lg text-label-lg shadow-sm hover:opacity-95 transition-all"
                    type="button" wire:click="openCreateModal">
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
                    <span class="font-display-lg text-display-lg text-on-surface tracking-tight">{{ $activeBusinesses }}</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-primary-fixed flex items-center justify-center text-on-primary-fixed">
                    <span class="material-symbols-outlined text-[22px]">storefront</span>
                </div>
            </div>
            <div class="flex items-center gap-space-xs mt-space-md">
                <span class="font-body-sm text-body-sm text-on-surface-variant">de {{ $totalBusinesses }} registrados en la plataforma</span>
            </div>
        </div>
        <!-- KPI 2 -->
        <div class="flex flex-col justify-between p-space-md bg-surface-container-lowest rounded-xl shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between gap-space-sm">
                <div class="flex flex-col">
                    <span class="font-label-md text-label-md text-on-surface-variant uppercase">Negocios Suspendidos</span>
                    <span class="font-display-lg text-display-lg text-error tracking-tight">{{ $suspendedBusinesses }}</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-error-container flex items-center justify-center text-on-error-container">
                    <span class="material-symbols-outlined text-[22px]">domain_disabled</span>
                </div>
            </div>
            <div class="flex items-center gap-space-xs mt-space-md">
                <span class="font-body-sm text-body-sm text-on-surface-variant">Acceso del dueño bloqueado</span>
            </div>
        </div>
        <!-- KPI 3 -->
        <div class="flex flex-col justify-between p-space-md bg-surface-container-lowest rounded-xl shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between gap-space-sm">
                <div class="flex flex-col">
                    <span class="font-label-md text-label-md text-on-surface-variant uppercase">Pedidos Hoy</span>
                    <span class="font-display-lg text-display-lg text-on-surface tracking-tight">{{ $ordersToday }}</span>
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
                    <span class="font-display-lg text-display-lg text-tertiary tracking-tight">{{ $graceOrders }}</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-tertiary-fixed flex items-center justify-center text-on-tertiary-fixed">
                    <span class="material-symbols-outlined text-[22px]">hourglass_top</span>
                </div>
            </div>
            <div class="flex items-center gap-space-xs mt-space-md">
                <span class="font-body-sm text-body-sm text-on-surface-variant">Pedidos assigned / in_transit</span>
            </div>
        </div>
    </div>

    <!-- Main Content Card: Filtering Toolbar + Table -->
    <div class="flex flex-col bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
        <!-- Toolbar Area -->
        <div class="p-space-lg flex flex-col gap-space-md bg-surface-container-low">
            <div class="flex flex-wrap items-center justify-between gap-space-md">
                <div class="inline-flex p-1 bg-surface-container rounded-lg gap-space-xs" id="status-filter-pills">
                    <button class="px-space-md py-1.5 rounded-lg font-label-md text-label-md transition-colors {{ $filter === 'todos' ? 'bg-surface-container-lowest text-on-surface shadow-sm' : 'text-on-surface-variant hover:text-on-surface' }}"
                            type="button" wire:click="$set('filter', 'todos')">
                        Todos ({{ $totalBusinesses }})
                    </button>
                    <button class="px-space-md py-1.5 rounded-lg font-label-md text-label-md transition-colors {{ $filter === 'activos' ? 'bg-surface-container-lowest text-on-surface shadow-sm' : 'text-on-surface-variant hover:text-on-surface' }}"
                            type="button" wire:click="$set('filter', 'activos')">
                        Activos ({{ $activeBusinesses }})
                    </button>
                    <button class="px-space-md py-1.5 rounded-lg font-label-md text-label-md transition-colors {{ $filter === 'suspendidos' ? 'bg-surface-container-lowest text-on-surface shadow-sm' : 'text-on-surface-variant hover:text-on-surface' }}"
                            type="button" wire:click="$set('filter', 'suspendidos')">
                        Suspendidos ({{ $suspendedBusinesses }})
                    </button>
                </div>
            </div>
            <div class="relative w-full">
                <span class="material-symbols-outlined text-[20px] text-on-surface-variant absolute left-space-md top-2.5 pointer-events-none">search</span>
                <input class="w-full h-11 pl-11 pr-space-md bg-surface-container-lowest text-on-surface placeholder:text-outline rounded-lg font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20"
                       type="text" placeholder="Buscar negocio por nombre, dueño o teléfono..."
                       wire:model.live.debounce.400ms="search"/>
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
                        <tr class="hover:bg-surface-container-low/50 transition-colors tenant-row" wire:key="business-{{ $business->id }}">
                            <td class="py-space-md px-space-lg">
                                <div class="flex items-center gap-space-sm">
                                    <div class="w-9 h-9 rounded-lg bg-surface-container-highest text-primary flex items-center justify-center font-display-lg text-[14px] font-bold uppercase">
                                        {{ mb_substr($business->name, 0, 2) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-label-lg text-label-lg text-on-surface">{{ $business->name }}</span>
                                        <span class="font-body-sm text-body-sm text-on-surface-variant">{{ $business->address }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md text-on-surface">{{ $owner?->name ?? '—' }}</td>
                            <td class="py-space-md px-space-md font-data-mono text-data-mono text-on-surface">{{ $business->phone }}</td>
                            <td class="py-space-md px-space-md">
                                @if ($suspended)
                                    <span class="inline-flex items-center gap-1 px-space-sm py-0.5 rounded-full bg-error-container text-on-error-container font-label-sm text-label-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-error"></span>
                                        Suspendido
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-space-sm py-0.5 rounded-full bg-primary-fixed text-on-primary-fixed font-label-sm text-label-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                        Activo
                                    </span>
                                @endif
                            </td>
                            <td class="py-space-md px-space-md">
                                @if ($suspended && $grace > 0)
                                    <div class="flex items-center gap-space-xs flex-wrap">
                                        <span class="inline-flex items-center gap-1 px-space-sm py-1 rounded-full bg-tertiary-fixed text-on-tertiary-container font-label-sm text-label-sm font-semibold">
                                            <span class="material-symbols-outlined text-[14px]">timer</span>
                                            {{ $grace }} pedido(s) · @if ($hoursLeft !== null && $hoursLeft > 0) vence en {{ $hoursLeft }} h @else plazo vencido @endif
                                        </span>
                                        <button class="px-space-xs py-0.5 rounded bg-surface-container text-on-tertiary-container hover:bg-tertiary-fixed font-label-sm text-label-sm transition-colors"
                                                type="button" title="Forzar cierre de ventana de gracia"
                                                wire:click="closeGrace({{ $business->id }})"
                                                wire:confirm="¿Cerrar ahora los pedidos en ventana de gracia de este negocio?">
                                            Cerrar gracia
                                        </button>
                                    </div>
                                @else
                                    <span class="text-on-surface-variant font-body-sm">{{ $grace }} pedido(s) activos</span>
                                @endif
                            </td>
                            <td class="py-space-md px-space-lg text-right">
                                <div class="inline-flex items-center gap-space-xs">
                                    @if ($suspended)
                                        <button class="inline-flex items-center gap-1 px-space-sm py-1 rounded-lg bg-primary-container text-on-primary-container font-label-sm text-label-sm hover:opacity-90 transition-opacity"
                                                type="button" wire:click="reactivate({{ $business->id }})">
                                            <span class="material-symbols-outlined text-[14px]">replay</span>
                                            <span>Reactivar</span>
                                        </button>
                                    @else
                                        <button class="inline-flex items-center gap-1 px-space-sm py-1 rounded-lg bg-surface-container text-error font-label-sm text-label-sm hover:bg-error hover:text-on-error transition-colors"
                                                type="button" wire:click="openSuspendModal({{ $business->id }})">
                                            <span class="material-symbols-outlined text-[14px]">block</span>
                                            <span>Suspender</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="py-space-lg px-space-lg text-center text-on-surface-variant" colspan="6">
                                No se encontraron negocios con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer / Pagination -->
        <div class="p-space-md flex flex-col sm:flex-row items-center justify-between gap-space-sm bg-surface-container-low text-body-sm text-on-surface-variant">
            <span>Mostrando {{ $businesses->firstItem() ?? 0 }} a {{ $businesses->lastItem() ?? 0 }} de {{ $businesses->total() }} comercios afiliados</span>
            <div class="flex items-center gap-space-xs">
                <button class="px-space-sm py-1 rounded bg-surface-container-lowest text-on-surface hover:bg-surface-container font-label-sm text-label-sm shadow-sm disabled:opacity-50"
                        type="button" wire:click="previousPage" @disabled($businesses->onFirstPage())>Anterior</button>
                <span class="px-space-sm font-data-mono text-data-mono">Página {{ $businesses->currentPage() }} de {{ max($businesses->lastPage(), 1) }}</span>
                <button class="px-space-sm py-1 rounded bg-surface-container-lowest text-on-surface hover:bg-surface-container font-label-sm text-label-sm shadow-sm disabled:opacity-50"
                        type="button" wire:click="nextPage" @disabled(! $businesses->hasMorePages())>Siguiente</button>
            </div>
        </div>
    </div>

    <!-- Modal 1: Nuevo Negocio -->
    @if ($showCreateModal)
        <div aria-modal="true" role="dialog" class="fixed inset-0 z-50 flex items-center justify-center p-space-md bg-black/40 backdrop-blur-sm">
            <div class="flex flex-col w-full max-w-xl bg-surface-container-lowest rounded-xl shadow-xl overflow-hidden">
                <div class="p-space-md bg-surface-container-low flex items-center justify-between">
                    <div class="flex items-center gap-space-xs text-primary">
                        <span class="material-symbols-outlined text-[22px]">add_business</span>
                        <span class="font-headline-sm text-headline-sm text-on-surface">Crear Nuevo Negocio en FastLogi</span>
                    </div>
                    <button class="w-8 h-8 rounded-lg flex items-center justify-center text-on-surface-variant hover:bg-surface-container" type="button" wire:click="closeCreateModal">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
                <form class="p-space-lg flex flex-col gap-space-md max-h-[75vh] overflow-y-auto" wire:submit="createBusiness">
                    <p class="font-body-sm text-body-sm text-on-surface-variant">
                        Al registrar el comercio se creará su tenant aislado y sus credenciales de acceso para la sede principal.
                    </p>
                    <div class="flex flex-col gap-space-xs">
                        <label class="font-label-md text-label-md text-on-surface">Nombre del Negocio *</label>
                        <input class="h-10 px-space-md bg-surface-container-low rounded-lg text-on-surface font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary"
                               type="text" placeholder="Ej. Distribuidora Los Alpes" wire:model="name"/>
                        @error('name') <span class="text-error font-body-sm text-body-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                        <div class="flex flex-col gap-space-xs">
                            <label class="font-label-md text-label-md text-on-surface">Teléfono *</label>
                            <input class="h-10 px-space-md bg-surface-container-low rounded-lg text-on-surface font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary"
                                   type="tel" placeholder="310 000 0000" wire:model="phone"/>
                            @error('phone') <span class="text-error font-body-sm text-body-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-space-xs">
                            <label class="font-label-md text-label-md text-on-surface">Dirección *</label>
                            <input class="h-10 px-space-md bg-surface-container-low rounded-lg text-on-surface font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary"
                                   type="text" placeholder="Calle 100 # 15-20" wire:model="address"/>
                            @error('address') <span class="text-error font-body-sm text-body-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                        <div class="flex flex-col gap-space-xs">
                            <label class="font-label-md text-label-md text-on-surface">Nombre del Dueño *</label>
                            <input class="h-10 px-space-md bg-surface-container-low rounded-lg text-on-surface font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary"
                                   type="text" placeholder="Nombre y apellido" wire:model="owner_name"/>
                            @error('owner_name') <span class="text-error font-body-sm text-body-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-space-xs">
                            <label class="font-label-md text-label-md text-on-surface">Correo Electrónico *</label>
                            <input class="h-10 px-space-md bg-surface-container-low rounded-lg text-on-surface font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary"
                                   type="email" placeholder="contacto@negocio.com" wire:model="owner_email"/>
                            @error('owner_email') <span class="text-error font-body-sm text-body-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="flex flex-col gap-space-xs">
                        <label class="font-label-md text-label-md text-on-surface">Contraseña Inicial *</label>
                        <div class="relative" x-data="{ show: false }">
                            <input class="w-full h-10 px-space-md pr-10 bg-surface-container-low rounded-lg text-on-surface font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary"
                                   :type="show ? 'text' : 'password'" placeholder="Mínimo 8 caracteres" wire:model="password"/>
                            <button class="absolute right-2.5 top-2.5 text-on-surface-variant hover:text-on-surface" type="button" x-on:click="show = ! show">
                                <span class="material-symbols-outlined text-[18px]" x-text="show ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                        @error('password') <span class="text-error font-body-sm text-body-sm">{{ $message }}</span> @enderror
                        <span class="font-body-sm text-body-sm text-on-surface-variant">Debe contener al menos 8 caracteres.</span>
                    </div>
                    <div class="flex items-center justify-end gap-space-sm pt-space-sm">
                        <button class="px-space-md py-space-sm rounded-lg bg-surface-container text-on-surface font-label-md text-label-md hover:bg-surface-container-high transition-colors" type="button" wire:click="closeCreateModal">
                            Cancelar
                        </button>
                        <button class="px-space-lg py-space-sm rounded-lg bg-primary text-on-primary font-label-md text-label-md shadow-sm hover:opacity-90 transition-opacity" type="submit">
                            Crear Negocio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal 2: Confirmar Suspensión -->
    @if ($showSuspendModal)
        <div aria-modal="true" role="dialog" class="fixed inset-0 z-50 flex items-center justify-center p-space-md bg-black/40 backdrop-blur-sm">
            <div class="flex flex-col w-full max-w-lg bg-surface-container-lowest rounded-xl shadow-xl overflow-hidden">
                <div class="p-space-md bg-surface-container-low flex items-center justify-between">
                    <div class="flex items-center gap-space-xs text-error">
                        <span class="material-symbols-outlined text-[20px]">warning</span>
                        <span class="font-headline-sm text-headline-sm text-on-surface">Confirmar Suspensión</span>
                    </div>
                    <button class="w-8 h-8 rounded-lg flex items-center justify-center text-on-surface-variant hover:bg-surface-container" type="button" wire:click="closeSuspendModal">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
                <div class="p-space-lg flex flex-col gap-space-md">
                    <div class="flex items-start gap-space-sm">
                        <div class="w-10 h-10 rounded-lg bg-error-container text-error flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-[24px]">front_hand</span>
                        </div>
                        <div class="flex flex-col">
                            <h3 class="font-headline-sm text-headline-sm text-on-surface">¿Confirmas la suspensión de {{ $suspendTargetName }}?</h3>
                            <p class="font-body-sm text-body-sm text-on-surface-variant">La plataforma activará los mecanismos de contingencia.</p>
                        </div>
                    </div>
                    <div class="p-space-md rounded-lg bg-surface-container-low flex flex-col gap-space-xs text-body-sm text-on-surface">
                        <span class="font-label-sm text-label-sm uppercase text-error font-bold">Al suspender este negocio:</span>
                        <ul class="list-disc list-inside space-y-1 text-on-surface-variant font-body-sm">
                            <li>El dueño queda bloqueado de inmediato.</li>
                            <li>Las ofertas pendientes se cancelan y liberan su reserva de stock.</li>
                            <li>Los pedidos pendientes quedan congelados.</li>
                            <li>Se activa una <strong>ventana de gracia de 24 horas</strong> para los pedidos asignados o en tránsito.</li>
                        </ul>
                    </div>
                    <label class="flex items-start gap-space-sm p-space-sm rounded-lg bg-surface-container cursor-pointer select-none">
                        <input class="mt-0.5 h-4 w-4 rounded text-primary focus:ring-0" type="checkbox" wire:model="suspendConfirmed"/>
                        <span class="font-body-sm text-body-sm text-on-surface font-medium leading-tight">
                            Entiendo las implicaciones operativas de la ventana de gracia de 24 horas
                        </span>
                    </label>
                    @error('suspendConfirmed') <span class="text-error font-body-sm text-body-sm">{{ $message }}</span> @enderror
                </div>
                <div class="p-space-md bg-surface-container-low flex items-center justify-end gap-space-sm">
                    <button class="px-space-md py-space-sm rounded-lg bg-surface-container text-on-surface font-label-md text-label-md hover:bg-surface-container-high transition-colors" type="button" wire:click="closeSuspendModal">
                        Cancelar
                    </button>
                    <button class="inline-flex items-center gap-1 px-space-md py-space-sm rounded-lg bg-error text-on-error font-label-md text-label-md shadow-sm hover:opacity-90 transition-opacity"
                            type="button" wire:click="suspend">
                        <span class="material-symbols-outlined text-[16px]">lock_clock</span>
                        <span>Confirmar Suspensión (Iniciar gracia 24h)</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
