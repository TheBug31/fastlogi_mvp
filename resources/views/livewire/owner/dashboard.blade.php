<div class="flex flex-col w-full pb-16" wire:poll.15s>
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-space-md mb-space-lg">
        <div>
            <h1 class="font-headline-xl text-headline-xl text-on-surface">Panel de Operaciones</h1>
            <p class="font-body-md text-on-surface-variant">
                Resumen de {{ auth()->user()?->business?->name ?? 'tu negocio' }} · {{ now()->translatedFormat('d \d\e F, Y') }}
            </p>
        </div>
        <div class="flex items-center gap-space-sm">
            <a href="{{ route('owner.orders.create') }}" wire:navigate
               class="bg-primary text-on-primary px-space-md py-space-sm font-label-lg rounded-lg flex items-center gap-space-xs hover:opacity-90 transition-opacity">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Nuevo pedido
            </a>
            <a href="{{ route('owner.orders.index') }}" wire:navigate
               class="border border-outline-variant px-space-md py-space-sm font-label-lg rounded-lg flex items-center gap-space-xs hover:bg-surface-container transition-colors">
                <span class="material-symbols-outlined text-[18px]">list</span>
                Ver pedidos
            </a>
        </div>
    </div>

    @if ($kpis['stockBajo'] > 0)
        <a href="{{ route('owner.inventory.index') }}" wire:navigate
           class="w-full bg-error-container text-on-error-container px-space-lg py-space-sm flex items-center gap-space-sm mb-space-md rounded-lg">
            <span class="material-symbols-outlined text-[20px]">warning</span>
            <span class="font-body-md">
                Tienes {{ $kpis['stockBajo'] }} producto(s) por debajo del stock mínimo. Reabastece para no perder ventas.
            </span>
        </a>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-space-md mb-space-lg">
        @foreach ([
            ['Ventas de hoy', '$ ' . number_format($kpis['ventasHoy'], 0, ',', '.'), 'payments', 'text-primary'],
            ['Pedidos activos', $kpis['activos'], 'local_shipping', 'text-tertiary'],
            ['Entregados hoy', $kpis['entregadosHoy'], 'task_alt', 'text-primary'],
            ['Repartidores disponibles', $kpis['disponibles'], 'two_wheeler', 'text-tertiary'],
        ] as [$label, $value, $icon, $tone])
            <div class="p-space-lg bg-surface-container-lowest border border-outline-variant rounded-xl flex items-center justify-between">
                <div>
                    <div class="font-label-md text-on-surface-variant uppercase">{{ $label }}</div>
                    <div class="font-headline-lg text-headline-lg text-on-surface font-data-mono">{{ $value }}</div>
                </div>
                <span class="material-symbols-outlined text-[28px] {{ $tone }}">{{ $icon }}</span>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-space-md">
        <!-- Active orders -->
        <div class="xl:col-span-2 bg-surface-container-lowest border border-outline-variant rounded-xl flex flex-col">
            <div class="px-space-lg py-space-md border-b border-outline-variant flex items-center justify-between">
                <h2 class="font-headline-sm text-headline-sm text-on-surface">Pedidos en curso</h2>
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Actualización automática</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="font-label-md text-on-surface-variant uppercase border-b border-outline-variant">
                            <th class="px-space-lg py-space-sm font-medium">Pedido</th>
                            <th class="px-space-sm py-space-sm font-medium">Cliente</th>
                            <th class="px-space-sm py-space-sm font-medium">Estado</th>
                            <th class="px-space-sm py-space-sm font-medium">Repartidor</th>
                            <th class="px-space-lg py-space-sm font-medium text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @forelse ($activeOrders as $order)
                            @php
                                $badge = match ($order->status) {
                                    \App\Enums\OrderStatus::Pending => 'bg-[#FEF3C7] text-[#B45309]',
                                    \App\Enums\OrderStatus::Offered => 'bg-[#DBEAFE] text-[#1D4ED8]',
                                    \App\Enums\OrderStatus::Assigned => 'bg-[#EDE9FE] text-[#6D28D9]',
                                    \App\Enums\OrderStatus::InTransit => 'bg-[#CCFBF1] text-[#0F766E]',
                                    default => 'bg-surface-container-high text-on-surface-variant',
                                };
                            @endphp
                            <tr wire:key="order-{{ $order->id }}" class="font-body-md">
                                <td class="px-space-lg py-space-sm">
                                    <a href="{{ route('owner.orders.show', $order->id) }}" wire:navigate class="text-primary hover:underline font-data-mono">
                                        #{{ str_pad((string) $order->id, 4, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>
                                <td class="px-space-sm py-space-sm text-on-surface">{{ $order->customer_name }}</td>
                                <td class="px-space-sm py-space-sm">
                                    <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm {{ $badge }}">{{ $order->status->label() }}</span>
                                </td>
                                <td class="px-space-sm py-space-sm text-on-surface-variant">
                                    {{ $order->deliveryDriver?->user?->name ?? 'Sin asignar' }}
                                </td>
                                <td class="px-space-lg py-space-sm text-right font-data-mono text-on-surface">
                                    $ {{ number_format($order->total, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-space-lg py-space-xl text-center font-body-md text-on-surface-variant">
                                    No hay pedidos en curso. Registra uno nuevo para comenzar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Fleet -->
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl flex flex-col">
            <div class="px-space-lg py-space-md border-b border-outline-variant flex items-center justify-between">
                <h2 class="font-headline-sm text-headline-sm text-on-surface">Flota</h2>
                <a href="{{ route('owner.drivers.index') }}" wire:navigate class="font-label-sm text-label-sm text-primary hover:underline">Gestionar</a>
            </div>
            <div class="divide-y divide-outline-variant max-h-96 overflow-y-auto">
                @forelse ($drivers as $driver)
                    @php
                        $tone = match ($driver->availability) {
                            \App\Enums\DriverAvailability::Available => 'bg-primary-container',
                            \App\Enums\DriverAvailability::Busy => 'bg-[#FEF3C7]',
                            \App\Enums\DriverAvailability::Offline => 'bg-outline-variant',
                        };
                    @endphp
                    <div wire:key="driver-{{ $driver->id }}" class="flex items-center justify-between px-space-lg py-space-sm">
                        <div class="flex items-center gap-space-sm min-w-0">
                            <span class="w-2.5 h-2.5 rounded-full {{ $tone }} shrink-0"></span>
                            <div class="min-w-0">
                                <div class="font-body-md text-on-surface truncate">{{ $driver->user?->name }}</div>
                                <div class="font-label-sm text-label-sm text-on-surface-variant">
                                    {{ $driver->availability->label() }} · {{ $driver->active_orders_count }}/{{ $driver->max_active_orders }}
                                </div>
                            </div>
                        </div>
                        <div class="font-label-sm text-label-sm text-on-surface-variant text-right shrink-0">
                            {{ $driver->last_seen_at ? $driver->last_seen_at->diffForHumans() : 'Sin señal' }}
                        </div>
                    </div>
                @empty
                    <div class="px-space-lg py-space-xl text-center font-body-md text-on-surface-variant">
                        Aún no tienes repartidores vinculados.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Timeline -->
    <div class="mt-space-md bg-surface-container-lowest border border-outline-variant rounded-xl">
        <div class="px-space-lg py-space-md border-b border-outline-variant">
            <h2 class="font-headline-sm text-headline-sm text-on-surface">Actividad reciente</h2>
        </div>
        <ol class="p-space-lg space-y-space-md">
            @forelse ($timeline as $log)
                <li wire:key="log-{{ $log->id }}" class="flex items-start gap-space-sm">
                    <span class="mt-1 w-2.5 h-2.5 rounded-full bg-primary-container shrink-0"></span>
                    <div>
                        <div class="font-body-md text-on-surface">
                            <span class="font-data-mono">#{{ str_pad((string) $log->order_id, 4, '0', STR_PAD_LEFT) }}</span>
                            <span class="font-label-md uppercase text-primary">{{ $log->from }} → {{ $log->to }}</span>
                        </div>
                        <div class="font-label-sm text-label-sm text-on-surface-variant">
                            {{ $log->created_at->format('d/m H:i') }}
                            · {{ $log->user?->name ?? 'Sistema' }}
                            @if ($log->reason) · {{ $log->reason }} @endif
                        </div>
                    </div>
                </li>
            @empty
                <li class="font-body-md text-on-surface-variant">Sin movimientos registrados todavía.</li>
            @endforelse
        </ol>
    </div>
</div>
