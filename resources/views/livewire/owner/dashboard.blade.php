<div class="space-y-6" wire:poll.15s>
    @php
        $orderLabel = fn ($id) => '#' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    @endphp

    <x-ui.page-header title="Panel de Operaciones"
                      :description="'Resumen de ' . (auth()->user()?->business?->name ?? 'tu negocio') . ' · ' . format_date(now())">
        <x-slot:actions>
            <x-ui.button variant="secondary" :href="route('owner.orders.index')" wire:navigate>
                <x-icon name="list-bullet" />
                Ver pedidos
            </x-ui.button>
            <x-ui.button :href="route('owner.orders.create')" wire:navigate>
                <x-icon name="plus" />
                Nuevo pedido
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.kpi label="Ventas de hoy" icon="banknotes">{{ format_cop($kpis['ventasHoy']) }}</x-ui.kpi>
        <x-ui.kpi label="Pedidos entregados" icon="check-circle">{{ $kpis['entregadosHoy'] }}</x-ui.kpi>
        <x-ui.kpi label="Tiempo promedio de entrega" icon="clock">
            {{ $kpis['promedioEntrega'] !== null ? $kpis['promedioEntrega'] . ' min' : '—' }}
        </x-ui.kpi>
        <x-ui.kpi label="Pedidos activos" icon="truck">{{ $kpis['activos'] }}</x-ui.kpi>
    </div>

    <x-ui.card padding="none">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <h2 class="text-base font-semibold text-slate-900">Pedidos en curso</h2>
            <span class="text-xs text-slate-500">Actualización automática</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <x-ui.table.th>Pedido</x-ui.table.th>
                        <x-ui.table.th>Cliente</x-ui.table.th>
                        <x-ui.table.th>Estado</x-ui.table.th>
                        <x-ui.table.th>Repartidor</x-ui.table.th>
                        <x-ui.table.th align="right">Total</x-ui.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($activeOrders as $order)
                        <tr wire:key="order-{{ $order->id }}" class="hover:bg-slate-50/70">
                            <x-ui.table.td>
                                <a href="{{ route('owner.orders.show', $order) }}" wire:navigate
                                   class="font-semibold text-teal-700 hover:underline">{{ $orderLabel($order->id) }}</a>
                            </x-ui.table.td>
                            <x-ui.table.td>{{ $order->customer_name }}</x-ui.table.td>
                            <x-ui.table.td><x-ui.status-badge :status="$order->status" /></x-ui.table.td>
                            <x-ui.table.td>
                                @if ($order->deliveryDriver?->user)
                                    <div class="flex items-center gap-2.5">
                                        <x-ui.avatar :name="$order->deliveryDriver->user->name" size="sm" />
                                        <span>{{ $order->deliveryDriver->user->name }}</span>
                                    </div>
                                @else
                                    <span class="italic text-slate-400">Sin asignar</span>
                                @endif
                            </x-ui.table.td>
                            <x-ui.table.td align="right">
                                <span class="font-semibold tabular-nums text-slate-900">{{ format_cop($order->total) }}</span>
                            </x-ui.table.td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-14 text-center text-sm text-slate-500">
                                No hay pedidos en curso. Registra uno nuevo para comenzar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-ui.card padding="none">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h2 class="text-base font-semibold text-slate-900">Flota</h2>
                <a href="{{ route('owner.drivers.index') }}" wire:navigate
                   class="text-sm font-medium text-teal-700 hover:underline">Gestionar</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($drivers as $driver)
                    <div wire:key="driver-{{ $driver->id }}" class="flex items-center justify-between gap-4 px-6 py-3.5">
                        <div class="flex min-w-0 items-center gap-3">
                            <x-ui.avatar :name="$driver->user?->name ?? ''" />
                            <div class="min-w-0">
                                <p class="font-medium text-slate-900">{{ $driver->user?->name }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $driver->last_seen_at ? $driver->last_seen_at->diffForHumans() : 'Sin señal' }}
                                </p>
                            </div>
                        </div>

                        <div class="w-40 shrink-0 space-y-1.5">
                            <x-ui.availability-badge :availability="$driver->availability" />
                            <x-ui.capacity-bar :active="$driver->active_orders_count" :max="$driver->max_active_orders" />
                        </div>
                    </div>
                @empty
                    <p class="px-6 py-10 text-center text-sm text-slate-500">Aún no tienes repartidores vinculados.</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card padding="none">
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-6 py-4">
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-slate-900">Alertas de inventario</h2>
                    @if ($kpis['stockBajo'] > 0)
                        <p class="mt-0.5 text-xs text-slate-500">
                            <span class="font-medium tabular-nums text-slate-700">{{ $kpis['stockBajo'] }}</span> producto(s) por debajo del stock mínimo
                        </p>
                    @endif
                </div>
                <a href="{{ route('owner.inventory.index') }}" wire:navigate
                   class="shrink-0 text-sm font-medium text-teal-700 hover:underline">Ver inventario</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($lowStockProducts as $product)
                    @php $out = $product->stock <= 0; @endphp
                    <div wire:key="low-{{ $product->id }}" class="flex items-center justify-between gap-4 px-6 py-3.5">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900">{{ $product->name }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Stock <span class="font-medium tabular-nums text-slate-700">{{ $product->stock }}</span> {{ $product->unit }}
                                · mínimo <span class="font-medium tabular-nums text-slate-700">{{ $product->min_stock }}</span>
                            </p>
                        </div>

                        <x-ui.badge :color="$out ? 'red' : 'amber'">{{ $out ? 'Agotado' : 'Stock bajo' }}</x-ui.badge>
                    </div>
                @empty
                    <p class="px-6 py-10 text-center text-sm text-slate-500">Todo el inventario está por encima del mínimo.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>

    <x-ui.card padding="none">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-base font-semibold text-slate-900">Actividad reciente</h2>
        </div>

        <ol class="space-y-4 p-6">
            @forelse ($timeline as $log)
                <li wire:key="log-{{ $log->id }}" class="relative border-l border-slate-200 pl-4">
                    <span class="absolute -left-[5px] top-1.5 h-2 w-2 rounded-full bg-teal-600" aria-hidden="true"></span>
                    <p class="text-sm font-medium text-slate-900">
                        {{ $orderLabel($log->order_id) }}
                        <span class="text-slate-500">
                            {{ \App\Enums\OrderStatus::tryFrom($log->from)?->label() ?? $log->from }}
                            <span class="text-slate-400" aria-hidden="true">→</span>
                            {{ \App\Enums\OrderStatus::tryFrom($log->to)?->label() ?? $log->to }}
                        </span>
                    </p>
                    <p class="mt-0.5 text-xs text-slate-500">
                        {{ format_datetime($log->created_at) }} · {{ $log->user?->name ?? 'Sistema' }}
                    </p>
                    @if ($log->reason)
                        <p class="mt-0.5 text-xs text-slate-500">{{ $log->reason }}</p>
                    @endif
                </li>
            @empty
                <li class="text-sm text-slate-500">Sin movimientos registrados todavía.</li>
            @endforelse
        </ol>
    </x-ui.card>
</div>
