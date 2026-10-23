<?php

namespace App\Livewire\Driver;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\DriverTelemetryService;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Delivery history for the signed-in driver (read only).
 *
 * Lists the finished orders that were assigned to the driver — delivered,
 * cancelled and incidents — with a status filter and daily KPIs.
 */
class History extends Component
{
    use WithPagination;

    public string $status = 'todos';

    /** Start/end of the period, `Y-m-d` (both inclusive). */
    public string $desde = '';

    public string $hasta = '';

    public function mount(DriverTelemetryService $telemetry): void
    {
        $driver = auth()->user()->deliveryDriver;

        if ($driver) {
            // Opening the panel counts as presence ("inicio de sesión", RF-011).
            $telemetry->touch($driver);
        }

        if ($this->desde === '') {
            $this->desde = today()->subDays(29)->toDateString();
        }

        if ($this->hasta === '') {
            $this->hasta = today()->toDateString();
        }
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingDesde(): void
    {
        $this->resetPage();
    }

    public function updatingHasta(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['status']);
        $this->desde = today()->subDays(29)->toDateString();
        $this->hasta = today()->toDateString();
        $this->resetPage();
    }

    public function render()
    {
        $driver = auth()->user()->deliveryDriver;

        $orders = collect();
        $validRange = $this->desde !== '' && $this->hasta !== '' && $this->desde <= $this->hasta;

        $base = fn () => Order::query()
            ->where('delivery_driver_id', $driver?->id)
            ->whereIn('status', [OrderStatus::Delivered->value, OrderStatus::Cancelled->value, OrderStatus::Incident->value]);

        if ($driver && $validRange) {
            $orders = $base()
                ->when($this->status !== 'todos', fn ($query) => $query->where('status', $this->status))
                ->whereDate('updated_at', '>=', $this->desde)
                ->whereDate('updated_at', '<=', $this->hasta)
                ->withCount('items')
                ->orderByDesc('updated_at')
                ->paginate(15);
        }

        return view('livewire.driver.history', [
            'driver' => $driver,
            'orders' => $orders,
            'validRange' => $validRange,
            'statuses' => OrderStatus::cases(),
            'kpis' => [
                'entregadas' => $driver ? $base()->where('status', OrderStatus::Delivered->value)->count() : 0,
                'hoy' => $driver ? $base()->where('status', OrderStatus::Delivered->value)->whereDate('updated_at', today())->count() : 0,
                'incidencias' => $driver ? $base()->where('status', OrderStatus::Incident->value)->count() : 0,
                'canceladas' => $driver ? $base()->where('status', OrderStatus::Cancelled->value)->count() : 0,
            ],
        ]);
    }
}
