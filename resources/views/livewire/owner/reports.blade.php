<div class="space-y-6">
    <x-ui.page-header title="Reportes" description="Genera reportes de pedidos y repartidores en PDF con filtros." />

    <x-ui.card>
        <h2 class="text-base font-semibold text-slate-900">Filtros</h2>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <x-ui.input name="desde" type="date" label="Desde" wire:model.live="desde" />
            <x-ui.input name="hasta" type="date" label="Hasta" wire:model.live="hasta" />

            <x-ui.select name="estado" label="Estado" wire:model.live="estado">
                <option value="todos">Todos</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select name="driver_id" label="Repartidor" wire:model.live="driver_id">
                <option value="">Todos</option>
                @foreach ($driverOptions as $driver)
                    <option value="{{ $driver->id }}">{{ $driver->user?->name }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select name="payment_method" label="Método de pago" wire:model.live="payment_method">
                <option value="todos">Todos</option>
                @foreach ($paymentMethods as $method)
                    <option value="{{ $method }}">{{ $method }}</option>
                @endforeach
            </x-ui.select>
        </div>

        <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-slate-200 pt-5">
            <x-ui.button variant="secondary" wire:click="resetFilters">
                <x-icon name="arrow-uturn-left" />
                Limpiar filtros
            </x-ui.button>

            @if ($validRange)
                <x-ui.button :href="route('owner.reports.orders', array_filter($filters, fn ($value) => $value !== null && $value !== 'todos'))">
                    <x-icon name="arrow-down-tray" />
                    Descargar pedidos (PDF)
                </x-ui.button>

                <x-ui.button variant="secondary"
                             :href="route('owner.reports.drivers', ['desde' => $filters['desde'], 'hasta' => $filters['hasta']])">
                    <x-icon name="document-text" />
                    Descargar repartidores (PDF)
                </x-ui.button>
            @endif
        </div>

        @if (! $validRange)
            <x-ui.alert type="error" class="mt-4">
                La fecha final debe ser igual o posterior a la inicial.
            </x-ui.alert>
        @endif
    </x-ui.card>

    @if ($validRange)
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <x-ui.kpi label="Pedidos en el periodo" icon="clipboard-document-list">{{ $orders->count() }}</x-ui.kpi>
            <x-ui.kpi label="Valor total" icon="banknotes">{{ format_cop($orderTotal) }}</x-ui.kpi>
            <x-ui.kpi label="Repartidores" icon="users">{{ $drivers->count() }}</x-ui.kpi>
        </div>

        <x-ui.card padding="none">
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-6 py-4">
                <h2 class="text-base font-semibold text-slate-900">Vista previa de pedidos</h2>
                <span class="text-xs text-slate-500">Primeros 10 registros</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <x-ui.table.th>Pedido</x-ui.table.th>
                            <x-ui.table.th>Fecha</x-ui.table.th>
                            <x-ui.table.th>Cliente</x-ui.table.th>
                            <x-ui.table.th>Estado</x-ui.table.th>
                            <x-ui.table.th>Repartidor</x-ui.table.th>
                            <x-ui.table.th align="right">Total</x-ui.table.th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($orders->take(10) as $order)
                            <tr wire:key="report-order-{{ $order->id }}" class="hover:bg-slate-50/70">
                                <x-ui.table.td>
                                    <a href="{{ route('owner.orders.show', $order) }}" wire:navigate
                                       class="font-semibold text-teal-700 hover:underline">#{{ str_pad((string) $order->id, 4, '0', STR_PAD_LEFT) }}</a>
                                </x-ui.table.td>
                                <x-ui.table.td>
                                    <span class="text-xs text-slate-500">{{ format_datetime($order->created_at) }}</span>
                                </x-ui.table.td>
                                <x-ui.table.td>{{ $order->customer_name }}</x-ui.table.td>
                                <x-ui.table.td><x-ui.status-badge :status="$order->status" /></x-ui.table.td>
                                <x-ui.table.td>
                                    @if ($order->deliveryDriver?->user)
                                        {{ $order->deliveryDriver->user->name }}
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
                                <td colspan="6" class="px-4 py-14 text-center text-sm text-slate-500">
                                    No hay pedidos para los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    @endif
</div>
