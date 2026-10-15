<?php

use App\Enums\BusinessStatus;
use App\Enums\InventoryMovementType;
use App\Enums\OrderStatus;
use App\Enums\UserStatus;
use App\Jobs\CloseGraceOrders;
use App\Models\Business;
use App\Models\DeliveryDriver;
use App\Models\InventoryMovement;
use App\Models\OrderStatusLog;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderCancelled;
use App\Services\BusinessSuspensionService;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(BusinessSuspensionService::class);
    $this->superadmin = User::factory()->superadmin()->create();
});

function driverFor(Business $business): array
{
    $user = User::factory()->driver($business)->create();
    $driver = DeliveryDriver::factory()->create([
        'user_id' => $user->id,
        'business_id' => $business->id,
    ]);

    return [$user, $driver];
}

test('suspending a business blocks the owner and marks the business as suspended', function () {
    $business = Business::factory()->create();
    $owner = User::factory()->owner($business)->create();

    $this->service->suspend($business, $this->superadmin);

    expect($business->fresh()->isSuspended())->toBeTrue()
        ->and($business->fresh()->suspended_at)->not->toBeNull()
        ->and($owner->fresh()->status)->toBe(UserStatus::Suspended);
});

test('suspending cancels offered orders, releases their reservation and logs the change', function () {
    $business = Business::factory()->create();
    User::factory()->owner($business)->create();
    $product = Product::factory()->create(['business_id' => $business->id, 'stock' => 10]);
    [, $driver] = driverFor($business);

    $order = makeReservedOrder($business, $product, OrderStatus::Offered, 2, $driver);
    expect($product->fresh()->stock)->toBe(8);

    $this->service->suspend($business, $this->superadmin);

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($order->fresh()->cancel_reason)->toBe(BusinessSuspensionService::REASON_SUSPENDED)
        ->and($product->fresh()->stock)->toBe(10)
        ->and(InventoryMovement::where('order_id', $order->id)
            ->where('type', InventoryMovementType::Release->value)
            ->exists())->toBeTrue()
        ->and(OrderStatusLog::where('order_id', $order->id)
            ->where('to', OrderStatus::Cancelled->value)
            ->where('reason', BusinessSuspensionService::REASON_SUSPENDED)
            ->exists())->toBeTrue();
});

test('suspending freezes pending orders and keeps assigned/in_transit orders for the grace window', function () {
    $business = Business::factory()->create();
    $product = Product::factory()->create(['business_id' => $business->id, 'stock' => 20]);
    [, $driver] = driverFor($business);

    $pending = makeReservedOrder($business, $product, OrderStatus::Pending, 1, $driver);
    $assigned = makeReservedOrder($business, $product, OrderStatus::Assigned, 1, $driver);
    $inTransit = makeReservedOrder($business, $product, OrderStatus::InTransit, 1, $driver);

    $this->service->suspend($business, $this->superadmin);

    expect($pending->fresh()->status)->toBe(OrderStatus::Pending)
        ->and($assigned->fresh()->status)->toBe(OrderStatus::Assigned)
        ->and($inTransit->fresh()->status)->toBe(OrderStatus::InTransit);
});

test('suspending notifies the driver of the cancelled offered order', function () {
    Notification::fake();

    $business = Business::factory()->create();
    $product = Product::factory()->create(['business_id' => $business->id, 'stock' => 10]);
    [$driverUser, $driver] = driverFor($business);

    makeReservedOrder($business, $product, OrderStatus::Offered, 1, $driver);

    $this->service->suspend($business, $this->superadmin);

    Notification::assertSentTo($driverUser, OrderCancelled::class);
});

test('suspending is idempotent and never releases stock twice', function () {
    $business = Business::factory()->create();
    $product = Product::factory()->create(['business_id' => $business->id, 'stock' => 10]);
    [, $driver] = driverFor($business);

    makeReservedOrder($business, $product, OrderStatus::Offered, 3, $driver);

    $this->service->suspend($business, $this->superadmin);
    $this->service->suspend($business->fresh(), $this->superadmin);

    expect($product->fresh()->stock)->toBe(10);
});

