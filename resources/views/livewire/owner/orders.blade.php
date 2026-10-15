<div class="flex flex-col w-full pb-16">
    @if (session('success'))
        <div class="w-full bg-primary-fixed text-on-primary-fixed px-space-lg py-space-sm flex items-center gap-space-sm mb-space-md">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span class="font-body-md">{{ session('success') }}</span>
        </div>
    @endif

    @error('devolution')
        <div class="w-full bg-error-container text-on-error-container px-space-lg py-space-sm flex items-center gap-space-sm mb-space-md">
            <span class="material-symbols-outlined text-[20px]">error</span>
            <span class="font-body-md">{{ $message }}</span>
        </div>
    @enderror

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-space-md mb-space-lg">
        <div>
            <h1 class="font-headline-xl text-headline-xl text-on-surface">Gestión de Pedidos</h1>
            <p class="font-body-md text-on-surface-variant">Monitoreo en tiempo real y asignación de flotas urbanas</p>
        </div>
        <div class="flex items-center gap-space-sm">
            <button class="bg-[#14B8A6] text-[#0F172A] px-space-md py-space-sm font-label-lg flex items-center gap-space-xs hover:opacity-90 transition-opacity"
                    type="button" wire:click="openCreateModal">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Nuevo Pedido
            </button>
        </div>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-space-md mb-space-lg">
        @foreach ([
            ['Pedidos activos', $kpis['activos'], 'receipt_long'],
            ['Pendientes', $kpis['pendientes'], 'pending_actions'],
            ['En camino', $kpis['en_camino'], 'local_shipping'],
            ['Entregados', $kpis['entregados'], 'task_alt'],
        ] as [$label, $value, $icon])
            <div class="p-space-lg bg-surface-container-lowest border border-outline-variant flex items-center justify-between">
                <div>
                    <div class="font-label-md text-on-surface-variant uppercase">{{ $label }}</div>
                    <div class="font-headline-lg text-headline-lg text-on-surface font-data-mono">{{ $value }}</div>
                </div>
                <span class="material-symbols-outlined text-[28px] text-primary">{{ $icon }}</span>
            </div>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="bg-surface-container-lowest p-space-md border border-outline-variant mb-space-md flex flex-wrap items-center justify-between gap-space-md">
        <div class="flex items-center gap-space-sm flex-1 min-w-[280px]">
            <span class="material-symbols-outlined text-outline">search</span>
            <input class="w-full bg-transparent border-none text-on-surface placeholder:text-outline focus:outline-none font-body-md"
                   placeholder="Buscar por cliente, teléfono o número de pedido..." type="text"
                   wire:model.live.debounce.400ms="search"/>
        </div>
        <select class="bg-surface-container-low border border-outline-variant px-space-sm py-space-xs font-body-sm text-on-surface focus:outline-none"
                wire:model.live="statusFilter">
            <option value="todos">Estado: Todos</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>

    <!-- Orders table -->
    <div class="bg-surface-container-lowest border border-outline-variant overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-surface-container-low border-b border-outline-variant font-label-md text-on-surface-variant uppercase">
                    <th class="p-space-md">Pedido</th>
                    <th class="p-space-md">Cliente</th>
                    <th class="p-space-md">Total</th>
                    <th class="p-space-md">Repartidor</th>
                    <th class="p-space-md">Estado</th>
                    <th class="p-space-md text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant font-body-md">
                @forelse ($orders as $order)
                    @php
                        $badge = match ($order->status) {
                            \App\Enums\OrderStatus::Pending => 'bg-[#FEF3C7] text-[#B45309]',
                            \App\Enums\OrderStatus::Offered => 'bg-[#DBEAFE] text-[#1D4ED8]',
                            \App\Enums\OrderStatus::Assigned => 'bg-[#EDE9FE] text-[#6D28D9]',
                            \App\Enums\OrderStatus::InTransit => 'bg-[#CCFBF1] text-[#0F766E]',
                            \App\Enums\OrderStatus::Delivered => 'bg-[#DCFCE7] text-[#15803D]',
                            \App\Enums\OrderStatus::Incident => 'bg-[#FFDAD6] text-[#93000A]',
                            \App\Enums\OrderStatus::Cancelled => 'bg-surface-container-high text-on-surface-variant',
                        };
                    @endphp
                    <tr class="hover:bg-surface-container-low/50 transition-colors" wire:key="order-{{ $order->id }}">
                        <td class="p-space-md">
                            <div class="font-data-mono text-on-surface">#{{ str_pad((string) $order->id, 4, '0', STR_PAD_LEFT) }}</div>
                            <div class="font-body-sm text-on-surface-variant">{{ $order->created_at->format('d/m H:i') }}</div>
                        </td>
                        <td class="p-space-md">
                            <div class="font-semibold text-on-surface">{{ $order->customer_name }}</div>
                            <div class="font-body-sm text-on-surface-variant">{{ $order->customer_phone }}</div>
                        </td>
                        <td class="p-space-md font-data-mono text-on-surface">$ {{ number_format($order->total, 0, ',', '.') }}</td>
                        <td class="p-space-md text-on-surface-variant">
                            {{ $order->deliveryDriver?->user?->name ?? '—' }}
                        </td>
                        <td class="p-space-md">
                            <span class="{{ $badge }} font-label-sm px-2 py-1 uppercase">{{ $order->status->label() }}</span>
                        </td>
                        <td class="p-space-md text-right">
                            <div class="flex items-center justify-end gap-space-xs flex-wrap">
                                <button class="p-1 hover:bg-surface-container text-on-surface-variant hover:text-on-surface" title="Detalle"
                                        type="button" wire:click="openDetailModal({{ $order->id }})">
                                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                                </button>

                                @if ($order->status === \App\Enums\OrderStatus::Pending)
                                    <button class="px-2 py-1 bg-[#14B8A6] text-[#0F172A] font-label-sm uppercase"
                                            type="button" wire:click="openOfferModal({{ $order->id }})">Ofrecer</button>
                                @endif

                                @if (in_array($order->status, [\App\Enums\OrderStatus::Pending, \App\Enums\OrderStatus::Offered, \App\Enums\OrderStatus::Assigned]))
                                    <button class="px-2 py-1 border border-error text-error font-label-sm uppercase"
                                            type="button" wire:click="openCancelModal({{ $order->id }})">Cancelar</button>
                                @endif

                                @if ($order->status === \App\Enums\OrderStatus::Assigned)
                                    <button class="px-2 py-1 border border-outline-variant text-on-surface-variant font-label-sm uppercase"
                                            type="button" wire:click="openReassignModal({{ $order->id }})">Reasignar</button>
                                @endif

                                @if ($order->status === \App\Enums\OrderStatus::InTransit)
                                    <button class="px-2 py-1 bg-[#FFDAD6] text-[#93000A] font-label-sm uppercase"
                                            type="button" wire:click="openIncidentModal({{ $order->id }})">Incidencia</button>
                                @endif

                                @if ($order->status === \App\Enums\OrderStatus::Incident)
                                    <button class="px-2 py-1 bg-[#CCFBF1] text-[#0F766E] font-label-sm uppercase"
                                            type="button" wire:click="confirmDevolution({{ $order->id }})"
                                            wire:confirm="¿Confirmas la recepción del paquete devuelto? Se reintegrará el stock.">Confirmar devolución</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="p-space-lg text-center text-on-surface-variant" colspan="6">No hay pedidos que coincidan con los filtros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-space-md bg-surface-container-low border-t border-outline-variant flex items-center justify-between font-body-sm text-on-surface-variant">
            <span>Mostrando {{ $orders->firstItem() ?? 0 }} a {{ $orders->lastItem() ?? 0 }} de {{ $orders->total() }} pedidos</span>
            <div class="flex items-center gap-space-xs">
                <button class="px-space-sm py-1 border border-outline-variant bg-surface-container-lowest disabled:opacity-50"
                        type="button" wire:click="previousPage" @disabled($orders->onFirstPage())>Anterior</button>
                <span class="px-space-sm font-data-mono">Página {{ $orders->currentPage() }} de {{ max($orders->lastPage(), 1) }}</span>
                <button class="px-space-sm py-1 border border-outline-variant bg-surface-container-lowest disabled:opacity-50"
                        type="button" wire:click="nextPage" @disabled(! $orders->hasMorePages())>Siguiente</button>
            </div>
        </div>
    </div>

    <!-- MODAL: Crear pedido -->
    @if ($showCreateModal)
        <div class="fixed inset-0 bg-[#0B1C30]/60 backdrop-blur-sm z-50 flex items-center justify-center p-space-md" role="dialog" aria-modal="true">
            <div class="bg-surface-container-lowest border border-outline-variant w-full max-w-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <div class="bg-surface-container-low px-space-lg py-space-md border-b border-outline-variant flex items-center justify-between">
                    <div class="flex items-center gap-space-sm">
                        <span class="material-symbols-outlined text-primary">add_box</span>
                        <h3 class="font-headline-sm text-headline-sm text-on-surface">Nuevo Pedido</h3>
                    </div>
                    <button class="text-on-surface-variant hover:text-on-surface" type="button" wire:click="$set('showCreateModal', false)">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form class="p-space-lg space-y-space-md overflow-y-auto" wire:submit="createOrder">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                        <div class="flex flex-col gap-space-xs">
                            <label class="font-label-md text-on-surface-variant uppercase">Cliente *</label>
                            <input class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md focus:outline-none focus:border-on-surface"
                                   type="text" wire:model="customer_name"/>
                            @error('customer_name') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-space-xs">
                            <label class="font-label-md text-on-surface-variant uppercase">Teléfono *</label>
                            <input class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-data-mono focus:outline-none focus:border-on-surface"
                                   type="text" wire:model="customer_phone"/>
                            @error('customer_phone') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-space-xs md:col-span-2">
                            <label class="font-label-md text-on-surface-variant uppercase">Dirección de entrega *</label>
                            <input class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md focus:outline-none focus:border-on-surface"
                                   type="text" wire:model="delivery_address"/>
                            @error('delivery_address') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-space-xs">
                            <label class="font-label-md text-on-surface-variant uppercase">Método de pago *</label>
                            <select class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md focus:outline-none focus:border-on-surface"
                                    wire:model="payment_method">
                                <option>Contra Entrega (Efectivo)</option>
                                <option>Nequi / Daviplata</option>
                                <option>Transferencia Bancaria</option>
                            </select>
                            @error('payment_method') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                        </div>
                        <label class="flex items-center gap-space-sm cursor-pointer mt-6">
                            <input class="w-4 h-4" type="checkbox" wire:model="requires_traceability"/>
                            <span class="font-body-md text-on-surface">Requiere trazabilidad (paquete)</span>
                        </label>
                    </div>

                    <div class="border-t border-outline-variant pt-space-md">
                        <div class="flex items-center justify-between mb-space-sm">
                            <span class="font-label-md text-on-surface-variant uppercase">Productos</span>
                            <button class="text-primary font-label-md uppercase flex items-center gap-1" type="button" wire:click="addLine">
                                <span class="material-symbols-outlined text-[16px]">add</span> Agregar
                            </button>
                        </div>
                        @error('lines') <div class="font-body-sm text-error mb-space-xs">{{ $message }}</div> @enderror

                        @foreach ($lines as $index => $line)
                            <div class="flex items-center gap-space-sm mb-space-xs" wire:key="line-{{ $index }}">
                                <select class="flex-1 bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md focus:outline-none focus:border-on-surface"
                                        wire:model="lines.{{ $index }}.product_id">
                                    <option value="">Seleccione un producto...</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }} (stock: {{ $product->stock }})</option>
                                    @endforeach
                                </select>
                                <input class="w-24 bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-data-mono focus:outline-none focus:border-on-surface"
                                       type="number" min="1" wire:model="lines.{{ $index }}.quantity"/>
                                <button class="text-error p-1" type="button" wire:click="removeLine({{ $index }})">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                            @error("lines.{$index}.product_id") <div class="font-body-sm text-error mb-space-xs">{{ $message }}</div> @enderror
                            @error("lines.{$index}.quantity") <div class="font-body-sm text-error mb-space-xs">{{ $message }}</div> @enderror
                        @endforeach
                    </div>

                    <div class="pt-space-md border-t border-outline-variant flex items-center justify-end gap-space-sm">
                        <button class="bg-surface border border-outline-variant px-space-md py-space-sm font-label-lg text-on-surface hover:bg-surface-container"
                                type="button" wire:click="$set('showCreateModal', false)">Cancelar</button>
                        <button class="bg-[#14B8A6] text-[#0F172A] px-space-lg py-space-sm font-label-lg hover:opacity-90" type="submit">
                            Crear pedido y reservar stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL: Ofrecer pedido -->
    @if ($showOfferModal)
        <div class="fixed inset-0 bg-[#0B1C30]/60 backdrop-blur-sm z-50 flex items-center justify-center p-space-md" role="dialog" aria-modal="true">
            <div class="bg-surface-container-lowest border border-outline-variant w-full max-w-xl shadow-2xl overflow-hidden">
                <div class="bg-surface-container-low px-space-lg py-space-md border-b border-outline-variant flex items-center justify-between">
                    <h3 class="font-headline-sm text-headline-sm text-on-surface">Ofrecer Pedido</h3>
                    <button class="text-on-surface-variant hover:text-on-surface" type="button" wire:click="$set('showOfferModal', false)">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="p-space-lg space-y-space-sm">
                    <p class="font-body-md text-on-surface-variant">Selecciona un repartidor disponible. La oferta expira en 90 segundos.</p>
                    @error('offer') <div class="font-body-sm text-error">{{ $message }}</div> @enderror

                    @forelse ($candidates as $driver)
                        <div class="flex items-center justify-between border border-outline-variant p-space-sm" wire:key="cand-{{ $driver->id }}">
                            <div>
                                <div class="font-semibold text-on-surface">{{ $driver->user?->name }}</div>
                                <div class="font-body-sm text-on-surface-variant">
                                    {{ $driver->active_orders_count }} de {{ $driver->max_active_orders }} pedidos activos
                                </div>
                            </div>
                            <button class="bg-[#14B8A6] text-[#0F172A] px-space-md py-space-xs font-label-md uppercase"
                                    type="button" wire:click="offerToDriver({{ $driver->id }})">Ofrecer</button>
                        </div>
                    @empty
                        <div class="text-on-surface-variant font-body-md p-space-md border border-dashed border-outline-variant text-center">
                            No hay repartidores con capacidad disponible en este momento.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL: Cancelar -->
    @if ($showCancelModal)
        <div class="fixed inset-0 bg-[#0B1C30]/60 backdrop-blur-sm z-50 flex items-center justify-center p-space-md" role="dialog" aria-modal="true">
            <div class="bg-surface-container-lowest border border-outline-variant w-full max-w-lg shadow-2xl overflow-hidden">
                <div class="bg-surface-container-low px-space-lg py-space-md border-b border-outline-variant flex items-center justify-between">
                    <h3 class="font-headline-sm text-headline-sm text-on-surface">Cancelar Pedido</h3>
                    <button class="text-on-surface-variant hover:text-on-surface" type="button" wire:click="$set('showCancelModal', false)">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form class="p-space-lg space-y-space-md" wire:submit="cancelOrder">
                    <p class="font-body-md text-on-surface-variant">Al cancelar se libera la reserva de stock. Si el pedido está asignado el motivo es obligatorio.</p>
                    <div class="flex flex-col gap-space-xs">
                        <label class="font-label-md text-on-surface-variant uppercase">Motivo</label>
                        <textarea class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md focus:outline-none focus:border-on-surface"
                                  rows="3" wire:model="cancel_reason"></textarea>
                        @error('cancel_reason') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex items-center justify-end gap-space-sm">
                        <button class="bg-surface border border-outline-variant px-space-md py-space-sm font-label-lg text-on-surface"
                                type="button" wire:click="$set('showCancelModal', false)">Volver</button>
                        <button class="bg-error text-on-error px-space-lg py-space-sm font-label-lg" type="submit">Confirmar cancelación</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL: Reasignar -->
    @if ($showReassignModal)
        <div class="fixed inset-0 bg-[#0B1C30]/60 backdrop-blur-sm z-50 flex items-center justify-center p-space-md" role="dialog" aria-modal="true">
            <div class="bg-surface-container-lowest border border-outline-variant w-full max-w-lg shadow-2xl overflow-hidden">
                <div class="bg-surface-container-low px-space-lg py-space-md border-b border-outline-variant flex items-center justify-between">
                    <h3 class="font-headline-sm text-headline-sm text-on-surface">Reasignar Pedido</h3>
                    <button class="text-on-surface-variant hover:text-on-surface" type="button" wire:click="$set('showReassignModal', false)">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form class="p-space-lg space-y-space-md" wire:submit="reassign">
                    <p class="font-body-md text-on-surface-variant">El pedido vuelve a pendiente y el repartidor deja de verlo. El motivo es obligatorio.</p>
                    <div class="flex flex-col gap-space-xs">
                        <label class="font-label-md text-on-surface-variant uppercase">Motivo</label>
                        <textarea class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md focus:outline-none focus:border-on-surface"
                                  rows="3" wire:model="reassign_reason"></textarea>
                        @error('reassign_reason') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex items-center justify-end gap-space-sm">
                        <button class="bg-surface border border-outline-variant px-space-md py-space-sm font-label-lg text-on-surface"
                                type="button" wire:click="$set('showReassignModal', false)">Volver</button>
                        <button class="bg-[#14B8A6] text-[#0F172A] px-space-lg py-space-sm font-label-lg" type="submit">Reasignar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL: Incidencia -->
    @if ($showIncidentModal)
        <div class="fixed inset-0 bg-[#0B1C30]/60 backdrop-blur-sm z-50 flex items-center justify-center p-space-md" role="dialog" aria-modal="true">
            <div class="bg-surface-container-lowest border border-outline-variant w-full max-w-lg shadow-2xl overflow-hidden">
                <div class="bg-surface-container-low px-space-lg py-space-md border-b border-outline-variant flex items-center justify-between">
                    <h3 class="font-headline-sm text-headline-sm text-on-surface">Registrar Incidencia</h3>
                    <button class="text-on-surface-variant hover:text-on-surface" type="button" wire:click="$set('showIncidentModal', false)">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form class="p-space-lg space-y-space-md" wire:submit="registerIncident">
                    <p class="font-body-md text-on-surface-variant">El stock se reintegra solo cuando confirmes la devolución del paquete.</p>
                    <div class="flex flex-col gap-space-xs">
                        <label class="font-label-md text-on-surface-variant uppercase">Motivo</label>
                        <textarea class="bg-surface border border-outline-variant px-space-sm py-space-xs text-on-surface font-body-md focus:outline-none focus:border-on-surface"
                                  rows="3" wire:model="incident_reason"></textarea>
                        @error('incident_reason') <span class="font-body-sm text-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex items-center justify-end gap-space-sm">
                        <button class="bg-surface border border-outline-variant px-space-md py-space-sm font-label-lg text-on-surface"
                                type="button" wire:click="$set('showIncidentModal', false)">Volver</button>
                        <button class="bg-[#FFDAD6] text-[#93000A] px-space-lg py-space-sm font-label-lg" type="submit">Registrar incidencia</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL: Detalle + timeline -->
    @if ($showDetailModal && $detail)
        <div class="fixed inset-0 bg-[#0B1C30]/60 backdrop-blur-sm z-50 flex items-center justify-center p-space-md" role="dialog" aria-modal="true">
            <div class="bg-surface-container-lowest border border-outline-variant w-full max-w-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="bg-surface-container-low px-space-lg py-space-md border-b border-outline-variant flex items-center justify-between">
                    <h3 class="font-headline-sm text-headline-sm text-on-surface">
                        Pedido #{{ str_pad((string) $detail->id, 4, '0', STR_PAD_LEFT) }} · {{ $detail->customer_name }}
                    </h3>
                    <button class="text-on-surface-variant hover:text-on-surface" type="button" wire:click="$set('showDetailModal', false)">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="p-space-lg space-y-space-md overflow-y-auto">
                    <div class="grid grid-cols-2 gap-space-md font-body-md">
                        <div><span class="text-on-surface-variant">Teléfono:</span> {{ $detail->customer_phone }}</div>
                        <div><span class="text-on-surface-variant">Estado:</span> {{ $detail->status->label() }}</div>
                        <div class="col-span-2"><span class="text-on-surface-variant">Dirección:</span> {{ $detail->delivery_address }}</div>
                        <div><span class="text-on-surface-variant">Pago:</span> {{ $detail->payment_method }}</div>
                        <div><span class="text-on-surface-variant">Total:</span> $ {{ number_format($detail->total, 0, ',', '.') }}</div>
                    </div>

                    <div>
                        <div class="font-label-md text-on-surface-variant uppercase mb-space-xs">Productos</div>
                        <ul class="divide-y divide-outline-variant border border-outline-variant">
                            @foreach ($detail->items as $item)
                                <li class="flex items-center justify-between p-space-sm font-body-md">
                                    <span>{{ $item->quantity }} × {{ $item->product?->name }}</span>
                                    <span class="font-data-mono">$ {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <div class="font-label-md text-on-surface-variant uppercase mb-space-xs">Trazabilidad</div>
                        @if ($detail->requires_traceability && $detail->package)
                            <div class="font-body-sm text-on-surface-variant mb-space-xs">Guía: {{ $detail->package->tracking_code }} · {{ $detail->package->status->label() }}</div>
                            <ol class="border-l-2 border-outline-variant pl-space-md space-y-space-sm">
                                @forelse ($detail->package->scans as $scan)
                                    <li class="font-body-sm">
                                        <span class="font-label-sm uppercase text-primary">{{ $scan->type->label() }}</span>
                                        · {{ $scan->scanned_at?->format('d/m/Y H:i') }}
                                    </li>
                                @empty
                                    <li class="font-body-sm text-on-surface-variant">Sin escaneos registrados.</li>
                                @endforelse
                            </ol>
                        @else
                            <div class="font-body-sm text-on-surface-variant">Este pedido no requiere trazabilidad de paquete.</div>
                        @endif
                    </div>

                    <div>
                        <div class="font-label-md text-on-surface-variant uppercase mb-space-xs">Historial de estados</div>
                        <ol class="border-l-2 border-outline-variant pl-space-md space-y-space-sm">
                            @forelse ($detail->statusLogs->sortBy('created_at') as $log)
                                <li class="font-body-sm">
                                    <span class="font-label-sm uppercase">{{ $log->from }} → {{ $log->to }}</span>
                                    <span class="text-on-surface-variant">· {{ $log->created_at->format('d/m H:i') }}</span>
                                    @if ($log->reason) <div class="text-on-surface-variant">{{ $log->reason }}</div> @endif
                                </li>
                            @empty
                                <li class="font-body-sm text-on-surface-variant">Sin transiciones registradas.</li>
                            @endforelse
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
