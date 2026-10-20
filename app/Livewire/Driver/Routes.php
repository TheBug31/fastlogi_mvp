<?php

namespace App\Livewire\Driver;

use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderTransitionException;
use App\Models\Order;
use App\Services\DriverTelemetryService;
use App\Services\OrderService;
use Livewire\Component;

class Routes extends Component
{
    public function mount(DriverTelemetryService $telemetry): void
    {
        $driver = auth()->user()->deliveryDriver;

        if ($driver) {
            // Opening the panel counts as presence ("inicio de sesión", RF-011).
            $telemetry->touch($driver);
        }
    }

    public function acceptOffer(int $orderId, OrderService $orders): void
    {
        $driver = auth()->user()->deliveryDriver;
        $order = $this->findDriverOrder($orderId);

        if (! $driver) {
            $this->addError('offer', 'No tienes un perfil de repartidor.');

            return;
        }

        try {
            $orders->acceptOffer($order, $driver);
        } catch (InvalidOrderTransitionException $e) {
            $this->addError('offer', $e->getMessage());

            return;
        }

        session()->flash('success', 'Pedido aceptado. Revisa el detalle para iniciar la entrega.');
    }

    public function rejectOffer(int $orderId, OrderService $orders): void
    {
        $driver = auth()->user()->deliveryDriver;
        $order = $this->findDriverOrder($orderId);

        if (! $driver) {
            $this->addError('offer', 'No tienes un perfil de repartidor.');

            return;
        }

        try {
            $orders->rejectOffer($order, $driver);
        } catch (InvalidOrderTransitionException $e) {
            $this->addError('offer', $e->getMessage());

            return;
        }

        session()->flash('success', 'Oferta rechazada. El pedido vuelve al negocio.');
    }

    public function render()
    {
        $driver = auth()->user()->deliveryDriver;

        $offers = collect();
        $active = collect();
        $hasInTransit = false;
        $deliveredToday = 0;

        if ($driver) {
            $offers = Order::query()
                ->where('delivery_driver_id', $driver->id)
                ->where('status', OrderStatus::Offered->value)
                ->where(fn ($query) => $query->whereNull('offer_expires_at')
                    ->orWhere('offer_expires_at', '>', now()))
                ->oldest('offered_at')
                ->get();

            $active = Order::query()
                ->where('delivery_driver_id', $driver->id)
                ->whereIn('status', [OrderStatus::Assigned->value, OrderStatus::InTransit->value])
                ->latest('updated_at')
                ->get();

            $hasInTransit = $active->contains(fn (Order $order) => $order->status === OrderStatus::InTransit);

            $deliveredToday = Order::query()
                ->where('delivery_driver_id', $driver->id)
                ->where('status', OrderStatus::Delivered->value)
                ->whereDate('updated_at', today())
                ->count();
        }

        return view('livewire.driver.routes', [
            'driver' => $driver,
            'offers' => $offers,
            'active' => $active,
            'hasInTransit' => $hasInTransit,
            'deliveredToday' => $deliveredToday,
        ]);
    }

    private function findDriverOrder(int $orderId): Order
    {
        // Drivers only ever see their own orders, regardless of the tenant scope.
        return Order::where('delivery_driver_id', auth()->user()->deliveryDriver?->id)
            ->findOrFail($orderId);
    }
}
