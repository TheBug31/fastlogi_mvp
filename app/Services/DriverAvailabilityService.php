<?php

namespace App\Services;

use App\Enums\DriverAvailability;
use App\Enums\OrderStatus;
use App\Models\DeliveryDriver;
use App\Models\Scopes\BusinessScope;
use Illuminate\Database\Eloquent\Collection;

/**
 * Keeps a driver's availability in sync with their active workload (RF-005).
 *
 * A driver is `busy` while they have at least one `assigned` or `in_transit`
 * order and returns to `available` when none is left. `offline` is a manual
 * state and is never overwritten automatically.
 */
class DriverAvailabilityService
{
    /**
     * Recompute and persist the availability of a driver.
     */
    public function refresh(DeliveryDriver $driver): DeliveryDriver
    {
        $driver->refresh();

        if ($driver->availability === DriverAvailability::Offline) {
            return $driver;
        }

        $hasActive = $driver->orders()
            ->whereIn('status', [
                OrderStatus::Assigned->value,
                OrderStatus::InTransit->value,
            ])
            ->exists();

        $driver->availability = $hasActive
            ? DriverAvailability::Busy
            : DriverAvailability::Available;

        $driver->save();

        return $driver;
    }

    /**
     * Drivers of the given business that can receive a new offer. Pending offers
     * count towards the driver's capacity (RF-002, RF-005).
     *
     * @return Collection<int, DeliveryDriver>
     */
    public function candidates(int $businessId)
    {
        return DeliveryDriver::withoutGlobalScope(BusinessScope::class)
            ->where('business_id', $businessId)
            ->where('availability', '!=', DriverAvailability::Offline->value)
            ->withCount(['orders as active_orders_count' => fn ($query) => $query->whereIn('status', [
                OrderStatus::Offered->value,
                OrderStatus::Assigned->value,
                OrderStatus::InTransit->value,
            ])])
            ->get()
            ->filter(fn (DeliveryDriver $driver) => $driver->active_orders_count < $driver->max_active_orders)
            ->values();
    }
}
