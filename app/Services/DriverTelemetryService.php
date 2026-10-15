<?php

namespace App\Services;

use App\Enums\DriverAvailability;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\DeliveryDriver;
use App\Models\Scopes\BusinessScope;
use App\Notifications\DriverInactive;
use Illuminate\Database\Eloquent\Collection;

/**
 * Records driver GPS pings and detects drivers that stopped reporting while they
 * still have accepted orders (RF-011, CU17).
 */
class DriverTelemetryService
{
    /**
     * Store a location ping and restore the driver's availability by load.
     */
    public function record(DeliveryDriver $driver, float $latitude, float $longitude): DeliveryDriver
    {
        $driver->latitude = $latitude;
        $driver->longitude = $longitude;
        $driver->last_seen_at = now();
        $driver->save();

        return $this->restoreAvailability($driver);
    }

    /**
     * Refresh the driver's presence without a location, e.g. on panel load.
     */
    public function touch(DeliveryDriver $driver): DeliveryDriver
    {
        $driver->last_seen_at = now();
        $driver->save();

        return $this->restoreAvailability($driver);
    }

    /**
     * Reconnecting (or a fresh ping) always resumes the availability that matches
     * the current workload, overriding an inactivity `offline` (RF-011).
     */
    private function restoreAvailability(DeliveryDriver $driver): DeliveryDriver
    {
        $hasActive = $driver->orders()
            ->whereIn('status', [OrderStatus::Assigned->value, OrderStatus::InTransit->value])
            ->exists();

        $driver->availability = $hasActive
            ? DriverAvailability::Busy
            : DriverAvailability::Available;

        $driver->save();

        return $driver;
    }

    /**
     * Drivers with accepted orders whose last ping is older than the business
     * threshold. Drivers that never reported are not flagged (RF-011).
     *
     * @return Collection<int, DeliveryDriver>
     */
    public function inactiveDrivers(): Collection
    {
        return DeliveryDriver::withoutGlobalScope(BusinessScope::class)
            ->whereNotNull('last_seen_at')
            ->where('availability', '!=', DriverAvailability::Offline->value)
            ->whereHas('orders', fn ($query) => $query->whereIn('status', [
                OrderStatus::Assigned->value,
                OrderStatus::InTransit->value,
            ]))
            ->with(['business', 'user'])
            ->get()
            ->filter(fn (DeliveryDriver $driver) => $driver->last_seen_at
                ->lt(now()->subMinutes($driver->business->inactivity_threshold_minutes)))
            ->values();
    }

    /**
     * Mark a driver offline and warn the owners once per inactivity episode.
     */
    public function flagInactive(DeliveryDriver $driver): void
    {
        if ($driver->availability === DriverAvailability::Offline) {
            return;
        }

        $pending = $driver->orders()
            ->whereIn('status', [OrderStatus::Assigned->value, OrderStatus::InTransit->value])
            ->count();

        $driver->update(['availability' => DriverAvailability::Offline]);

        $driver->business->users()
            ->where('role', UserRole::Owner->value)
            ->get()
            ->each(fn ($owner) => $owner->notify(new DriverInactive(
                $driver->id,
                $driver->user?->name ?? 'Repartidor',
                $driver->business->inactivity_threshold_minutes,
                $pending,
            )));
    }
}
