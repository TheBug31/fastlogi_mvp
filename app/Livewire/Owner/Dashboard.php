<?php

namespace App\Livewire\Owner;

use App\Enums\DriverAvailability;
use App\Enums\OrderStatus;
use App\Models\DeliveryDriver;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Product;
use Livewire\Component;

/**
 * Owner operations dashboard (RF-005, RF-006): today's KPIs, the live list of
 * active orders, the fleet panel with capacity and the recent activity timeline.
 * It refreshes every 15 seconds to keep the operations board current.
 */
class Dashboard extends Component
{
    public function render()
    {
        $activeStatuses = [
            OrderStatus::Pending->value,
            OrderStatus::Offered->value,
            OrderStatus::Assigned->value,
            OrderStatus::InTransit->value,
        ];

        $drivers = DeliveryDriver::query()
            ->with('user')
            ->withCount(['orders as active_orders_count' => fn ($query) => $query->whereIn('status', [
                OrderStatus::Offered->value,
                OrderStatus::Assigned->value,
                OrderStatus::InTransit->value,
            ])])
            ->get()
            ->sortBy(fn (DeliveryDriver $driver) => match ($driver->availability) {
                DriverAvailability::Available => 0,
                DriverAvailability::Busy => 1,
                DriverAvailability::Offline => 2,
            })
            ->values();

        $kpis = [
            'ventasHoy' => Order::where('status', OrderStatus::Delivered->value)
                ->whereDate('updated_at', today())
                ->sum('total'),
            'pedidosHoy' => Order::whereDate('created_at', today())->count(),
            'activos' => Order::whereIn('status', $activeStatuses)->count(),
            'entregadosHoy' => Order::where('status', OrderStatus::Delivered->value)
                ->whereDate('updated_at', today())
                ->count(),
            'disponibles' => $drivers->where('availability', DriverAvailability::Available)->count(),
            'stockBajo' => Product::where('min_stock', '>', 0)
                ->whereColumn('stock', '<', 'min_stock')
                ->count(),
        ];

        $activeOrders = Order::query()
            ->with('deliveryDriver.user')
            ->whereIn('status', $activeStatuses)
            ->latest()
            ->take(6)
            ->get();

        $timeline = OrderStatusLog::query()
            ->with(['order', 'user'])
            ->whereHas('order')
            ->latest()
            ->take(8)
            ->get();

        return view('livewire.owner.dashboard', [
            'kpis' => $kpis,
            'activeOrders' => $activeOrders,
            'drivers' => $drivers,
            'timeline' => $timeline,
        ]);
    }
}
