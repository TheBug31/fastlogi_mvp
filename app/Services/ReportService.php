<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\DeliveryDriver;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Builds the data set behind the PDF reports (RF-009).
 *
 * Every query runs through the tenant-scoped models, so a report can only ever
 * contain data of the authenticated owner's business (RF-007).
 */
class ReportService
{
    public const ACTIVE_STATUSES = [
        OrderStatus::Offered->value,
        OrderStatus::Assigned->value,
        OrderStatus::InTransit->value,
    ];

    /**
     * @return array{desde: string, hasta: string, estado: string, driver_id: ?int, payment_method: string}
     */
    public function defaultFilters(): array
    {
        return [
            'desde' => now()->startOfMonth()->toDateString(),
            'hasta' => now()->toDateString(),
            'estado' => 'todos',
            'driver_id' => null,
            'payment_method' => 'todos',
        ];
    }

    /**
     * @param  array{desde: string, hasta: string, estado?: string, driver_id?: ?int, payment_method?: string}  $filters
     * @return Collection<int, Order>
     */
    public function orders(array $filters): Collection
    {
        return Order::query()
            ->with('deliveryDriver.user')
            ->whereDate('created_at', '>=', $filters['desde'])
            ->whereDate('created_at', '<=', $filters['hasta'])
            ->when(($filters['estado'] ?? 'todos') !== 'todos', fn (Builder $query) => $query->where('status', $filters['estado']))
            ->when(! empty($filters['driver_id']), fn (Builder $query) => $query->where('delivery_driver_id', $filters['driver_id']))
            ->when(($filters['payment_method'] ?? 'todos') !== 'todos', fn (Builder $query) => $query->where('payment_method', $filters['payment_method']))
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Drivers of the business with their workload and delivered totals in range.
     *
     * @param  array{desde: string, hasta: string}  $filters
     * @return Collection<int, DeliveryDriver>
     */
    public function drivers(array $filters): Collection
    {
        return DeliveryDriver::query()
            ->with('user')
            ->withCount(['orders as delivered_count' => fn (Builder $query) => $this->deliveredInRange($query, $filters)])
            ->withSum(['orders as delivered_total' => fn (Builder $query) => $this->deliveredInRange($query, $filters)], 'total')
            ->withCount(['orders as active_count' => fn (Builder $query) => $query->whereIn('status', self::ACTIVE_STATUSES)])
            ->orderBy('id')
            ->get();
    }

    /**
     * Distinct payment methods used by the business, for the filter dropdown.
     *
     * @return array<int, string>
     */
    public function paymentMethods(): array
    {
        return Order::query()->distinct()->orderBy('payment_method')->pluck('payment_method')->all();
    }

    private function deliveredInRange(Builder $query, array $filters): Builder
    {
        return $query
            ->where('status', OrderStatus::Delivered->value)
            ->whereDate('updated_at', '>=', $filters['desde'])
            ->whereDate('updated_at', '<=', $filters['hasta']);
    }
}
