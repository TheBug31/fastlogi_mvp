<div class="space-y-6" wire:poll.15s>
    <x-ui.toast />

    @error('offer')
        <x-ui.alert type="error">{{ $message }}</x-ui.alert>
    @enderror

    @php
        $availability = $driver?->availability;
        $maxActive = $driver?->max_active_orders ?? 3;
        $activeCount = $offers->count() + $active->count();
        $reserved = $offers->count() + $active->count();
        $locationStale = ! $driver?->last_seen_at?->gt(now()->subMinutes(3));
        $orderLabel = fn ($order) => 'Pedido #'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT);
    @endphp

    <x-ui.page-header title="Rutas de hoy" description="Tus ofertas y entregas en curso." />

    @if ($hasInTransit && $locationStale)
        <x-ui.alert type="warning">
            No estamos recibiendo tu ubicación. Mantén esta pestaña abierta y la pantalla encendida.
        </x-ui.alert>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Columna principal -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Nueva oferta -->
            <section class="space-y-4">
                <h2 class="text-lg font-semibold text-slate-900">Nuevas ofertas</h2>

                @forelse ($offers as $offer)
                    @php
                        $window = $offer->offer_expires_at && $offer->offered_at
                            ? max(1, (int) abs($offer->offered_at->diffInSeconds($offer->offer_expires_at)))
                            : 90;
                    @endphp

                    <div class="flex flex-col gap-4 rounded-xl border-2 border-teal-600 bg-white p-6 shadow-sm"
                         wire:key="offer-{{ $offer->id }}"
                         x-data="{
                            expires: {{ $offer->offer_expires_at?->getTimestampMs() ?? 0 }},
                            duration: {{ $window }},
                            now: Date.now(),
                            get left() { return Math.max(0, Math.round((this.expires - this.now) / 1000)); },
                            get offset() { return (1 - Math.min(1, this.left / this.duration)) * 163.36; }
                         }"
                         x-init="setInterval(() => now = Date.now(), 1000)">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-slate-500">{{ $orderLabel($offer) }}</p>
                                <p class="mt-0.5 truncate text-lg font-semibold text-slate-900">{{ $offer->customer_name }}</p>
                            </div>

                            @if ($offer->offer_expires_at)
                                <div class="relative h-16 w-16 shrink-0">
                                    <svg viewBox="0 0 64 64" class="h-16 w-16 -rotate-90">
                                        <circle cx="32" cy="32" r="26" fill="none" stroke-width="6" class="stroke-slate-200" />
                                        <circle cx="32" cy="32" r="26" fill="none" stroke-width="6" stroke-linecap="round"
                                                stroke-dasharray="163.36"
                                                class="transition-[stroke-dashoffset] duration-1000 ease-linear"
                                                :class="left <= 10 ? 'stroke-red-600' : (left <= 30 ? 'stroke-amber-500' : 'stroke-teal-600')"
                                                :style="'stroke-dashoffset: ' + offset" />
                                    </svg>
                                    <span class="absolute inset-0 flex items-center justify-center text-sm font-semibold tabular-nums text-slate-900"
                                          x-text="left + 's'"></span>
                                </div>
                            @endif
                        </div>

                        <div class="flex items-start gap-2.5">
                            <x-icon name="map-pin" class="mt-0.5 h-5 w-5 shrink-0 text-teal-600" />
                            <span class="text-sm text-slate-700">{{ $offer->delivery_address }}</span>
                        </div>

                        @if ($offer->items->isNotEmpty())
                            <ul class="divide-y divide-slate-100 rounded-lg border border-slate-200">
                                @foreach ($offer->items as $item)
                                    <li class="flex items-center justify-between px-3 py-2 text-sm">
                                        <span class="text-slate-700">{{ $item->quantity }} × {{ $item->product?->name }}</span>
                                        <span class="tabular-nums text-slate-500">{{ format_cop($item->subtotal) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="flex items-center justify-between">
                            <span class="text-base font-semibold tabular-nums text-slate-900">{{ format_cop($offer->total) }}</span>
                            <x-ui.badge color="slate-soft">{{ $offer->payment_method }}</x-ui.badge>
                        </div>

                        <div class="flex gap-3 pt-1">
                            <x-ui.button variant="secondary" class="flex-1" type="button" wire:click="rejectOffer({{ $offer->id }})">
                                Rechazar
                            </x-ui.button>
                            <x-ui.button class="flex-1" type="button" wire:click="acceptOffer({{ $offer->id }})">
                                <x-icon name="check" class="h-4 w-4" />
                                Aceptar
                            </x-ui.button>
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state icon="inbox" title="No tienes ofertas pendientes."
                                      description="Cuando el negocio te ofrezca un pedido aparecerá aquí." />
                @endforelse
            </section>

            <!-- Mis pedidos -->
            <section class="space-y-4">
                <h2 class="text-lg font-semibold text-slate-900">Mis pedidos</h2>

                @forelse ($active as $order)
                    <a href="{{ route('driver.orders.show', $order) }}" wire:navigate
                       class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition-colors hover:border-teal-300"
                       wire:key="active-{{ $order->id }}">
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-slate-500">{{ $orderLabel($order) }}</p>
                            <p class="mt-0.5 truncate font-semibold text-slate-900">{{ $order->customer_name }}</p>
                            <p class="mt-0.5 truncate text-sm text-slate-500">{{ $order->delivery_address }}</p>
                            <div class="mt-2">
                                <x-ui.status-badge :status="$order->status" />
                            </div>
                        </div>
                        <x-icon name="chevron-right" class="h-5 w-5 shrink-0 text-slate-400" />
                    </a>
                @empty
                    <x-ui.empty-state icon="truck" title="No tienes pedidos activos."
                                      description="Acepta una oferta para empezar una entrega." />
                @endforelse
            </section>
        </div>

        <!-- Tu día -->
        <div class="lg:col-span-1">
            <x-ui.card class="lg:sticky lg:top-24">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">Tu día</h2>
                    @if ($availability)
                        <x-ui.availability-badge :availability="$availability->value" />
                    @endif
                </div>

                <dl class="mt-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-slate-600">Entregadas hoy</dt>
                        <dd class="text-lg font-semibold tabular-nums text-slate-900">{{ $deliveredToday }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-slate-600">En curso</dt>
                        <dd class="text-lg font-semibold tabular-nums text-slate-900">{{ $active->count() }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-slate-600">Ofertas</dt>
                        <dd class="text-lg font-semibold tabular-nums text-slate-900">{{ $offers->count() }}</dd>
                    </div>
                </dl>

                <div class="mt-5 border-t border-slate-200 pt-5">
                    <x-ui.capacity-bar :active="$reserved" :max="$maxActive"
                                       :label="$reserved.' de '.$maxActive.' pedidos activos'" />
                    <p class="mt-2 text-xs text-slate-500">El cálculo incluye las ofertas pendientes.</p>
                </div>
            </x-ui.card>
        </div>
    </div>

    @script
    <script>
        (() => {
            const shouldSend = {{ $hasInTransit ? 'true' : 'false' }};
            if (!shouldSend || !navigator.geolocation) {
                return;
            }
            const url = @json(route('driver.telemetry'));
            const token = @json(csrf_token());
            const send = () => navigator.geolocation.getCurrentPosition((position) => {
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                    }),
                }).catch(() => {});
            }, () => {}, { enableHighAccuracy: false, timeout: 10000 });
            send();
            setInterval(send, 60000);
        })();
    </script>
    @endscript
</div>