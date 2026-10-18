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

        // Tiempo promedio de entrega (guía §6): minutos entre la creación y la
        // última actualización de los pedidos entregados. Solo lectura.
        $promedioEntrega = Order::where('status', OrderStatus::Delivered->value)
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as promedio')
            ->value('promedio');

        $kpis = [
            'ventasHoy' => Order::where('status', OrderStatus::Delivered->value)
                ->whereDate('updated_at', today())
                ->sum('total'),
            'promedioEntrega' => $promedioEntrega !== null ? (int) round(((float) $promedioEntrega) / 60) : null,
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

        // Alertas de inventario del dashboard (guía §6). Solo lectura.
        $lowStockProducts = Product::query()
            ->where('min_stock', '>', 0)
            ->whereColumn('stock', '<', 'min_stock')
            ->orderBy('stock')
            ->take(6)
            ->get();

        return view('livewire.owner.dashboard', [
            'kpis' => $kpis,
            'activeOrders' => $activeOrders,
            'drivers' => $drivers,
            'timeline' => $timeline,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}
