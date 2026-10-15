<div class="flex flex-col w-full pb-10" wire:poll.15s>
    @if (session('success'))
        <div class="bg-primary-container text-on-primary-container p-space-md rounded-lg text-body-sm mb-space-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @error('offer')
        <div class="bg-error-container text-on-error-container p-space-md rounded-lg text-body-sm mb-space-sm">{{ $message }}</div>
    @enderror

    <!-- Status & warning banner -->
    <div class="flex flex-col gap-space-sm mb-space-md">
        <div class="flex items-center justify-between bg-surface-container-high p-space-md rounded-lg shadow-sm">
            <div class="flex items-center gap-space-sm">
                <span class="w-3 h-3 {{ $driver?->availability?->value === 'available' ? 'bg-primary-container' : 'bg-error' }} rounded-full"></span>
                <span class="text-label-lg uppercase font-headline-sm text-on-surface">Estado de flota</span>
            </div>
            <div class="bg-primary-container text-on-primary-container px-space-md py-space-xs rounded-full text-label-sm uppercase font-label-sm">
                {{ $driver?->availability?->label() ?? 'Sin perfil' }}
            </div>
        </div>
        <div class="bg-secondary-container text-on-secondary-container p-space-md rounded-lg flex items-start gap-space-md shadow-sm">
            <span class="material-symbols-outlined text-tertiary-container text-[22px]">warning</span>
            <div class="flex flex-col gap-space-xs">
                <span class="text-label-md uppercase font-headline-sm text-on-secondary-container">Aviso de telemetría</span>
                <span class="text-body-sm text-on-secondary-container">
                    @if ($hasInTransit)
                        Se envía tu ubicación cada 60 s mientras tengas entregas en tránsito.
                    @else
                        La ubicación se envía solo mientras tengas un pedido en tránsito.
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- Nueva oferta -->
    <section class="flex flex-col gap-space-md mb-space-xl">
        <h2 class="text-headline-sm font-headline-sm text-on-surface">Nuevas ofertas</h2>

        @forelse ($offers as $offer)
            <div class="bg-surface-container-low rounded-xl p-space-lg shadow-md flex flex-col gap-space-md border border-primary/20"
                 wire:key="offer-{{ $offer->id }}"
                 x-data="{
                    expires: {{ $offer->offer_expires_at?->getTimestampMs() ?? 0 }},
                    now: Date.now(),
                    get left() { return Math.max(0, Math.round((this.expires - this.now) / 1000)); }
                 }"
                 x-init="setInterval(() => now = Date.now(), 1000)">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="text-label-md text-on-surface-variant uppercase">Pedido #{{ str_pad((string) $offer->id, 4, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-headline-sm font-headline-sm text-on-surface">{{ $offer->customer_name }}</span>
                    </div>
                    <span class="text-data-mono bg-surface-container px-space-sm py-space-xs rounded text-primary font-bold"
                          x-text="left + 's'"></span>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-primary text-[20px] mt-0.5">location_on</span>
                    <span class="text-body-md text-on-surface">{{ $offer->delivery_address }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-body-md font-mono text-on-surface">$ {{ number_format($offer->total, 0, ',', '.') }}</span>
                    <span class="text-label-sm uppercase text-on-surface-variant">{{ $offer->payment_method }}</span>
                </div>
                <div class="flex gap-2 pt-1">
                    <button class="flex-1 py-3 bg-primary text-on-primary font-headline-sm uppercase rounded flex items-center justify-center gap-2 shadow-md"
                            type="button" wire:click="acceptOffer({{ $offer->id }})">
                        <span class="material-symbols-outlined text-[20px]">check</span> Aceptar
                    </button>
                    <button class="flex-1 py-3 bg-surface-container-high text-error border border-error/40 font-headline-sm uppercase rounded"
                            type="button" wire:click="rejectOffer({{ $offer->id }})">
                        Rechazar
                    </button>
                </div>
            </div>
        @empty
            <div class="text-body-md text-on-surface-variant p-space-md border border-dashed border-outline-variant rounded-lg text-center">
                No tienes ofertas pendientes.
            </div>
        @endforelse
    </section>

    <!-- Pedidos activos -->
    <section class="flex flex-col gap-space-md">
        <h2 class="text-headline-sm font-headline-sm text-on-surface">Pedidos en curso</h2>

        @forelse ($active as $order)
            <a href="{{ route('driver.orders.show', $order->id) }}"
               class="bg-surface-container-lowest rounded-lg p-space-md border border-surface-dim flex items-center justify-between"
               wire:key="active-{{ $order->id }}">
                <div class="flex flex-col">
                    <span class="text-body-lg font-medium text-on-surface">{{ $order->customer_name }}</span>
                    <span class="text-body-sm text-outline">{{ $order->delivery_address }}</span>
                    <span class="text-label-sm uppercase text-primary">{{ $order->status->label() }}</span>
                </div>
                <span class="material-symbols-outlined text-primary">chevron_right</span>
            </a>
        @empty
            <div class="text-body-md text-on-surface-variant p-space-md border border-dashed border-outline-variant rounded-lg text-center">
                No tienes pedidos activos.
            </div>
        @endforelse
    </section>

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
