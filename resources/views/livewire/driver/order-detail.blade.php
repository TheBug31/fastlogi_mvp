<div class="flex flex-col gap-4 w-full pb-8">
    @if (session('success'))
        <div class="bg-primary-container text-on-primary-container p-space-md rounded-lg text-body-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @error('scan')
        <div class="bg-error-container text-on-error-container p-space-md rounded-lg text-body-sm">{{ $message }}</div>
    @enderror

    @php
        $step = match ($order->status) {
            \App\Enums\OrderStatus::Assigned => 2,
            \App\Enums\OrderStatus::InTransit => 4,
            \App\Enums\OrderStatus::Delivered => 5,
            default => 1,
        };
        $badge = match ($order->status) {
            \App\Enums\OrderStatus::Assigned => 'bg-secondary-container text-on-secondary-container',
            \App\Enums\OrderStatus::InTransit => 'bg-primary-container text-on-primary-container',
            \App\Enums\OrderStatus::Delivered => 'bg-primary-container text-on-primary-container',
            \App\Enums\OrderStatus::Incident => 'bg-error-container text-on-error-container',
            \App\Enums\OrderStatus::Cancelled => 'bg-error-container text-on-error-container',
            default => 'bg-surface-container-high text-on-surface',
        };
    @endphp

    <!-- Progress -->
    <div class="flex flex-col gap-1.5 bg-surface-container-lowest p-3 rounded-lg border border-surface-dim">
        <div class="flex justify-between items-center text-label-sm uppercase text-on-surface-variant">
            <span>Progreso de ruta</span>
            <span class="text-primary font-bold">Paso {{ $step }} de 5</span>
        </div>
        <div class="grid grid-cols-5 gap-1 h-2 w-full">
            @for ($i = 1; $i <= 5; $i++)
                <div class="{{ $i <= $step ? 'bg-primary' : 'bg-surface-dim' }} h-full rounded-sm"></div>
            @endfor
        </div>
        <div class="flex justify-between text-[10px] text-outline font-mono mt-0.5">
            <span>Recogida</span>
            <span>Entrega</span>
        </div>
    </div>

    <!-- Header -->
    <div class="bg-surface-container-lowest p-4 rounded-lg border border-surface-dim flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-label-md uppercase text-outline">Guía de rastreo</span>
                <h2 class="text-headline-lg-mobile font-headline-lg-mobile text-on-surface">
                    {{ $order->package?->tracking_code ?? '#'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT) }}
                </h2>
            </div>
            <div class="px-3 py-1 {{ $badge }} text-label-sm uppercase rounded font-bold">
                {{ $order->status->label() }}
            </div>
        </div>
        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-surface-dim">
            <div class="flex flex-col">
                <span class="text-label-sm uppercase text-outline">Total a cobrar</span>
                <span class="text-body-md font-mono font-medium text-on-surface">$ {{ number_format($order->total, 0, ',', '.') }}</span>
            </div>
            <div class="flex flex-col">
                <span class="text-label-sm uppercase text-outline">Método de pago</span>
                <span class="text-body-md font-mono font-medium text-on-surface">{{ $order->payment_method }}</span>
            </div>
        </div>
    </div>

    <!-- Customer -->
    <div class="bg-surface-container-lowest p-4 rounded-lg border border-surface-dim flex flex-col gap-3">
        <div class="flex items-center justify-between border-b border-surface-dim pb-2">
            <div class="flex flex-col">
                <span class="text-label-sm uppercase text-outline">Cliente</span>
                <span class="text-body-lg font-medium text-on-surface">{{ $order->customer_name }}</span>
            </div>
            <a class="w-10 h-10 rounded-full bg-primary-container/10 border border-primary-container text-primary flex items-center justify-center"
               href="tel:{{ $order->customer_phone }}">
                <span class="material-symbols-outlined text-[20px]">call</span>
            </a>
        </div>
        <div class="flex items-start gap-2.5 pt-1">
            <span class="material-symbols-outlined text-primary text-[20px] mt-0.5">location_on</span>
            <span class="text-body-md text-on-surface font-medium">{{ $order->delivery_address }}</span>
        </div>
    </div>

    <!-- Package manifest -->
    <div class="bg-surface-container-lowest p-4 rounded-lg border border-surface-dim flex flex-col gap-3">
        <div class="flex items-center justify-between border-b border-surface-dim pb-2">
            <span class="text-label-md uppercase text-outline">Manifiesto de paquetes</span>
            <span class="text-label-sm uppercase px-2 py-0.5 bg-surface-container-high text-on-surface rounded font-mono">
                {{ $order->items->count() }} ítems
            </span>
        </div>
        <div class="flex flex-col gap-2">
            @foreach ($order->items as $item)
                <div class="flex items-center justify-between p-2 rounded bg-surface-container-low border border-surface-dim">
                    <span class="text-body-md font-medium text-on-surface">{{ $item->quantity }} × {{ $item->product?->name }}</span>
                    <span class="text-label-sm uppercase text-on-surface-variant font-mono">$ {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Scans timeline -->
    @if ($order->requires_traceability)
        <div class="bg-surface-container-lowest p-4 rounded-lg border border-surface-dim flex flex-col gap-2">
            <span class="text-label-md uppercase text-outline">Escaneos</span>
            @forelse ($order->package?->scans ?? [] as $scan)
                <div class="flex items-center justify-between text-body-sm">
                    <span class="font-bold uppercase text-primary">{{ $scan->type->label() }}</span>
                    <span class="text-outline font-mono">{{ $scan->scanned_at?->format('d/m H:i') }}</span>
                </div>
            @empty
                <span class="text-body-sm text-on-surface-variant">Aún no hay escaneos registrados.</span>
            @endforelse
        </div>
    @endif

    <!-- Actions -->
    <div class="flex flex-col gap-2 pt-2">
        @if ($order->status === \App\Enums\OrderStatus::Assigned)
            <button class="w-full py-3.5 px-4 bg-primary text-on-primary font-headline-sm uppercase rounded flex items-center justify-center gap-2 shadow-md"
                    type="button" wire:click="scanPickup">
                <span class="material-symbols-outlined text-[20px]">qr_code_scanner</span>
                {{ $order->requires_traceability ? 'Escanear recogida' : 'Iniciar ruta' }}
            </button>
        @endif

        @if ($order->status === \App\Enums\OrderStatus::InTransit)
            <button class="w-full py-3.5 px-4 bg-primary text-on-primary font-headline-sm uppercase rounded flex items-center justify-center gap-2 shadow-md"
                    type="button" wire:click="scanDelivery"
                    wire:confirm="¿Confirmas la entrega de este pedido?">
                <span class="material-symbols-outlined text-[20px]">{{ $order->requires_traceability ? 'qr_code_scanner' : 'task_alt' }}</span>
                {{ $order->requires_traceability ? 'Escanear entrega' : 'Marcar entregado' }}
            </button>
            <button class="w-full py-3 px-4 bg-surface-container-lowest text-error border border-error/50 font-headline-sm uppercase rounded flex items-center justify-center gap-2"
                    type="button" wire:click="openIncidentSheet">
                <span class="material-symbols-outlined text-[20px]">report_problem</span>
                Reportar incidencia
            </button>
        @endif

        @if (in_array($order->status, [\App\Enums\OrderStatus::Delivered, \App\Enums\OrderStatus::Cancelled, \App\Enums\OrderStatus::Incident]))
            <div class="p-4 rounded-lg border border-surface-dim bg-surface-container-low text-body-sm text-on-surface-variant text-center">
                Este pedido ya no requiere acciones. Estado final: <strong>{{ $order->status->label() }}</strong>.
            </div>
        @endif

        <a href="{{ route('driver.routes') }}"
           class="w-full py-3 px-4 bg-surface-container-high text-on-surface font-headline-sm uppercase rounded flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span> Volver a rutas
        </a>
    </div>

    <!-- Incident sheet -->
    @if ($showIncidentSheet)
        <div class="fixed inset-0 z-50 bg-on-surface/40 backdrop-blur-sm flex items-end justify-center" role="dialog" aria-modal="true">
            <div class="w-full max-w-[390px] bg-surface-container-lowest rounded-t-xl p-5 border-t border-surface-dim flex flex-col gap-4 shadow-xl">
                <div class="flex items-center justify-between border-b border-surface-dim pb-3">
                    <div class="flex items-center gap-2 text-error">
                        <span class="material-symbols-outlined">warning</span>
                        <h3 class="text-headline-sm font-headline-sm">Reportar incidencia</h3>
                    </div>
                    <button class="w-8 h-8 rounded-full bg-surface-container-high flex items-center justify-center" type="button"
                            wire:click="$set('showIncidentSheet', false)">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-label-md uppercase text-outline">Motivo del reporte</label>
                    <select class="w-full p-2.5 rounded bg-surface border border-surface-dim text-on-surface font-body-md focus:outline-none"
                            wire:model="incidentReason">
                        <option>Cliente ausente / No contesta</option>
                        <option>Dirección incorrecta o incompleta</option>
                        <option>Paquete dañado en tránsito</option>
                        <option>Negocio cerrado</option>
                    </select>
                    @error('incidentReason') <span class="text-body-sm text-error">{{ $message }}</span> @enderror
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-label-md uppercase text-outline">Observaciones adicionales</label>
                    <textarea class="w-full p-2.5 rounded bg-surface border border-surface-dim text-on-surface font-body-md resize-none focus:outline-none"
                              placeholder="Describe la novedad..." rows="3" wire:model="incidentNotes"></textarea>
                </div>
                <div class="flex gap-2 pt-2">
                    <button class="flex-1 py-3 bg-surface-container-high text-on-surface font-headline-sm uppercase rounded"
                            type="button" wire:click="$set('showIncidentSheet', false)">Cancelar</button>
                    <button class="flex-1 py-3 bg-error text-on-error font-headline-sm uppercase rounded shadow"
                            type="button" wire:click="reportIncident">Enviar reporte</button>
                </div>
            </div>
        </div>
    @endif
</div>
