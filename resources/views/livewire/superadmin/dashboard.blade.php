<div class="space-y-6">
    <x-ui.page-header title="Negocios"
                      description="Supervisión de comercios afiliados, control de estados y ventana de gracia de 24 horas.">
        <x-slot:actions>
            <x-ui.button type="button" wire:click="openCreateModal">
                <x-icon name="plus" class="h-5 w-5" />
                Nuevo negocio
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.kpi label="Negocios activos" icon="building-storefront">
            {{ $activeBusinesses }}
            <x-slot:trend>de {{ $totalBusinesses }} registrados</x-slot:trend>
        </x-ui.kpi>
        <x-ui.kpi label="Negocios suspendidos" icon="no-symbol">
            {{ $suspendedBusinesses }}
            <x-slot:trend>Acceso del dueño bloqueado</x-slot:trend>
        </x-ui.kpi>
        <x-ui.kpi label="Pedidos hoy" icon="truck">
            {{ $ordersToday }}
            <x-slot:trend>Totales creados hoy</x-slot:trend>
        </x-ui.kpi>
        <x-ui.kpi label="En ventana de gracia" icon="clock">
            {{ $graceOrders }}
            <x-slot:trend>Pedidos asignados o en tránsito</x-slot:trend>
        </x-ui.kpi>
    </div>

    <x-ui.card padding="none">
        <div class="flex flex-col gap-5 p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="inline-flex flex-wrap gap-1 rounded-lg bg-slate-100 p-1">
                    <button
                        type="button"
                        wire:click="$set('filter', 'todos')"
                        class="rounded-lg px-4 py-1.5 text-sm font-medium transition-colors {{ $filter === 'todos' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                    >
                        Todos ({{ $totalBusinesses }})
                    </button>
                    <button
                        type="button"
                        wire:click="$set('filter', 'activos')"
                        class="rounded-lg px-4 py-1.5 text-sm font-medium transition-colors {{ $filter === 'activos' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                    >
                        Activos ({{ $activeBusinesses }})
                    </button>
                    <button
                        type="button"
                        wire:click="$set('filter', 'suspendidos')"
                        class="rounded-lg px-4 py-1.5 text-sm font-medium transition-colors {{ $filter === 'suspendidos' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                    >
                        Suspendidos ({{ $suspendedBusinesses }})
                    </button>
                </div>

                <div class="relative w-full sm:w-80">
                    <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3.5 top-3 h-5 w-5 text-slate-400" />
                    <input
                        type="search"
                        placeholder="Buscar por nombre, dueño o teléfono..."
                        aria-label="Buscar negocio"
                        wire:model.live.debounce.250ms="search"
                        class="h-11 w-full rounded-lg border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20"
                    />
                </div>
            </div>

            <div class="divide-y divide-slate-200 lg:hidden">
                @forelse ($businesses as $business)
                    @php
                        $owner = $business->users->first();
                        $suspended = $business->isSuspended();
                        $grace = $business->grace_orders_count;
                        $hoursLeft = $suspended && $business->suspended_at
                            ? (int) ceil(now()->diffInHours($business->suspended_at->copy()->addHours(24), false))
                            : null;
                    @endphp
                    <article wire:key="business-card-{{ $business->id }}" class="space-y-4 bg-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-teal-50 text-sm font-semibold uppercase text-teal-700" aria-hidden="true">{{ mb_strtoupper(mb_substr($business->name, 0, 1)) }}</span>
                                <div class="min-w-0">
                                    <h2 class="break-words font-semibold text-slate-900">{{ $business->name }}</h2>
                                    <p class="break-words text-xs text-slate-500">{{ $business->address }}</p>
                                </div>
                            </div>
                            @if ($suspended)<x-ui.badge color="red">Suspendido</x-ui.badge>@else<x-ui.badge color="green">Activo</x-ui.badge>@endif
                        </div>
                        <dl class="grid grid-cols-2 gap-3 rounded-lg bg-slate-50 p-3 text-sm">
                            <div><dt class="text-xs text-slate-500">Dueño</dt><dd class="mt-0.5 break-words text-slate-800">{{ $owner?->name ?? '—' }}</dd></div>
                            <div><dt class="text-xs text-slate-500">Teléfono</dt><dd class="mt-0.5 break-all font-mono text-slate-800">{{ $business->phone }}</dd></div>
                            <div class="col-span-2"><dt class="text-xs text-slate-500">Pedidos en gracia</dt><dd class="mt-0.5 text-slate-800">{{ $grace }} pedido(s)@if ($hoursLeft !== null && $grace > 0) · {{ $hoursLeft > 0 ? 'vence en '.$hoursLeft.' h' : 'plazo vencido' }}@endif</dd></div>
                        </dl>
                        <div class="flex flex-wrap gap-2">
                            @if ($suspended)
                                <x-ui.button variant="secondary" size="sm" type="button" wire:click="reactivate({{ $business->id }})"><x-icon name="arrow-path" class="h-4 w-4" />Reactivar</x-ui.button>
                            @else
                                <x-ui.button variant="danger" size="sm" type="button" wire:click="openSuspendModal({{ $business->id }})"><x-icon name="no-symbol" class="h-4 w-4" />Suspender</x-ui.button>
                            @endif
                            @if ($suspended && $grace > 0)
                                <x-ui.button variant="secondary" size="sm" type="button" wire:click="closeGrace({{ $business->id }})" wire:confirm="¿Cerrar ahora los pedidos en ventana de gracia de este negocio?"><x-icon name="clock" class="h-4 w-4" />Cerrar gracia</x-ui.button>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="px-4 py-10 text-center text-sm text-slate-500">No se encontraron negocios con los filtros aplicados.</p>
                @endforelse
            </div>

            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <x-ui.table.th>Negocio</x-ui.table.th>
                            <x-ui.table.th>Dueño / Representante</x-ui.table.th>
                            <x-ui.table.th>Teléfono</x-ui.table.th>
                            <x-ui.table.th>Estado</x-ui.table.th>
                            <x-ui.table.th>Pedidos en gracia</x-ui.table.th>
                            <x-ui.table.th align="right">Acciones</x-ui.table.th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
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
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-teal-50 text-sm font-semibold uppercase text-teal-700" aria-hidden="true">
                                            {{ mb_strtoupper(mb_substr($business->name, 0, 1)) }}
                                        </span>
                                        <div class="min-w-0">
                                            <span class="block font-semibold text-slate-900">{{ $business->name }}</span>
                                            <span class="block max-w-[220px] whitespace-normal break-words text-xs text-slate-500">{{ $business->address }}</span>
                                        </div>
                                    </div>
                                </x-ui.table.td>
                                <x-ui.table.td>{{ $owner?->name ?? '—' }}</x-ui.table.td>
                                <x-ui.table.td>
                                    <span class="font-mono text-sm text-slate-600">{{ $business->phone }}</span>
                                </x-ui.table.td>
                                <x-ui.table.td>
                                    @if ($suspended)
                                        <x-ui.badge color="red">Suspendido</x-ui.badge>
                                    @else
                                        <x-ui.badge color="green">Activo</x-ui.badge>
                                    @endif
                                </x-ui.table.td>
                                <x-ui.table.td>
                                    @if ($suspended && $grace > 0)
                                        <div class="flex flex-wrap items-center gap-2">
                                            <x-ui.badge color="amber">
                                                <x-icon name="clock" class="h-3.5 w-3.5" />
                                                {{ $grace }} pedido(s) ·
                                                @if ($hoursLeft !== null && $hoursLeft > 0)
                                                    vence en {{ $hoursLeft }} h
                                                @else
                                                    plazo vencido
                                                @endif
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
                                        <span class="text-sm text-slate-600">{{ $grace }} pedido(s)</span>
                                    @endif
                                </x-ui.table.td>
                                <x-ui.table.td align="right">
                                    <div class="inline-flex items-center justify-end gap-2">
                                        @if ($suspended)
                                            <x-ui.button variant="secondary" size="sm" type="button"
                                                         wire:click="reactivate({{ $business->id }})">
                                                <x-icon name="arrow-path" class="h-4 w-4" />
                                                Reactivar
                                            </x-ui.button>
                                        @else
                                            <x-ui.button variant="danger" size="sm" type="button"
                                                         wire:click="openSuspendModal({{ $business->id }})">
                                                <x-icon name="no-symbol" class="h-4 w-4" />
                                                Suspender
                                            </x-ui.button>
                                        @endif
                                    </div>
                                </x-ui.table.td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-14 text-center text-sm text-slate-500">
                                    No se encontraron negocios con los filtros aplicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $businesses->links('partials.pagination', ['label' => 'negocios']) }}
        </div>
    </x-ui.card>

    <!-- Modal: Nuevo negocio -->
    @if ($showCreateModal)
        <x-ui.modal property="showCreateModal" title="Crear nuevo negocio">
        <form id="business-create-form" wire:submit="createBusiness" class="flex flex-col gap-5">
            <p class="text-sm text-slate-600">
                Al registrar el comercio se crea su tenant aislado y las credenciales de acceso del dueño.
            </p>

            <x-ui.input name="name" label="Nombre del negocio" placeholder="Ej. Distribuidora Los Andes"
                        wire:model="name" required />

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-ui.input name="phone" type="tel" label="Teléfono" placeholder="310 000 0000"
                            wire:model="phone" required />
                <x-ui.input name="address" label="Dirección" placeholder="Calle 100 # 15-20"
                            wire:model="address" required />
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-ui.input name="owner_name" label="Nombre del dueño" placeholder="Nombre y apellido"
                            wire:model="owner_name" required />
                <x-ui.input name="owner_email" type="email" label="Correo electrónico"
                            placeholder="contacto@negocio.com" wire:model="owner_email" required />
            </div>

            <div x-data="{ show: false }">
                <x-ui.input name="password" label="Contraseña inicial" placeholder="Mínimo 8 caracteres"
                            ::type="show ? 'text' : 'password'" wire:model="password"
                            help="Debe contener al menos 8 caracteres." autocomplete="new-password" required>
                    <x-slot:suffix>
                        <button
                            type="button"
                            @click="show = ! show"
                            :aria-label="show ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                            class="flex items-center rounded text-slate-400 transition-colors hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600"
                        >
                            <x-icon name="eye" x-show="!show" />
                            <x-icon name="eye-slash" x-show="show" x-cloak />
                        </button>
                    </x-slot:suffix>
                </x-ui.input>
            </div>
        </form>

        <x-slot:footer>
            <x-ui.button variant="secondary" type="button" @click="$wire.set('showCreateModal', false)">
                Cancelar
            </x-ui.button>
            <x-ui.button type="submit" form="business-create-form">
                Crear negocio
            </x-ui.button>
        </x-slot:footer>
        </x-ui.modal>
    @endif

    <!-- Modal: Confirmar suspensión -->
    @if ($showSuspendModal)
        <x-ui.modal property="showSuspendModal" title="Confirmar suspensión">
        <div class="flex flex-col gap-5">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600" aria-hidden="true">
                    <x-icon name="exclamation-triangle" class="h-6 w-6" />
                </span>
                <div class="min-w-0">
                    <h3 class="text-base font-semibold text-slate-900">
                        ¿Confirmas la suspensión de {{ $suspendTargetName }}?
                    </h3>
                    <p class="mt-1 text-sm text-slate-600">
                        La plataforma activará los mecanismos de contingencia.
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-2 rounded-lg bg-slate-50 p-4">
                <span class="text-xs font-bold uppercase tracking-wide text-red-600">Al suspender este negocio:</span>
                <ul class="list-inside list-disc space-y-1 text-sm text-slate-700">
                    <li>El dueño queda bloqueado de inmediato.</li>
                    <li>Las ofertas pendientes se cancelan y liberan su reserva de stock.</li>
                    <li>Los pedidos pendientes quedan congelados.</li>
                    <li>Se activa una <strong>ventana de gracia de 24 horas</strong> para los pedidos asignados o en tránsito.</li>
                </ul>
            </div>

            <label class="flex cursor-pointer select-none items-start gap-3 rounded-lg bg-slate-50 p-3">
                <input
                    type="checkbox"
                    wire:model="suspendConfirmed"
                    class="mt-0.5 h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-0"
                />
                <span class="text-sm font-medium leading-tight text-slate-900">
                    Entiendo las implicaciones operativas de la ventana de gracia de 24 horas
                </span>
            </label>
            @error('suspendConfirmed')
                <p class="text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <x-slot:footer>
            <x-ui.button variant="secondary" type="button" @click="$wire.set('showSuspendModal', false)">
                Cancelar
            </x-ui.button>
            <x-ui.button variant="danger-solid" type="button" wire:click="suspend">
                <x-icon name="lock-closed" class="h-4 w-4" />
                Confirmar suspensión
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
    @endif
</div>
