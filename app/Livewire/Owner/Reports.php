<?php

namespace App\Livewire\Owner;

use App\Enums\OrderStatus;
use App\Models\DeliveryDriver;
use App\Services\ReportService;
use Livewire\Component;

/**
 * Reports page (RF-009): pick the date range and the filters, preview the
 * result set and download the orders or drivers PDF generated with dompdf.
 */
class Reports extends Component
{
    public string $desde = '';

    public string $hasta = '';

    public string $estado = 'todos';

    public string $driver_id = '';

    public string $payment_method = 'todos';

    public function mount(ReportService $reports): void
    {
        $defaults = $reports->defaultFilters();

        $this->desde = $defaults['desde'];
        $this->hasta = $defaults['hasta'];
    }

    public function resetFilters(): void
    {
        $this->reset(['estado', 'driver_id', 'payment_method']);
        $this->mount(app(ReportService::class));
    }

    /**
     * @return array{desde: string, hasta: string, estado: string, driver_id: ?int, payment_method: string}
     */
    public function filters(): array
    {
        return [
            'desde' => $this->desde,
            'hasta' => $this->hasta,
            'estado' => $this->estado,
            'driver_id' => $this->driver_id === '' ? null : (int) $this->driver_id,
            'payment_method' => $this->payment_method,
        ];
    }

    public function render(ReportService $reports)
    {
        $filters = $this->filters();
        $validRange = $this->desde !== '' && $this->hasta !== '' && $this->desde <= $this->hasta;

        $orders = $validRange ? $reports->orders($filters) : collect();
        $drivers = $validRange ? $reports->drivers($filters) : collect();

        return view('livewire.owner.reports', [
            'orders' => $orders,
            'drivers' => $drivers,
            'orderTotal' => (float) $orders->sum('total'),
            'validRange' => $validRange,
            'filters' => $filters,
            'driverOptions' => DeliveryDriver::with('user')->orderBy('id')->get(),
            'paymentMethods' => $reports->paymentMethods(),
            'statuses' => OrderStatus::cases(),
        ]);
    }
}
