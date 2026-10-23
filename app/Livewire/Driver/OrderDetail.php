<?php

namespace App\Livewire\Driver;

use App\Exceptions\InvalidOrderTransitionException;
use App\Models\Order;
use App\Services\DriverTelemetryService;
use App\Services\OrderService;
use App\Services\PackageScanService;
use Livewire\Component;

class OrderDetail extends Component
{
    public string $orderId = '';

    public string $incidentReason = 'Cliente ausente / No contesta';

    public string $incidentNotes = '';

    public bool $showIncidentSheet = false;

    public function mount(string $orderId, DriverTelemetryService $telemetry): void
    {
        $this->orderId = $orderId;

        $driver = auth()->user()->deliveryDriver;

        if ($driver) {
            $telemetry->touch($driver);
        }

        $this->order();
    }

    public function scanPickup(PackageScanService $scans, OrderService $orders): void
    {
        $user = auth()->user();
        $driver = $user->deliveryDriver;
        $order = $this->order();

        try {
            if ($order->requires_traceability) {
                $scans->scanPickup($order, $user, $driver?->latitude, $driver?->longitude);
            } else {
                $orders->markInTransit($order, $user);
            }
        } catch (InvalidOrderTransitionException $e) {
            $this->addError('scan', $e->getMessage());

            return;
        }

        session()->flash('success', 'Recogida confirmada. Pedido en tránsito.');
    }

    public function scanDelivery(PackageScanService $scans, OrderService $orders): void
    {
        $user = auth()->user();
        $driver = $user->deliveryDriver;
        $order = $this->order();

        try {
            if ($order->requires_traceability) {
                $scans->scanDelivery($order, $user, $driver?->latitude, $driver?->longitude);
            } else {
                $orders->markDelivered($order, $user);
            }
        } catch (InvalidOrderTransitionException $e) {
            $this->addError('scan', $e->getMessage());

            return;
        }

        session()->flash('success', 'Entrega confirmada. ¡Buen trabajo!');
    }

    public function openIncidentSheet(): void
    {
        $this->resetValidation();
        $this->showIncidentSheet = true;
    }

    public function reportIncident(OrderService $orders): void
    {
        $this->validate([
            'incidentReason' => ['required', 'string', 'min:3'],
        ], [], ['incidentReason' => 'motivo']);

        $order = $this->order();

        $reason = $this->incidentNotes !== ''
            ? $this->incidentReason.' — '.$this->incidentNotes
            : $this->incidentReason;

        try {
            $orders->registerIncident($order, auth()->user(), $reason);
        } catch (InvalidOrderTransitionException $e) {
            $this->addError('incidentReason', $e->getMessage());

            return;
        }

        $this->showIncidentSheet = false;
        $this->incidentNotes = '';
        session()->flash('success', 'Incidencia reportada al negocio.');
    }

    public function render()
    {
        return view('livewire.driver.order-detail', [
            'order' => $this->order(),
            'driver' => auth()->user()->deliveryDriver,
        ]);
    }

    private function order(): Order
    {
        return Order::where('delivery_driver_id', auth()->user()->deliveryDriver?->id)
            ->with(['items.product', 'package.scans', 'business'])
            ->whereKeyOrSlug($this->orderId)
            ->firstOrFail();
    }
}
