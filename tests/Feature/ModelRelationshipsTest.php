<?php

use App\Enums\BusinessStatus;
use App\Enums\DriverAvailability;
use App\Enums\InventoryMovementType;
use App\Enums\OrderStatus;
use App\Enums\PackageScanType;
use App\Enums\PackageStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Business;
use App\Models\DeliveryDriver;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\Package;
use App\Models\PackageScan;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('models cast enums and evaluate business logic correctly', function () {
    $business = Business::factory()->create();
    $owner = User::factory()->owner($business)->create();
    $driverUser = User::factory()->driver($business)->create();

    $driver = DeliveryDriver::factory()->create([
        'user_id' => $driverUser->id,
        'business_id' => $business->id,
        'availability' => DriverAvailability::Available,
        'max_active_orders' => 3,
    ]);

    $product = Product::factory()->create([
        'business_id' => $business->id,
        'stock' => 2,
        'min_stock' => 5,
    ]);

    expect($product->isLowStock())->toBeTrue()
        ->and($product->isOutOfStock())->toBeFalse();

    $product->update(['stock' => 0]);
    expect($product->isOutOfStock())->toBeTrue();

    expect($driver->hasCapacity())->toBeTrue()
        ->and($driver->isAvailable())->toBeTrue();

    // Attach 3 active orders
    Order::factory()->count(3)->create([
        'business_id' => $business->id,
        'status' => OrderStatus::Assigned,
        'delivery_driver_id' => $driver->id,
    ]);

    expect($driver->activeOrdersCount())->toBe(3)
        ->and($driver->hasCapacity())->toBeFalse();
});

test('order and package relationships and status logs link cleanly', function () {
    $business = Business::factory()->create();
    $owner = User::factory()->owner($business)->create();
    $driverUser = User::factory()->driver($business)->create();
    $driver = DeliveryDriver::factory()->create(['user_id' => $driverUser->id, 'business_id' => $business->id]);

    $order = Order::factory()->withTraceability()->create([
        'business_id' => $business->id,
        'delivery_driver_id' => $driver->id,
        'status' => OrderStatus::Assigned,
    ]);

    $package = Package::factory()->create([
        'order_id' => $order->id,
        'tracking_code' => 'PKG-TEST-001',
        'status' => PackageStatus::Pending,
    ]);

    $scan = PackageScan::factory()->pickup()->create([
        'package_id' => $package->id,
        'user_id' => $driverUser->id,
    ]);

    $log = OrderStatusLog::factory()->create([
        'order_id' => $order->id,
        'from' => OrderStatus::Offered->value,
        'to' => OrderStatus::Assigned->value,
        'user_id' => $driverUser->id,
    ]);

    expect($order->package)->not->toBeNull()
        ->and($order->package->tracking_code)->toBe('PKG-TEST-001')
        ->and($package->scans->count())->toBe(1)
        ->and($package->scans->first()->type)->toBe(PackageScanType::Pickup)
        ->and($order->statusLogs->count())->toBe(1)
        ->and($order->statusLogs->first()->to)->toBe(OrderStatus::Assigned->value);
});

test('user canLogin checks business and status rules', function () {
    $superadmin = User::factory()->superadmin()->create();
    expect($superadmin->canLogin())->toBeTrue();

    $activeBusiness = Business::factory()->create(['status' => BusinessStatus::Active]);
    $activeOwner = User::factory()->owner($activeBusiness)->create(['status' => UserStatus::Active]);
    expect($activeOwner->canLogin())->toBeTrue();

    $suspendedBusiness = Business::factory()->suspended()->create();
    $ownerOfSuspended = User::factory()->owner($suspendedBusiness)->create(['status' => UserStatus::Active]);
    // Owner of suspended business cannot log in
    expect($ownerOfSuspended->canLogin())->toBeFalse();

    $suspendedUser = User::factory()->owner($activeBusiness)->suspended()->create();
    expect($suspendedUser->canLogin())->toBeFalse();

    $inactiveUser = User::factory()->owner($activeBusiness)->inactive()->create();
    expect($inactiveUser->canLogin())->toBeFalse();
});
