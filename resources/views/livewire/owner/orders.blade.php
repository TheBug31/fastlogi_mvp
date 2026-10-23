<div class="space-y-6">
    @error('devolution')
        <x-ui.alert type="error">{{ $message }}</x-ui.alert>
    @enderror

    @php
        $lowStockProducts = $products->filter(
            fn ($product) => $product->min_stock > 0 && $product->stock < $product->min_stock,
        );
        $isFiltered = trim($search) !== '' || $statusFilter !== 'todos';
        $totalOrders = array_sum($statusCounts);
        $orderLabel = fn ($order) => '#'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT);
        $actionOrderLabel = $actionOrder ? $orderLabel($actionOrder) : null;
        $actionIsOffer = $actionOrder?->status === \App\Enums\OrderStatus::Offered;
    @endphp

    <x-ui.page-header title="Gestión de Pedidos" description="Monitoreo en tiempo real y asignación de repartidores.">
        <x-slot:actions>
            <x-ui.button wire:click="openCreateModal">
                <x-icon name="plus" />
                Nuevo pedido
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($lowStockProducts->isNotEmpty())
        <x-ui.alert type="warning">
            <p>
                Tienes <span class="font-semibold tabular-nums">{{ $lowStockProducts->count() }}</span> producto(s)
                por debajo del stock mínimo. Reabastece para no perder ventas.
            </p>

            <x-slot:actions>
                <a href="{{ route('owner.inventory.index') }}" wire:navigate class="font-medium underline">Ver inventario</a>
            </x-slot:actions>
        </x-ui.alert>
    @endif

    @if ($totalOrders === 0 && ! $isFiltered)
        <x-ui.empty-state icon="truck" title="Aún no tienes pedidos."
                          description="Crea el primero para empezar a asignar repartidores.">
            <x-slot:action>
                <x-ui.button wire:click="openCreateModal">
                    <x-icon name="plus" />
                    Nuevo pedido
                </x-ui.button>
            </x-slot:action>
        </x-ui.empty-state>
    @else
        <x-ui.card padding="none">
            <div class="px-4 pt-4 sm:px-6 sm:pt-5">
                <x-ui.tabs aria-label="Filtrar pedidos por estado">
                    <x-ui.tab :active="$statusFilter === 'todos'" :count="$totalOrders"
                              wire:click="$set('statusFilter', 'todos')">Todos</x-ui.tab>
                    <x-ui.tab :active="$statusFilter === \App\Enums\OrderStatus::Pending->value"
                              :count="$statusCounts['pending'] ?? 0"
                              wire:click="$set('statusFilter', 'pending')">Pendientes</x-ui.tab>
                    <x-ui.tab :active="$statusFilter === \App\Enums\OrderStatus::Offered->value"
                              :count="$statusCounts['offered'] ?? 0"
                              wire:click="$set('statusFilter', 'offered')">Ofrecidos</x-ui.tab>
                    <x-ui.tab :active="$statusFilter === \App\Enums\OrderStatus::Assigned->value"
                              :count="$statusCounts['assigned'] ?? 0"
                              wire:click="$set('statusFilter', 'assigned')">Asignados</x-ui.tab>
                    <x-ui.tab :active="$statusFilter === \App\Enums\OrderStatus::InTransit->value"
                              :count="$statusCounts['in_transit'] ?? 0"
                              wire:click="$set('statusFilter', 'in_transit')">En camino</x-ui.tab>
                    <x-ui.tab :active="$statusFilter === \App\Enums\OrderStatus::Delivered->value"
                              :count="$statusCounts['delivered'] ?? 0"
                              wire:click="$set('statusFilter', 'delivered')">Entregados</x-ui.tab>
                    <x-ui.tab :active="$statusFilter === \App\Enums\OrderStatus::Incident->value"
                              :count="$statusCounts['incident'] ?? 0"
                              wire:click="$set('statusFilter', 'incident')">Incidencias</x-ui.tab>
                </x-ui.tabs>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50/70 p-4">
                <div class="relative w-full sm:max-w-md">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400" aria-hidden="true">
                        <x-icon name="magnifying-glass" />
                    </span>
                    <input type="search" wire:model.live.debounce.250ms="search"
                           aria-label="Buscar por cliente, teléfono o número de pedido"
                           placeholder="Buscar por cliente, teléfono o número de pedido…"
                           class="h-10 w-full rounded-lg border border-slate-300 bg-white pl-10 pr-3.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20"/>
                </div>

                <p class="text-xs text-slate-500">
                    <span class="font-medium tabular-nums text-slate-700">{{ $orders->total() }}</span> pedido(s)
                </p>
            </div>

            <div class="divide-y divide-slate-200 lg:hidden">
                @forelse ($orders as $order)
                    <article wire:key="order-card-{{ $order->id }}" class="space-y-3 p-4 {{ $order->status === \App\Enums\OrderStatus::Incident ? 'bg-red-50/40' : 'bg-white' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <a href="{{ route('owner.orders.show', $order) }}" wire:navigate class="font-semibold tabular-nums text-teal-700 hover:underline">{{ $orderLabel($order) }}</a>
                                <p class="mt-0.5 text-xs text-slate-500">{{ format_datetime($order->created_at) }}</p>
                            </div>
                            <x-ui.status-badge :status="$order->status" />
                        </div>
                        <div class="space-y-1 text-sm">
                            <p class="font-medium text-slate-900">{{ $order->customer_name }} <span class="font-normal tabular-nums text-slate-500">· {{ $order->customer_phone }}</span></p>
                            <p class="break-words text-slate-600">{{ $order->delivery_address }}</p>
                            <p class="text-xs text-slate-500">{{ $order->deliveryDriver?->user?->name ?? 'Sin asignar' }}</p>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
                            <div>
                                <p class="font-semibold tabular-nums text-slate-900">{{ format_cop($order->total) }}</p>
                                <p class="text-xs text-slate-500">{{ $order->payment_method }}</p>
                            </div>
                            <div class="flex flex-wrap justify-end gap-2">
                                @switch($order->status)
                                    @case(\App\Enums\OrderStatus::Pending)
                                        <x-ui.button size="sm" type="button" wire:click="openOfferModal({{ $order->id }})">Ofrecer</x-ui.button>
                                        @break
                                    @case(\App\Enums\OrderStatus::Offered)
                                        <x-ui.button variant="secondary" size="sm" type="button" wire:click="openReassignModal({{ $order->id }})">Retirar</x-ui.button>
                                        @break
                                    @case(\App\Enums\OrderStatus::Assigned)
                                    @case(\App\Enums\OrderStatus::InTransit)
                                    @case(\App\Enums\OrderStatus::Delivered)
                                        <x-ui.button variant="secondary" size="sm" type="button" wire:click="openDetailModal({{ $order->id }})">Ver detalle</x-ui.button>
                                        @break
                                    @case(\App\Enums\OrderStatus::Incident)
                                        <x-ui.button size="sm" type="button" wire:click="confirmDevolution({{ $order->id }})" wire:confirm="¿Confirmas la recepción del paquete devuelto? Se reintegrará el stock.">Confirmar devolución</x-ui.button>
                                        @break
                                    @case(\App\Enums\OrderStatus::Cancelled)
                                        <span class="max-w-40 text-right text-xs italic text-slate-500">{{ $order->cancel_reason ?: 'Cancelado' }}</span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="px-4 py-10 text-center text-sm text-slate-500">No hay pedidos que coincidan con los filtros.</p>
                @endforelse
            </div>

            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <x-ui.table.th>Pedido</x-ui.table.th>
                            <x-ui.table.th>Cliente</x-ui.table.th>
                            <x-ui.table.th>Dirección</x-ui.table.th>
                            <x-ui.table.th align="right">Total</x-ui.table.th>
                            <x-ui.table.th>Repartidor</x-ui.table.th>
                            <x-ui.table.th>Estado</x-ui.table.th>
                            <x-ui.table.th align="right">Acción</x-ui.table.th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($orders as $order)
                            <tr wire:key="order-{{ $order->id }}"
                                @class(['hover:bg-slate-50/70', 'bg-red-50/40' => $order->status === \App\Enums\OrderStatus::Incident])>
                                <x-ui.table.td>
                                    <a href="{{ route('owner.orders.show', $order) }}" wire:navigate
                                       class="font-semibold tabular-nums text-teal-700 hover:underline">{{ $orderLabel($order) }}</a>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ format_datetime($order->created_at) }}</p>
                                </x-ui.table.td>

                                <x-ui.table.td>
                                    <p class="font-medium text-slate-900">{{ $order->customer_name }}</p>
                                    <p class="mt-0.5 text-xs tabular-nums text-slate-500">{{ $order->customer_phone }}</p>
                                </x-ui.table.td>

                                <x-ui.table.td>
                                    <p class="max-w-[240px] whitespace-normal break-words text-slate-700">{{ $order->delivery_address }}</p>
                                </x-ui.table.td>

                                <x-ui.table.td align="right">
                                    <p class="font-semibold tabular-nums text-slate-900">{{ format_cop($order->total) }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $order->payment_method }}</p>
                                </x-ui.table.td>

                                <x-ui.table.td>
                                    @if ($order->deliveryDriver?->user)
                                        <div class="flex items-center gap-2">
                                            <x-ui.avatar :name="$order->deliveryDriver->user->name" size="sm" />
                                            <span>{{ $order->deliveryDriver->user->name }}</span>
                                        </div>
                                    @else
                                        <span class="italic text-slate-400">Sin asignar</span>
                                    @endif
                                </x-ui.table.td>

                                <x-ui.table.td>
                                    <x-ui.status-badge :status="$order->status" />
                                </x-ui.table.td>

                                <x-ui.table.td align="right">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @switch($order->status)
                                            @case(\App\Enums\OrderStatus::Pending)
                                                <x-ui.button size="sm" wire:click="openOfferModal({{ $order->id }})">Ofrecer</x-ui.button>
                                                @break

                                            @case(\App\Enums\OrderStatus::Offered)
                                                @php
                                                    $remaining = $order->offer_expires_at
                                                        ? max(0, (int) now()->diffInSeconds($order->offer_expires_at, false))
                                                        : 0;
                                                @endphp
                                                <span class="text-xs font-medium text-slate-600"
                                                      x-data="{
                                                          left: {{ $remaining }},
                                                          timer: null,
                                                          init() { this.timer = setInterval(() => { if (this.left > 0) this.left--; }, 1000); },
                                                          destroy() { clearInterval(this.timer); },
                                                      }">
                                                    Responde en
                                                    <span class="tabular-nums" x-text="Math.floor(left / 60) + ':' + String(left % 60).padStart(2, '0')">{{ floor($remaining / 60) }}:{{ str_pad((string) ($remaining % 60), 2, '0', STR_PAD_LEFT) }}</span>
                                                </span>
                                                <x-ui.button variant="secondary" size="sm"
                                                             wire:click="openReassignModal({{ $order->id }})">Retirar</x-ui.button>
                                                @break

                                            @case(\App\Enums\OrderStatus::Assigned)
                                            @case(\App\Enums\OrderStatus::InTransit)
                                            @case(\App\Enums\OrderStatus::Delivered)
                                                <x-ui.button variant="secondary" size="sm"
                                                             wire:click="openDetailModal({{ $order->id }})">Ver detalle</x-ui.button>
                                                @break

                                            @case(\App\Enums\OrderStatus::Cancelled)
                                                <span class="text-xs italic text-slate-400">{{ $order->cancel_reason ?: 'Sin motivo registrado' }}</span>
                                                @break

                                            @case(\App\Enums\OrderStatus::Incident)
                                                <x-ui.button size="sm" wire:click="confirmDevolution({{ $order->id }})"
                                                             wire:confirm="¿Confirmas la recepción del paquete devuelto? Se reintegrará el stock.">
                                                    Confirmar devolución
                                                </x-ui.button>
                                                @break
                                        @endswitch
                                    </div>
                                </x-ui.table.td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-14 text-center text-sm text-slate-500">
                                    No hay pedidos que coincidan con los filtros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $orders->links('partials.pagination') }}
        </x-ui.card>
    @endif

    <!-- MODAL: Crear pedido -->
    @if ($showCreateModal)
        <x-ui.modal property="showCreateModal" title="Nuevo pedido" subtitle="Al crear el pedido se reserva el stock.">
            <form id="order-create-form" wire:submit="createOrder" class="space-y-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-ui.input name="customer_name" label="Cliente" required wire:model="customer_name" />
                    <x-ui.input name="customer_phone" label="Teléfono" type="tel" required wire:model="customer_phone" />

                    <div class="sm:col-span-2">
                        <x-ui.input name="delivery_address" label="Dirección de entrega" required wire:model="delivery_address" />
                    </div>

                    <x-ui.select name="payment_method" label="Método de pago" required wire:model="payment_method">
                        <option value="Efectivo">Efectivo</option>
                        <option value="Transferencia">Transferencia</option>
                        <option value="Tarjeta">Tarjeta</option>
                    </x-ui.select>

                    <div class="flex items-end pb-2.5">
                        <x-ui.checkbox name="requires_traceability" label="Requiere trazabilidad (paquete)"
                                       wire:model="requires_traceability" />
                    </div>
                </div>

                <div class="space-y-3 border-t border-slate-200 pt-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-900">Productos</h3>
                        <x-ui.button variant="ghost" size="sm" wire:click="addLine">
                            <x-icon name="plus" class="h-4 w-4" />
                            Agregar producto
                        </x-ui.button>
                    </div>

                    @error('lines')
                        <x-ui.alert type="error">{{ $message }}</x-ui.alert>
                    @enderror

                    @foreach ($lines as $index => $line)
                        <div wire:key="line-{{ $index }}" class="flex items-start gap-3">
                            <div class="min-w-0 flex-1">
                                <x-ui.select name="lines.{{ $index }}.product_id" aria-label="Producto {{ $index + 1 }}"
                                             wire:model="lines.{{ $index }}.product_id">
                                    <option value="">Seleccione un producto…</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">
                                            {{ $product->name }} — {{ format_cop($product->price) }} · stock: {{ $product->stock }}
                                        </option>
                                    @endforeach
                                </x-ui.select>
                            </div>

                            <div class="w-24">
                                <x-ui.input name="lines.{{ $index }}.quantity" type="number" min="1"
                                            aria-label="Cantidad {{ $index + 1 }}" wire:model="lines.{{ $index }}.quantity" />
                            </div>

                            <div class="w-10">
                                <x-ui.button variant="ghost" size="sm" class="w-10"
                                             wire:click="removeLine({{ $index }})"
                                             aria-label="Quitar producto {{ $index + 1 }}">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </x-ui.button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </form>

            <x-slot:footer>
                <x-ui.button variant="secondary" @click="$wire.set('showCreateModal', false)">Cancelar</x-ui.button>
                <x-ui.button type="submit" form="order-create-form">Crear pedido y reservar stock</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    <!-- MODAL: Ofrecer pedido -->
    @if ($showOfferModal)
        <x-ui.modal property="showOfferModal" title="Ofrecer pedido {{ $actionOrderLabel ?? '' }}"
                    subtitle="El repartidor tiene 90 segundos para aceptar.">
            @error('offer')
                <x-ui.alert type="error">{{ $message }}</x-ui.alert>
            @enderror

            @if ($actionOrder)
                <div class="space-y-1.5 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm">
                    <p class="font-medium text-slate-900">{{ $actionOrder->customer_name }}</p>
                    <p class="max-w-[420px] text-slate-600">{{ $actionOrder->delivery_address }}</p>
                    <div class="flex flex-wrap items-center gap-2 pt-0.5">
                        <span class="font-semibold tabular-nums text-slate-900">{{ format_cop($actionOrder->total) }}</span>
                        <span class="text-slate-400" aria-hidden="true">·</span>
                        <span class="text-slate-600">{{ $actionOrder->payment_method }}</span>
                        @if ($actionOrder->requires_traceability)
                            <x-ui.badge color="blue">Con trazabilidad</x-ui.badge>
                        @endif
                    </div>
                </div>
            @endif

            <div class="space-y-3">
                @forelse ($candidates as $driver)
                    <div wire:key="candidate-{{ $driver->id }}"
                         class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 p-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <x-ui.avatar :name="$driver->user?->name ?? ''" />

                            <div class="min-w-0 space-y-1.5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-medium text-slate-900">{{ $driver->user?->name }}</span>
                                    <x-ui.availability-badge :availability="$driver->availability" />
                                </div>

                                <x-ui.capacity-bar :active="$driver->active_orders_count" :max="$driver->max_active_orders"
                                                   :label="$driver->active_orders_count.'/'.$driver->max_active_orders.' pedidos activos (incluye ofertas pendientes)'" />
                            </div>
                        </div>

                        <x-ui.button size="sm" wire:click="offerToDriver({{ $driver->id }})">Ofrecer</x-ui.button>
                    </div>
                @empty
                    <x-ui.empty-state icon="users" title="No hay repartidores disponibles."
                                      description="Todos están desconectados o con la capacidad completa." />
                @endforelse
            </div>
        </x-ui.modal>
    @endif

    <!-- DRAWER: Detalle del pedido -->
    @if ($showDetailModal && $detail)
        <x-ui.drawer property="showDetailModal" title="Pedido {{ $orderLabel($detail) }}" size="md">
            <x-slot:meta>
                <x-ui.status-badge :status="$detail->status" />
                <span class="text-xs text-slate-500">{{ format_datetime($detail->created_at) }}</span>
            </x-slot:meta>

            <section class="rounded-xl border border-slate-200 bg-white p-4">
                <h3 class="text-sm font-semibold text-slate-900">Cliente</h3>

                <dl class="mt-2.5 space-y-2 text-sm">
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-500">Nombre</dt>
                        <dd class="text-right font-medium text-slate-900">{{ $detail->customer_name }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-500">Teléfono</dt>
                        <dd class="text-right tabular-nums text-slate-900">{{ $detail->customer_phone }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-500">Dirección</dt>
                        <dd class="max-w-[65%] text-right text-slate-900">{{ $detail->delivery_address }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-500">Pago</dt>
                        <dd class="text-right text-slate-900">{{ $detail->payment_method }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-sm font-semibold text-slate-900">Productos</h3>
                    <span class="text-sm font-semibold tabular-nums text-slate-900">{{ format_cop($detail->total) }}</span>
                </div>

                <ul class="mt-2 divide-y divide-slate-100">
                    @foreach ($detail->items as $item)
                        <li class="flex items-center justify-between gap-4 py-2 text-sm">
                            <span class="text-slate-700">
                                <span class="tabular-nums">{{ $item->quantity }}</span> ×
                                {{ $item->product?->name ?? 'Producto no disponible' }}
                            </span>
                            <span class="shrink-0 tabular-nums text-slate-900">{{ format_cop($item->subtotal) }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-4">
                <h3 class="text-sm font-semibold text-slate-900">Repartidor asignado</h3>

                @if ($detail->deliveryDriver?->user)
                    <div class="mt-2.5 flex items-center gap-3">
                        <x-ui.avatar :name="$detail->deliveryDriver->user->name" />
                        <div>
                            <p class="text-sm font-medium text-slate-900">{{ $detail->deliveryDriver->user->name }}</p>
                            <div class="mt-1">
                                <x-ui.availability-badge :availability="$detail->deliveryDriver->availability" />
                            </div>
                        </div>
                    </div>
                @else
                    <p class="mt-2 text-sm italic text-slate-400">Sin asignar</p>
                @endif
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-4">
                <h3 class="text-sm font-semibold text-slate-900">Trazabilidad</h3>

                @if ($detail->requires_traceability && $detail->package)
                    <p class="mt-1.5 text-xs text-slate-500">
                        Guía <span class="font-medium tabular-nums text-slate-700">{{ $detail->package->tracking_code }}</span>
                        · {{ $detail->package->status->label() }}
                    </p>

                    <ol class="mt-3 space-y-3 border-l border-slate-200 pl-4">
                        @forelse ($detail->package->scans as $scan)
                            <li class="relative">
                                <span class="absolute -left-5 top-1.5 h-2 w-2 rounded-full bg-teal-600" aria-hidden="true"></span>
                                <p class="text-sm font-medium text-slate-900">{{ $scan->type->label() }}</p>
                                <p class="text-xs text-slate-500">{{ format_datetime($scan->scanned_at) }}</p>
                            </li>
                        @empty
                            <li class="text-sm text-slate-500">Sin escaneos registrados.</li>
                        @endforelse
                    </ol>
                @else
                    <p class="mt-1.5 text-sm text-slate-500">Este pedido no tiene trazabilidad.</p>
                @endif
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-4">
                <h3 class="text-sm font-semibold text-slate-900">Historial de estados</h3>

                <ol class="mt-3 space-y-3 border-l border-slate-200 pl-4">
                    @forelse ($detail->statusLogs->sortBy('created_at') as $log)
                        <li class="relative">
                            <span class="absolute -left-5 top-1.5 h-2 w-2 rounded-full bg-teal-600" aria-hidden="true"></span>
                            <p class="text-sm font-medium text-slate-900">
                                {{ \App\Enums\OrderStatus::tryFrom($log->from)?->label() ?? $log->from }}
                                <span class="text-slate-400" aria-hidden="true">→</span>
                                {{ \App\Enums\OrderStatus::tryFrom($log->to)?->label() ?? $log->to }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ format_datetime($log->created_at) }} · {{ $log->user?->name ?? 'Sistema' }}
                            </p>
                            @if ($log->reason)
                                <p class="mt-0.5 text-xs text-slate-500">{{ $log->reason }}</p>
                            @endif
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">Sin transiciones registradas.</li>
                    @endforelse
                </ol>
            </section>

            <x-slot:footer>
                <x-ui.button variant="secondary" @click="$wire.set('showDetailModal', false)">Cerrar</x-ui.button>

                @if ($detail->canBeCancelled())
                    <x-ui.button variant="danger" wire:click="openCancelModal({{ $detail->id }})">Cancelar pedido</x-ui.button>
                @endif

                @if ($detail->status === \App\Enums\OrderStatus::Assigned)
                    <x-ui.button variant="secondary" wire:click="openReassignModal({{ $detail->id }})">Reasignar</x-ui.button>
                @endif

                @if ($detail->status === \App\Enums\OrderStatus::InTransit)
                    <x-ui.button variant="danger" wire:click="openIncidentModal({{ $detail->id }})">Registrar incidencia</x-ui.button>
                @endif

                @if ($detail->status === \App\Enums\OrderStatus::Incident)
                    <x-ui.button wire:click="confirmDevolution({{ $detail->id }})"
                                 wire:confirm="¿Confirmas la recepción del paquete devuelto? Se reintegrará el stock.">
                        Confirmar devolución
                    </x-ui.button>
                @endif
            </x-slot:footer>
        </x-ui.drawer>
    @endif

    <!-- MODAL: Cancelar pedido -->
    @if ($showCancelModal)
        <x-ui.modal property="showCancelModal" title="Cancelar pedido"
                    :subtitle="$actionOrderLabel ? $actionOrderLabel.' · '.$actionOrder->customer_name : null">
            <form id="order-cancel-form" wire:submit="cancelOrder" class="space-y-4">
                <x-ui.alert type="warning">
                    Al cancelar se libera la reserva de stock. Si el pedido está asignado, el motivo es obligatorio.
                </x-ui.alert>

                <x-ui.textarea name="cancel_reason" label="Motivo" rows="3" wire:model="cancel_reason"
                               help="Queda registrado en el historial del pedido." />
            </form>

            <x-slot:footer>
                <x-ui.button variant="secondary" @click="$wire.set('showCancelModal', false)">Volver</x-ui.button>
                <x-ui.button variant="danger-solid" type="submit" form="order-cancel-form">Confirmar cancelación</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    <!-- MODAL: Reasignar / retirar oferta -->
    @if ($showReassignModal)
        <x-ui.modal property="showReassignModal" :title="$actionIsOffer ? 'Retirar oferta' : 'Reasignar pedido'"
                    :subtitle="$actionOrderLabel ? $actionOrderLabel.' · '.$actionOrder->customer_name : null">
            <form id="order-reassign-form" wire:submit="reassign" class="space-y-4">
                <x-ui.alert type="warning">
                    {{ $actionIsOffer
                        ? 'El pedido vuelve a pendiente y el repartidor deja de ver la oferta.'
                        : 'El pedido vuelve a pendiente y el repartidor deja de verlo.' }}
                    El motivo es obligatorio.
                </x-ui.alert>

                <x-ui.textarea name="reassign_reason" label="Motivo" rows="3" wire:model="reassign_reason" />
            </form>

            <x-slot:footer>
                <x-ui.button variant="secondary" @click="$wire.set('showReassignModal', false)">Volver</x-ui.button>
                <x-ui.button type="submit" form="order-reassign-form">{{ $actionIsOffer ? 'Retirar oferta' : 'Reasignar' }}</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    <!-- MODAL: Registrar incidencia -->
    @if ($showIncidentModal)
        <x-ui.modal property="showIncidentModal" title="Registrar incidencia"
                    :subtitle="$actionOrderLabel ? $actionOrderLabel.' · '.$actionOrder->customer_name : null">
            <form id="order-incident-form" wire:submit="registerIncident" class="space-y-4">
                <x-ui.alert type="warning">
                    El stock se reintegra solo cuando confirmes la devolución del paquete.
                </x-ui.alert>

                <x-ui.textarea name="incident_reason" label="Motivo" rows="3" wire:model="incident_reason" />
            </form>

            <x-slot:footer>
                <x-ui.button variant="secondary" @click="$wire.set('showIncidentModal', false)">Volver</x-ui.button>
                <x-ui.button variant="danger-solid" type="submit" form="order-incident-form">Registrar incidencia</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif
</div>