test('reactivating restores the business and the owner', function () {
    $business = Business::factory()->suspended()->create();
    $owner = User::factory()->owner($business)->suspended()->create();

    $this->service->reactivate($business);

    expect($business->fresh()->status)->toBe(BusinessStatus::Active)
        ->and($business->fresh()->suspended_at)->toBeNull()
        ->and($owner->fresh()->status)->toBe(UserStatus::Active);
});

test('closing the grace window cancels assigned orders and turns in_transit orders into incidents', function () {
    $business = Business::factory()->suspended()->create();

    $assignedProduct = Product::factory()->create(['business_id' => $business->id, 'stock' => 10]);
    $inTransitProduct = Product::factory()->create(['business_id' => $business->id, 'stock' => 10]);
    [, $driver] = driverFor($business);

    $assigned = makeReservedOrder($business, $assignedProduct, OrderStatus::Assigned, 2, $driver);
    $inTransit = makeReservedOrder($business, $inTransitProduct, OrderStatus::InTransit, 2, $driver);

    $closed = $this->service->closeGraceOrders($business, $this->superadmin);

    expect($closed)->toBe(2)
        ->and($assigned->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($assignedProduct->fresh()->stock)->toBe(10)
        // in_transit keeps its reservation: stock returns only on a confirmed devolución
        ->and($inTransit->fresh()->status)->toBe(OrderStatus::Incident)
        ->and($inTransitProduct->fresh()->stock)->toBe(8);
});

test('closing the grace window does nothing for an active business', function () {
    $business = Business::factory()->create();
    $product = Product::factory()->create(['business_id' => $business->id, 'stock' => 10]);
    [, $driver] = driverFor($business);
    $assigned = makeReservedOrder($business, $product, OrderStatus::Assigned, 1, $driver);

    $closed = $this->service->closeGraceOrders($business, $this->superadmin);

    expect($closed)->toBe(0)
        ->and($assigned->fresh()->status)->toBe(OrderStatus::Assigned);
});

test('only grace windows older than 24 hours are closed automatically', function () {
    $expired = Business::factory()->suspended()->create(['suspended_at' => now()->subHours(25)]);
    $recent = Business::factory()->suspended()->create(['suspended_at' => now()->subHours(1)]);

    $expiredProduct = Product::factory()->create(['business_id' => $expired->id, 'stock' => 10]);
    $recentProduct = Product::factory()->create(['business_id' => $recent->id, 'stock' => 10]);
    [, $driverExpired] = driverFor($expired);
    [, $driverRecent] = driverFor($recent);

    $expiredOrder = makeReservedOrder($expired, $expiredProduct, OrderStatus::Assigned, 1, $driverExpired);
    $recentOrder = makeReservedOrder($recent, $recentProduct, OrderStatus::Assigned, 1, $driverRecent);

    $total = $this->service->closeExpiredGraceWindows();

    expect($total)->toBe(1)
        ->and($expiredOrder->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($recentOrder->fresh()->status)->toBe(OrderStatus::Assigned);
});

test('the CloseGraceOrders job closes an expired grace window', function () {
    $business = Business::factory()->suspended()->create(['suspended_at' => now()->subHours(25)]);
    $product = Product::factory()->create(['business_id' => $business->id, 'stock' => 10]);
    [, $driver] = driverFor($business);
    $order = makeReservedOrder($business, $product, OrderStatus::Assigned, 1, $driver);

    (new CloseGraceOrders($business))->handle($this->service);

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($order->fresh()->cancel_reason)->toBe(BusinessSuspensionService::REASON_GRACE_EXPIRED);
});

test('releasing a reservation is idempotent', function () {
    $business = Business::factory()->create();
    $product = Product::factory()->create(['business_id' => $business->id, 'stock' => 10]);
    $order = makeReservedOrder($business, $product, OrderStatus::Pending, 4);

    $inventory = app(InventoryService::class);
    $inventory->releaseForOrder($order, $this->superadmin);
    $inventory->releaseForOrder($order, $this->superadmin);

    expect($product->fresh()->stock)->toBe(10)
        ->and(InventoryMovement::where('order_id', $order->id)
            ->where('type', InventoryMovementType::Release->value)
            ->count())->toBe(1);
});
