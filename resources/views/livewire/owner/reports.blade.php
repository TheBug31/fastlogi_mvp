<div class="flex flex-col w-full pb-16">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-space-md mb-space-lg">
        <div>
            <h1 class="font-headline-xl text-headline-xl text-on-surface">Reportes</h1>
            <p class="font-body-md text-on-surface-variant">Genera reportes de pedidos y repartidores en PDF con filtros</p>
        </div>
    </div>

    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-space-lg mb-space-md">
        <h2 class="font-headline-sm text-headline-sm text-on-surface mb-space-md">Filtros</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-space-md">
            <div class="flex flex-col gap-space-xs">
                <label class="font-label-md text-on-surface-variant uppercase">Desde</label>
                <input type="date" wire:model.live="desde"
                       class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md rounded-lg focus:outline-none focus:border-on-surface"/>
            </div>
            <div class="flex flex-col gap-space-xs">
                <label class="font-label-md text-on-surface-variant uppercase">Hasta</label>
                <input type="date" wire:model.live="hasta"
                       class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md rounded-lg focus:outline-none focus:border-on-surface"/>
            </div>
            <div class="flex flex-col gap-space-xs">
                <label class="font-label-md text-on-surface-variant uppercase">Estado</label>
                <select wire:model.live="estado"
                        class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md rounded-lg focus:outline-none focus:border-on-surface">
                    <option value="todos">Todos</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col gap-space-xs">
                <label class="font-label-md text-on-surface-variant uppercase">Repartidor</label>
                <select wire:model.live="driver_id"
                        class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md rounded-lg focus:outline-none focus:border-on-surface">
                    <option value="">Todos</option>
                    @foreach ($driverOptions as $driver)
                        <option value="{{ $driver->id }}">{{ $driver->user?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col gap-space-xs">
                <label class="font-label-md text-on-surface-variant uppercase">Método de pago</label>
                <select wire:model.live="payment_method"
                        class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md rounded-lg focus:outline-none focus:border-on-surface">
                    <option value="todos">Todos</option>
                    @foreach ($paymentMethods as $method)
                        <option value="{{ $method }}">{{ $method }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-space-sm mt-space-md">
            <button type="button" wire:click="resetFilters"
                    class="border border-outline-variant px-space-md py-space-sm font-label-lg rounded-lg hover:bg-surface-container transition-colors">
                Limpiar filtros
            </button>

            @if ($validRange)
                <a href="{{ route('owner.reports.orders', array_filter($filters, fn ($value) => $value !== null && $value !== 'todos')) }}"
                   class="bg-primary text-on-primary px-space-md py-space-sm font-label-lg rounded-lg flex items-center gap-space-xs hover:opacity-90 transition-opacity">
                    <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                    Descargar pedidos (PDF)
                </a>
                <a href="{{ route('owner.reports.drivers', ['desde' => $filters['desde'], 'hasta' => $filters['hasta']]) }}"
                   class="border border-outline-variant px-space-md py-space-sm font-label-lg rounded-lg flex items-center gap-space-xs hover:bg-surface-container transition-colors">
                    <span class="material-symbols-outlined text-[18px]">badge</span>
                    Descargar repartidores (PDF)
                </a>
            @else
                <span class="font-body-sm text-error flex items-center gap-space-xs">
                    <span class="material-symbols-outlined text-[18px]">error</span>
                    La fecha final debe ser igual o posterior a la inicial.
                </span>
            @endif
        </div>
    </div>

    @if ($validRange)
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-space-md mb-space-md">
            <div class="p-space-md bg-surface-container-lowest border border-outline-variant rounded-xl">
                <p class="font-label-md text-label-md text-on-surface-variant uppercase">Pedidos en el periodo</p>
                <h3 class="font-headline-lg text-headline-lg text-on-surface font-data-mono">{{ $orders->count() }}</h3>
            </div>
            <div class="p-space-md bg-surface-container-lowest border border-outline-variant rounded-xl">
                <p class="font-label-md text-label-md text-on-surface-variant uppercase">Valor total</p>
                <h3 class="font-headline-lg text-headline-lg text-on-surface font-data-mono">$ {{ number_format($orderTotal, 0, ',', '.') }}</h3>
            </div>
            <div class="p-space-md bg-surface-container-lowest border border-outline-variant rounded-xl">
                <p class="font-label-md text-label-md text-on-surface-variant uppercase">Repartidores</p>
                <h3 class="font-headline-lg text-headline-lg text-on-surface font-data-mono">{{ $drivers->count() }}</h3>
            </div>
        </div>

        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
            <div class="px-space-lg py-space-md border-b border-outline-variant flex items-center justify-between">
                <h2 class="font-headline-sm text-headline-sm text-on-surface">Vista previa de pedidos</h2>
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Primeros 10 registros</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="font-label-md text-on-surface-variant uppercase border-b border-outline-variant">
                            <th class="px-space-lg py-space-sm font-medium">#</th>
                            <th class="px-space-sm py-space-sm font-medium">Fecha</th>
                            <th class="px-space-sm py-space-sm font-medium">Cliente</th>
                            <th class="px-space-sm py-space-sm font-medium">Estado</th>
                            <th class="px-space-sm py-space-sm font-medium">Repartidor</th>
                            <th class="px-space-lg py-space-sm font-medium text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @forelse ($orders->take(10) as $order)
                            <tr wire:key="report-order-{{ $order->id }}" class="font-body-md">
                                <td class="px-space-lg py-space-sm font-data-mono">{{ str_pad((string) $order->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-space-sm py-space-sm text-on-surface-variant">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-space-sm py-space-sm text-on-surface">{{ $order->customer_name }}</td>
                                <td class="px-space-sm py-space-sm">{{ $order->status->label() }}</td>
                                <td class="px-space-sm py-space-sm text-on-surface-variant">{{ $order->deliveryDriver?->user?->name ?? '—' }}</td>
                                <td class="px-space-lg py-space-sm text-right font-data-mono">$ {{ number_format($order->total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-space-lg py-space-xl text-center font-body-md text-on-surface-variant">
                                    No hay pedidos para los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
