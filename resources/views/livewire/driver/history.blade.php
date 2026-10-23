<div class="space-y-6">
    <x-ui.page-header title="Historial de entregas"
                      description="Tus pedidos finalizados: entregas, cancelaciones e incidencias." />

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.kpi label="Entregadas" icon="check-circle">{{ $kpis['entregadas'] }}</x-ui.kpi>
        <x-ui.kpi label="Entregadas hoy" icon="truck">{{ $kpis['hoy'] }}</x-ui.kpi>
        <x-ui.kpi label="Incidencias" icon="exclamation-triangle">{{ $kpis['incidencias'] }}</x-ui.kpi>
        <x-ui.kpi label="Canceladas" icon="x-circle">{{ $kpis['canceladas'] }}</x-ui.kpi>
    </div>

    <x-ui.card>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-ui.input name="desde" type="date" label="Desde" wire:model.live="desde" />
            <x-ui.input name="hasta" type="date" label="Hasta" wire:model.live="hasta" />

            <x-ui.select name="status" label="Estado" wire:model.live="status">
                <option value="todos">Todos</option>
                @foreach ($statuses as $status)
                    @if ($status->isFinal())
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endif
                @endforeach
            </x-ui.select>
        </div>

        <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-slate-200 pt-5">
            <x-ui.button variant="secondary" wire:click="clearFilters">
                <x-icon name="arrow-uturn-left" class="h-5 w-5" />
                Limpiar filtros
            </x-ui.button>
        </div>

        @if (! $validRange)
            <x-ui.alert type="error" class="mt-4">
                La fecha final debe ser igual o posterior a la inicial.
            </x-ui.alert>
        @endif
    </x-ui.card>

    @if ($validRange)
        @if ($orders->isEmpty())
            <x-ui.empty-state icon="clock" title="No hay entregas en este periodo."
                              description="Prueba con otro rango de fechas o quita el filtro de estado." />
        @else
            <x-ui.card padding="none">
                <div class="divide-y divide-slate-200 lg:hidden">
                    @foreach ($orders as $order)
                        <a wire:key="history-card-{{ $order->id }}" href="{{ route('driver.orders.show', $order) }}" wire:navigate
                           class="block space-y-3 p-4 transition-colors hover:bg-slate-50">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-semibold text-teal-700">#{{ str_pad((string) $order->id, 4, '0', STR_PAD_LEFT) }}</span>
                                <x-ui.status-badge :status="$order->status" />
                            </div>
                            <div>
                                <p class="font-medium text-slate-900">{{ $order->customer_name }}</p>
                                <p class="mt-0.5 break-words text-sm text-slate-600">{{ $order->delivery_address }}</p>
                            </div>
                            <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-3 text-xs text-slate-500">
                                <span>{{ format_datetime($order->updated_at) }}</span>
                                <span class="font-semibold tabular-nums text-slate-900">{{ format_cop($order->total) }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="hidden overflow-x-auto lg:block">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="border-b border-slate-200 bg-slate-50">
                            <tr>
                                <x-ui.table.th>Pedido</x-ui.table.th>
                                <x-ui.table.th>Cliente</x-ui.table.th>
                                <x-ui.table.th>Entrega</x-ui.table.th>
                                <x-ui.table.th>Fecha</x-ui.table.th>
                                <x-ui.table.th>Estado</x-ui.table.th>
                                <x-ui.table.th align="right">Total</x-ui.table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($orders as $order)
                                <tr wire:key="history-{{ $order->id }}" class="hover:bg-slate-50/70">
                                    <x-ui.table.td>
                                        <a href="{{ route('driver.orders.show', $order) }}" wire:navigate
                                           class="font-semibold text-teal-700 hover:underline">
                                            #{{ str_pad((string) $order->id, 4, '0', STR_PAD_LEFT) }}
                                        </a>
                                    </x-ui.table.td>
                                    <x-ui.table.td>{{ $order->customer_name }}</x-ui.table.td>
                                    <x-ui.table.td>
                                        <span class="block max-w-[240px] whitespace-normal break-words text-slate-600">{{ $order->delivery_address }}</span>
                                    </x-ui.table.td>
                                    <x-ui.table.td>
                                        <span class="text-xs text-slate-500">{{ format_datetime($order->updated_at) }}</span>
                                    </x-ui.table.td>
                                    <x-ui.table.td><x-ui.status-badge :status="$order->status" /></x-ui.table.td>
                                    <x-ui.table.td align="right">
                                        <span class="font-semibold tabular-nums text-slate-900">{{ format_cop($order->total) }}</span>
                                    </x-ui.table.td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $orders->links('partials.pagination', ['label' => 'entregas']) }}
            </x-ui.card>
        @endif
    @endif
</div>
