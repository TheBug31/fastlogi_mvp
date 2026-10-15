<?php

namespace App\Http\Controllers\Owner;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * PDF reports for the owner panel (RF-009). Both endpoints only export data of
 * the authenticated business because the queries go through the tenant scope.
 */
class ReportController extends Controller
{
    public function __construct(private ReportService $reports) {}

    public function orders(Request $request): Response
    {
        $filters = $this->validatedFilters($request);
        $orders = $this->reports->orders($filters);

        $pdf = Pdf::loadView('reports.orders', [
            'orders' => $orders,
            'filters' => $filters,
            'business' => $request->user()->business,
            'total' => (float) $orders->sum('total'),
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download("reporte-pedidos-{$filters['desde']}_{$filters['hasta']}.pdf");
    }

    public function drivers(Request $request): Response
    {
        $filters = $this->validatedFilters($request);
        $drivers = $this->reports->drivers($filters);

        $pdf = Pdf::loadView('reports.drivers', [
            'drivers' => $drivers,
            'filters' => $filters,
            'business' => $request->user()->business,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download("reporte-repartidores-{$filters['desde']}_{$filters['hasta']}.pdf");
    }

    /**
     * @return array{desde: string, hasta: string, estado: string, driver_id: ?int, payment_method: string}
     */
    private function validatedFilters(Request $request): array
    {
        $statuses = array_map(fn (OrderStatus $status) => $status->value, OrderStatus::cases());

        $data = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
            'estado' => ['nullable', Rule::in(array_merge($statuses, ['todos']))],
            'driver_id' => ['nullable', 'integer'],
            'payment_method' => ['nullable', 'string', 'max:100'],
        ]);

        $defaults = $this->reports->defaultFilters();

        $filters = [
            'desde' => $data['desde'] ?? $defaults['desde'],
            'hasta' => $data['hasta'] ?? $defaults['hasta'],
            'estado' => $data['estado'] ?? 'todos',
            'driver_id' => isset($data['driver_id']) ? (int) $data['driver_id'] : null,
            'payment_method' => $data['payment_method'] ?? 'todos',
        ];

        if ($filters['desde'] > $filters['hasta']) {
            throw ValidationException::withMessages([
                'hasta' => 'La fecha final debe ser igual o posterior a la inicial.',
            ]);
        }

        return $filters;
    }
}
