<div class="space-y-6">
    <x-ui.toast />

    @error('scan')
        <x-ui.alert type="error">{{ $message }}</x-ui.alert>
    @enderror

    @php
        $steps = [
            \App\Enums\OrderStatus::Assigned->value => 'Asignado',
            \App\Enums\OrderStatus::InTransit->value => 'En camino',
            \App\Enums\OrderStatus::Delivered->value => 'Entregado',
        ];
        $currentIndex = array_search($order->status->value, array_keys($steps), true);
        $isCancelled = $order->status === \App\Enums\OrderStatus::Cancelled;
        $isFinished = in_array($order->status, [
            \App\Enums\OrderStatus::Delivered,
            \App\Enums\OrderStatus::Cancelled,
            \App\Enums\OrderStatus::Incident,
        ], true);
        $reference = $order->package?->tracking_code ?? '#'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT);
    @endphp

    <!-- Migas de pan -->
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Migas de pan">
        <a href="{{ route('driver.routes') }}" wire:navigate class="font-medium text-teal-700 hover:underline">Rutas de hoy</a>
        <span aria-hidden="true">/</span>
        <span class="text-slate-700">{{ $reference }}</span>
    </nav>

    @if ($isCancelled)
        <x-ui.alert type="error">
            Este pedido fue cancelado por el negocio.
        </x-ui.alert>
    @endif

    <!-- Encabezado -->
    <x-ui.card>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Guía de rastreo</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ $reference }}</h1>
            </div>
            <x-ui.status-badge :status="$order->status" />
        </div>

        <dl class="mt-5 grid grid-cols-2 gap-4 border-t border-slate-200 pt-5">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Total a cobrar</dt>
                <dd class="mt-1 text-base font-semibold tabular-nums text-slate-900">{{ format_cop($order->total) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Método de pago</dt>
                <dd class="mt-1 text-base font-semibold text-slate-900">{{ $order->payment_method }}</dd>
            </div>
        </dl>
    </x-ui.card>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <!-- Columna izquierda -->
        <div class="space-y-6 lg:col-span-8">
            <!-- Cliente -->
            <x-ui.card>
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-4">
                    <div class="min-w-0">
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Cliente</p>
                        <p class="mt-1 truncate text-lg font-semibold text-slate-900">{{ $order->customer_name }}</p>
                    </div>
                    <a href="tel:{{ $order->customer_phone }}"
                       class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-teal-200 bg-teal-50 text-teal-700"
                       aria-label="Llamar al cliente">
                        <x-icon name="phone" class="h-5 w-5" />
                    </a>
                </div>
                <div class="mt-4 flex items-start gap-2.5">
                    <x-icon name="map-pin" class="mt-0.5 h-5 w-5 shrink-0 text-teal-600" />
                    <span class="font-medium text-slate-700">{{ $order->delivery_address }}</span>
                </div>
                @if ($order->customer_phone)
                    <p class="mt-1 pl-7 text-sm text-slate-500">{{ $order->customer_phone }}</p>
                @endif
            </x-ui.card>

            <!-- Manifiesto -->
            <x-ui.card>
                <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                    <h2 class="text-base font-semibold text-slate-900">Manifiesto de paquetes</h2>
                    <x-ui.badge color="slate-soft">{{ $order->items->count() }} ítems</x-ui.badge>
                </div>
                <ul class="mt-4 divide-y divide-slate-100">
                    @foreach ($order->items as $item)
                        <li class="flex items-center justify-between gap-3 py-2.5">
                            <span class="flex items-center gap-2.5 text-sm text-slate-700">
                                <x-icon name="check-circle" class="h-5 w-5 shrink-0 text-teal-600" />
                                {{ $item->quantity }} × {{ $item->product?->name }}
                            </span>
                            <span class="text-sm font-medium tabular-nums text-slate-900">{{ format_cop($item->subtotal) }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>

            <!-- Trazabilidad -->
            <x-ui.card>
                <h2 class="border-b border-slate-200 pb-4 text-base font-semibold text-slate-900">Trazabilidad</h2>

                @if ($order->requires_traceability)
                    @if ($order->package?->scans->isNotEmpty())
                        <ol class="mt-4 space-y-4 border-l border-slate-200 pl-5">
                            @foreach ($order->package->scans as $scan)
                                <li class="relative">
                                    <span class="absolute -left-[1.6rem] top-1 h-2.5 w-2.5 rounded-full bg-teal-600" aria-hidden="true"></span>
                                    <p class="text-sm font-medium text-slate-900">{{ $scan->type->label() }}</p>
                                    <p class="text-xs text-slate-500">{{ format_datetime($scan->scanned_at) }}</p>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <p class="mt-4 text-sm text-slate-500">Aún no hay escaneos registrados.</p>
                    @endif
                @else
                    <p class="mt-4 text-sm text-slate-500">Este pedido no tiene trazabilidad.</p>
                @endif
            </x-ui.card>
        </div>

        <!-- Columna derecha -->
        <div class="lg:col-span-4">
            <x-ui.card class="lg:sticky lg:top-24">
                <h2 class="text-base font-semibold text-slate-900">Estado y pasos</h2>

                <ol class="mt-5 space-y-4">
                    @foreach ($steps as $value => $label)
                        @php
                            $index = array_search($value, array_keys($steps), true);
                            $done = $currentIndex !== false && $index < $currentIndex;
                            $current = $currentIndex !== false && $index === $currentIndex;
                        @endphp
                        <li class="flex items-center gap-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full border-2 {{ $done ? 'border-teal-600 bg-teal-600 text-white' : ($current ? 'border-teal-600 text-teal-700 ring-4 ring-teal-600/20' : 'border-slate-300 text-slate-300') }}">
                                @if ($done)
                                    <x-icon name="check" class="h-3.5 w-3.5" />
                                @endif
                            </span>
                            <span class="text-sm font-medium {{ $done || $current ? 'text-slate-900' : 'text-slate-400' }}">{{ $label }}</span>
                        </li>
                    @endforeach
                </ol>

                <div class="mt-6 space-y-3 border-t border-slate-200 pt-5">
                    @if ($order->status === \App\Enums\OrderStatus::Assigned)
                        <x-ui.button class="w-full" type="button" wire:click="scanPickup">
                            <x-icon name="{{ $order->requires_traceability ? 'qr-code' : 'truck' }}" class="h-5 w-5" />
                            Registrar recogida
                        </x-ui.button>
                    @endif

                    @if ($order->status === \App\Enums\OrderStatus::InTransit)
                        <x-ui.button class="w-full" type="button" wire:click="scanDelivery"
                                     wire:confirm="¿Confirmas la entrega de este pedido?">
                            <x-icon name="check-circle" class="h-5 w-5" />
                            Marcar como entregado
                        </x-ui.button>
                        <x-ui.button variant="danger" class="w-full" type="button" wire:click="openIncidentSheet">
                            <x-icon name="exclamation-triangle" class="h-5 w-5" />
                            Reportar incidencia
                        </x-ui.button>
                    @endif

                    @if ($isFinished && ! $isCancelled)
                        <x-ui.alert type="info">
                            Este pedido ya no requiere acciones. Estado final: <strong>{{ $order->status->label() }}</strong>.
                        </x-ui.alert>
                    @endif

                    <x-ui.button variant="secondary" href="{{ route('driver.routes') }}" class="w-full">
                        <x-icon name="arrow-left" class="h-5 w-5" />
                        Volver a rutas
                    </x-ui.button>
                </div>
            </x-ui.card>
        </div>
    </div>

    <!-- Modal de incidencia -->
    @if ($showIncidentSheet)
        <x-ui.modal property="showIncidentSheet" title="Reportar incidencia" size="sm">
            <x-ui.select name="incidentReason" label="Motivo del reporte" required wire:model="incidentReason">
                <option>Cliente ausente / No contesta</option>
                <option>Dirección incorrecta o incompleta</option>
                <option>Paquete dañado en tránsito</option>
                <option>Negocio cerrado</option>
            </x-ui.select>

            <x-ui.textarea name="incidentNotes" label="Observaciones adicionales"
                           placeholder="Describe la novedad..." rows="3" wire:model="incidentNotes" />

            <x-slot:footer>
                <div class="flex items-center justify-end gap-3">
                    <x-ui.button variant="secondary" type="button" wire:click="$set('showIncidentSheet', false)">
                        Cancelar
                    </x-ui.button>
                    <x-ui.button variant="danger-solid" type="button" wire:click="reportIncident">
                        Enviar reporte
                    </x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.modal>
    @endif
</div>