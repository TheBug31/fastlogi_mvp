<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PackageScanType;
use App\Enums\PackageStatus;
use App\Exceptions\InvalidOrderTransitionException;
use App\Models\Order;
use App\Models\PackageScan;
use App\Models\Scopes\BusinessScope;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Registers the pickup and delivery scans that build the package timeline
 * (RF-006, CU14 and CU15). Each scan also advances the order state machine.
 */
class PackageScanService
{
    public function __construct(private OrderService $orders) {}

    /**
     * Driver scans the package at the point of pickup: `assigned` → `in_transit`.
     */
    public function scanPickup(Order $order, User $driver, ?float $latitude = null, ?float $longitude = null): Order
    {
        return $this->scan($order, $driver, PackageScanType::Pickup, $latitude, $longitude);
    }

    /**
     * Driver scans the package at the delivery point: `in_transit` → `delivered`.
     */
    public function scanDelivery(Order $order, User $driver, ?float $latitude = null, ?float $longitude = null): Order
    {
        return $this->scan($order, $driver, PackageScanType::Delivery, $latitude, $longitude);
    }

    private function scan(
        Order $order,
        User $driver,
        PackageScanType $type,
        ?float $latitude,
        ?float $longitude,
    ): Order {
        return DB::transaction(function () use ($order, $driver, $type, $latitude, $longitude) {
            $locked = Order::withoutGlobalScope(BusinessScope::class)
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $expected = $type === PackageScanType::Pickup
                ? OrderStatus::Assigned
                : OrderStatus::InTransit;

            if ($locked->status !== $expected) {
                throw new InvalidOrderTransitionException(match ($type) {
                    PackageScanType::Pickup => 'Solo se puede escanear la recogida de un pedido asignado.',
                    PackageScanType::Delivery => 'Solo se puede escanear la entrega de un pedido en tránsito.',
                });
            }

            $package = $locked->package()->lockForUpdate()->first();

            if (! $package) {
                throw new InvalidOrderTransitionException('El pedido no tiene un paquete de trazabilidad.');
            }

            // Idempotency: never duplicate the same scan type for a package.
            $already = $package->scans()->where('type', $type->value)->exists();

            if (! $already) {
                PackageScan::create([
                    'package_id' => $package->id,
                    'type' => $type,
                    'user_id' => $driver->id,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'scanned_at' => now(),
                ]);
            }

            $package->update([
                'status' => $type === PackageScanType::Pickup
                    ? PackageStatus::PickedUp
                    : PackageStatus::Delivered,
            ]);

            if ($type === PackageScanType::Pickup) {
                $this->orders->markInTransit($locked, $driver);
            } else {
                $this->orders->markDelivered($locked, $driver);
            }

            return $locked->fresh(['package.scans']);
        });
    }
}
