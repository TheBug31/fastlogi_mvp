<?php

namespace App\Services;

use App\Enums\DriverAvailability;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Business;
use App\Models\DeliveryDriver;
use App\Models\User;
use InvalidArgumentException;

/**
 * Owner-facing management of the delivery fleet (RF-005 / CU07).
 *
 * The owner can onboard a driver (user + profile in a single transaction),
 * edit the profile, adjust the per-driver capacity limit (1..8) and toggle the
 * manual `offline` state. The `busy`/`available` state stays automatic and is
 * recomputed by {@see DriverAvailabilityService}.
 */
class DriverManagementService
{
    public const MIN_ACTIVE_ORDERS = 1;

    public const MAX_ACTIVE_ORDERS = 8;

    public function __construct(private DriverAvailabilityService $availability) {}

    /**
     * @param  array{name: string, email: string, phone: string, password: string, max_active_orders?: int|string}  $data
     */
    public function createDriver(Business $business, array $data): DeliveryDriver
    {
        $max = $this->normalizeCapacity($data['max_active_orders'] ?? 3);

        $user = User::create([
            'business_id' => $business->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'role' => UserRole::Driver,
            'status' => UserStatus::Active,
        ]);

        return DeliveryDriver::create([
            'user_id' => $user->id,
            'business_id' => $business->id,
            'availability' => DriverAvailability::Available,
            'max_active_orders' => $max,
            'latitude' => null,
            'longitude' => null,
            'last_seen_at' => null,
        ]);
    }

    /**
     * @param  array{name: string, email: string, phone: string, max_active_orders?: int|string}  $data
     */
    public function updateDriver(DeliveryDriver $driver, array $data): DeliveryDriver
    {
        $driver->user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);

        $driver->update([
            'max_active_orders' => $this->normalizeCapacity($data['max_active_orders'] ?? $driver->max_active_orders),
        ]);

        return $driver->refresh();
    }

    public function updateCapacity(DeliveryDriver $driver, int $maxActiveOrders): DeliveryDriver
    {
        $driver->update(['max_active_orders' => $this->normalizeCapacity($maxActiveOrders)]);

        return $driver->refresh();
    }

    /**
     * Manual availability toggle. `offline` is always allowed; going back online
     * recomputes `available`/`busy` from the current workload (RF-005).
     */
    public function setAvailability(DeliveryDriver $driver, DriverAvailability $availability): DeliveryDriver
    {
        if ($availability === DriverAvailability::Offline) {
            $driver->update(['availability' => DriverAvailability::Offline]);

            return $driver->refresh();
        }

        $driver->update(['availability' => DriverAvailability::Available]);

        return $this->availability->refresh($driver);
    }

    private function normalizeCapacity(int|string $value): int
    {
        $max = (int) $value;

        if ($max < self::MIN_ACTIVE_ORDERS || $max > self::MAX_ACTIVE_ORDERS) {
            throw new InvalidArgumentException(
                'El límite de pedidos activos debe estar entre '.self::MIN_ACTIVE_ORDERS.' y '.self::MAX_ACTIVE_ORDERS.'.',
            );
        }

        return $max;
    }
}
