<?php

use App\Enums\OrderStatus;
use App\Enums\PackageScanType;
use App\Enums\PackageStatus;
use App\Exceptions\InvalidOrderTransitionException;
use App\Models\Business;
use App\Models\DeliveryDriver;
use App\Models\Order;
use App\Models\Package;
use App\Models\PackageScan;
use App\Models\Product;
use App\Models\User;
use App\Services\PackageScanService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

if (! function_exists('traceableAssignedOrder')) {
    /**
     * Assigned order that requires traceability and has its package ready.
     *
     * @return array{0: Order, 1: DeliveryDriver, 2: Package}
     */
    function traceableAssignedOrder(Business $business, Product $product, ?DeliveryDriver $driver = null): array
    {
        $driver ??= (function () use ($business) {
            $user = User::factory()->driver($business)->create();

            return DeliveryDriver::factory()->create(['user_id' => $user->id, 'business_id' => $business->id]);
        })();

        $order = makeReservedOrder($business, $product, OrderStatus::Assigned, 1, $driver);
        $order->update(['requires_traceability' => true]);

        $package = Package::create([
            'order_id' => $order->id,
            'tracking_code' => 'FL-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            'status' => PackageStatus::Pending,
        ]);

        return [$order->fresh(), $driver, $package];
    }
}

beforeEach(function () {
    $this->business = Business::factory()->create();
    $this->product = Product::factory()->create([
        'business_id' => $this->business->id,
        'stock' => 10,
        'min_stock' => 0,
    ]);
    $this->scans = app(PackageScanService::class);
});

test('a pickup scan records the scan, marks the package and moves the order in transit', function () {
    [$order, $driver, $package] = traceableAssignedOrder($this->business, $this->product);

    $this->scans->scanPickup($order, $driver->user, 4.65, -74.05);

    expect($order->fresh()->status)->toBe(OrderStatus::InTransit)
        ->and($package->fresh()->status)->toBe(PackageStatus::PickedUp);

    $scan = PackageScan::where('package_id', $package->id)->first();
    expect($scan)->not->toBeNull()
        ->and($scan->type)->toBe(PackageScanType::Pickup)
        ->and($scan->user_id)->toBe($driver->user_id)
        ->and($scan->latitude)->toBe(4.65)
        ->and($scan->longitude)->toBe(-74.05);
});

test('a delivery scan records the scan, marks the package delivered and completes the order', function () {
    [$order, $driver, $package] = traceableAssignedOrder($this->business, $this->product);
    $this->scans->scanPickup($order, $driver->user);

    $this->scans->scanDelivery($order->fresh(), $driver->user, 4.66, -74.06);

    expect($order->fresh()->status)->toBe(OrderStatus::Delivered)
        ->and($package->fresh()->status)->toBe(PackageStatus::Delivered)
        ->and(PackageScan::where('package_id', $package->id)->count())->toBe(2)
        ->and(PackageScan::where('package_id', $package->id)
            ->where('type', PackageScanType::Delivery->value)->exists())->toBeTrue();
});

test('a pickup scan is rejected when the order is not assigned', function () {
    [$order, $driver] = traceableAssignedOrder($this->business, $this->product);
    $order->update(['status' => OrderStatus::Pending]);

    expect(fn () => $this->scans->scanPickup($order->fresh(), $driver->user))
        ->toThrow(InvalidOrderTransitionException::class)
        ->and(PackageScan::count())->toBe(0);
});

test('a delivery scan is rejected when the order is not in transit', function () {
    [$order, $driver] = traceableAssignedOrder($this->business, $this->product);

    expect(fn () => $this->scans->scanDelivery($order, $driver->user))
        ->toThrow(InvalidOrderTransitionException::class);
});

test('scanning an order without a package is rejected', function () {
    $driver = (function () {
        $user = User::factory()->driver($this->business)->create();

        return DeliveryDriver::factory()->create(['user_id' => $user->id, 'business_id' => $this->business->id]);
    })();

    $order = makeReservedOrder($this->business, $this->product, OrderStatus::Assigned, 1, $driver);

    expect(fn () => $this->scans->scanPickup($order, $driver->user))
        ->toThrow(InvalidOrderTransitionException::class);
});
