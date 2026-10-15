<?php

use App\Enums\BusinessStatus;
use App\Enums\DriverAvailability;
use App\Enums\InventoryMovementType;
use App\Enums\OrderStatus;
use App\Exceptions\BusinessSuspendedException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidOrderTransitionException;
use App\Exceptions\OfferNotAllowedException;
use App\Jobs\ExpireOrderOffer;
use App\Models\Business;
use App\Models\DeliveryDriver;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OfferRejectedOrExpired;
use App\Notifications\OrderCancelled;
use App\Notifications\OrderOffered;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

if (! function_exists('makeDriverFor')) {
    function makeDriverFor(Business $business, array $attributes = []): DeliveryDriver
    {
        $user = User::factory()->driver($business)->create();

        return DeliveryDriver::factory()->create(array_merge([
            'user_id' => $user->id,
            'business_id' => $business->id,
        ], $attributes));
    }
}

beforeEach(function () {
    $this->business = Business::factory()->create();
    $this->owner = User::factory()->owner($this->business)->create();
    $this->service = app(OrderService::class);
    $this->actingAs($this->owner);
});

// ── Creation & stock reservation (RF-001) ──────────────────────────

test('creating an order computes the total, reserves stock and stays pending', function () {
    $product = Product::factory()->create([
        'business_id' => $this->business->id,
        'price' => 5000,
        'stock' => 10,
        'min_stock' => 0,
    ]);

    $order = $this->service->createOrder([
        'customer_name' => 'Ana Torres',
        'customer_phone' => '3001234567',
        'delivery_address' => 'Calle 1 # 2-3',
        'payment_method' => 'Efectivo',
        'requires_traceability' => false,
        'items' => [['product_id' => $product->id, 'quantity' => 3]],
    ], $this->owner);

    expect($order->status)->toBe(OrderStatus::Pending)
        ->and($order->total)->toBe(15000.0)
        ->and($order->items)->toHaveCount(1)
        ->and($product->fresh()->stock)->toBe(7)
        ->and(InventoryMovement::where('order_id', $order->id)
            ->where('type', InventoryMovementType::Reserve->value)->exists())->toBeTrue();
});

test('a traceable order also creates its package', function () {
    $product = Product::factory()->create(['business_id' => $this->business->id, 'stock' => 5]);

    $order = $this->service->createOrder([
        'customer_name' => 'Luis Prada',
        'customer_phone' => '3009998877',
        'delivery_address' => 'Carrera 9 # 9-9',
        'payment_method' => 'Nequi',
        'requires_traceability' => true,
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ], $this->owner);

    expect($order->package)->not->toBeNull()
        ->and($order->package->tracking_code)->toStartWith('FL-');
});

test('creating an order with insufficient stock rolls back and creates nothing', function () {
    $product = Product::factory()->create([
        'business_id' => $this->business->id,
        'stock' => 1,
        'min_stock' => 0,
    ]);

    $before = Order::count();

    expect(fn () => $this->service->createOrder([
        'customer_name' => 'Sobre Venta',
        'customer_phone' => '3000000000',
        'delivery_address' => 'Calle Falsa 123',
        'payment_method' => 'Efectivo',
        'items' => [['product_id' => $product->id, 'quantity' => 5]],
    ], $this->owner))->toThrow(InsufficientStockException::class);

    expect(Order::count())->toBe($before)
        ->and($product->fresh()->stock)->toBe(1)
        ->and(InventoryMovement::where('product_id', $product->id)->count())->toBe(0);
});

test('creating an order while the business is suspended is rejected', function () {
    $this->business->update(['status' => BusinessStatus::Suspended]);
    $product = Product::factory()->create(['business_id' => $this->business->id, 'stock' => 5]);

    expect(fn () => $this->service->createOrder([
        'customer_name' => 'Bloqueado',
        'customer_phone' => '3000000000',
        'delivery_address' => 'Calle 1',
        'payment_method' => 'Efectivo',
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ], $this->owner->fresh()))->toThrow(BusinessSuspendedException::class);
});

// ── Transition matrix (SRS §4.1.3) ─────────────────────────────────

test('the transition matrix matches the SRS', function () {
    $allowed = [
        [OrderStatus::Pending, OrderStatus::Offered],
        [OrderStatus::Pending, OrderStatus::Cancelled],
        [OrderStatus::Offered, OrderStatus::Assigned],
        [OrderStatus::Offered, OrderStatus::InTransit],
        [OrderStatus::Offered, OrderStatus::Pending],
        [OrderStatus::Offered, OrderStatus::Cancelled],
        [OrderStatus::Assigned, OrderStatus::InTransit],
        [OrderStatus::Assigned, OrderStatus::Cancelled],
        [OrderStatus::Assigned, OrderStatus::Pending],
        [OrderStatus::InTransit, OrderStatus::Delivered],
        [OrderStatus::InTransit, OrderStatus::Incident],
    ];

    foreach ($allowed as [$from, $to]) {
        expect($this->service->canTransition($from, $to))->toBeTrue();
    }

    $forbidden = [
        [OrderStatus::Pending, OrderStatus::Assigned],
        [OrderStatus::Pending, OrderStatus::InTransit],
        [OrderStatus::InTransit, OrderStatus::Cancelled],
        [OrderStatus::Delivered, OrderStatus::Cancelled],
        [OrderStatus::Cancelled, OrderStatus::Pending],
        [OrderStatus::Incident, OrderStatus::Pending],
        [OrderStatus::Delivered, OrderStatus::InTransit],
    ];

    foreach ($forbidden as [$from, $to]) {
        expect($this->service->canTransition($from, $to))->toBeFalse();
    }
});

// ── Offers (RF-002) ────────────────────────────────────────────────

test('offering an order notifies the driver, opens the 90 second window and schedules the expiry job', function () {
    Notification::fake();
    Queue::fake();

    $driver = makeDriverFor($this->business);
    $order = Order::factory()->create(['business_id' => $this->business->id]);

    $offered = $this->service->offerToDriver($order, $driver, $this->owner);

    expect($offered->status)->toBe(OrderStatus::Offered)
        ->and($offered->delivery_driver_id)->toBe($driver->id)
        ->and($offered->offered_at)->not->toBeNull()
        ->and($offered->offer_expires_at)->not->toBeNull()
        ->and($offered->offer_expires_at->diffInSeconds($offered->offered_at, true))->toBe(90.0);

    Notification::assertSentTo($driver->user, OrderOffered::class);
    Queue::assertPushed(ExpireOrderOffer::class);
});

test('an order cannot be offered to a driver of another business', function () {
    $otherBusiness = Business::factory()->create();
    $foreignDriver = makeDriverFor($otherBusiness);
    $order = Order::factory()->create(['business_id' => $this->business->id]);

    expect(fn () => $this->service->offerToDriver($order, $foreignDriver, $this->owner))
        ->toThrow(OfferNotAllowedException::class);
});

test('an order cannot be offered to an offline driver', function () {
    $driver = makeDriverFor($this->business, ['availability' => DriverAvailability::Offline]);
    $order = Order::factory()->create(['business_id' => $this->business->id]);

    expect(fn () => $this->service->offerToDriver($order, $driver, $this->owner))
        ->toThrow(OfferNotAllowedException::class);
});

test('offers count towards the driver capacity', function () {
    $driver = makeDriverFor($this->business, ['max_active_orders' => 1]);
    $first = Order::factory()->create(['business_id' => $this->business->id]);
    $second = Order::factory()->create(['business_id' => $this->business->id]);

    $this->service->offerToDriver($first, $driver, $this->owner);

    expect(fn () => $this->service->offerToDriver($second, $driver, $this->owner))
        ->toThrow(OfferNotAllowedException::class);
});

test('an order cannot be offered while the business is suspended', function () {
    $this->business->update(['status' => BusinessStatus::Suspended]);
    $driver = makeDriverFor($this->business);
    $order = Order::factory()->create(['business_id' => $this->business->id]);

    expect(fn () => $this->service->offerToDriver($order, $driver, $this->owner))
        ->toThrow(BusinessSuspendedException::class);
});

test('accepting a traceable offer assigns the order and marks the driver busy', function () {
    $driver = makeDriverFor($this->business);
    $order = Order::factory()->withTraceability()->offered($driver)->create([
        'business_id' => $this->business->id,
    ]);

    $accepted = $this->service->acceptOffer($order, $driver);

    expect($accepted->status)->toBe(OrderStatus::Assigned)
        ->and($driver->fresh()->availability)->toBe(DriverAvailability::Busy);
});

test('accepting a simple offer sends the order straight to in transit', function () {
    $driver = makeDriverFor($this->business);
    $order = Order::factory()->offered($driver)->create([
        'business_id' => $this->business->id,
        'requires_traceability' => false,
    ]);

    $accepted = $this->service->acceptOffer($order, $driver);

    expect($accepted->status)->toBe(OrderStatus::InTransit);
});

test('accepting an already expired offer reverts it to pending and throws', function () {
    Notification::fake();

    $driver = makeDriverFor($this->business);
    $order = Order::factory()->offered($driver)->create([
        'business_id' => $this->business->id,
        'offered_at' => now()->subSeconds(120),
        'offer_expires_at' => now()->subSeconds(30),
    ]);

    expect(fn () => $this->service->acceptOffer($order, $driver))
        ->toThrow(InvalidOrderTransitionException::class);

    expect($order->fresh()->status)->toBe(OrderStatus::Pending)
        ->and($order->fresh()->delivery_driver_id)->toBeNull();

    Notification::assertSentTo($this->owner, OfferRejectedOrExpired::class);
});

test('rejecting an offer returns the order to pending and notifies the owner', function () {
    Notification::fake();

    $driver = makeDriverFor($this->business);
    $order = Order::factory()->offered($driver)->create(['business_id' => $this->business->id]);

    $this->service->rejectOffer($order, $driver);

    expect($order->fresh()->status)->toBe(OrderStatus::Pending)
        ->and($order->fresh()->delivery_driver_id)->toBeNull();

    Notification::assertSentTo($this->owner, OfferRejectedOrExpired::class);
});

test('expiring an offer returns it to pending and notifies the owner once', function () {
    Notification::fake();

    $driver = makeDriverFor($this->business);
    $order = Order::factory()->offered($driver)->create([
        'business_id' => $this->business->id,
        'offer_expires_at' => now()->subSeconds(5),
    ]);

    expect($this->service->expireOffer($order))->toBeTrue();
    // Second call is a no-op (the order is no longer offered).
    expect($this->service->expireOffer($order->fresh()))->toBeFalse();

    expect($order->fresh()->status)->toBe(OrderStatus::Pending);
    Notification::assertSentToTimes($this->owner, OfferRejectedOrExpired::class, 1);
});

// ── Cancellation rules (SRS §4.1.4) ────────────────────────────────

test('cancelling a pending order releases the reservation', function () {
    $product = Product::factory()->create(['business_id' => $this->business->id, 'stock' => 10, 'min_stock' => 0]);
    $order = $this->service->createOrder([
        'customer_name' => 'Cliente',
        'customer_phone' => '3001234567',
        'delivery_address' => 'Calle 1',
        'payment_method' => 'Efectivo',
        'items' => [['product_id' => $product->id, 'quantity' => 4]],
    ], $this->owner);

    expect($product->fresh()->stock)->toBe(6);

    $this->service->cancelOrder($order, $this->owner);

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($product->fresh()->stock)->toBe(10)
        ->and(InventoryMovement::where('order_id', $order->id)
            ->where('type', InventoryMovementType::Release->value)->exists())->toBeTrue();
});

test('cancelling an assigned order requires a reason and notifies the driver', function () {
    Notification::fake();

    $product = Product::factory()->create(['business_id' => $this->business->id, 'stock' => 10, 'min_stock' => 0]);
    $driver = makeDriverFor($this->business);
    $order = makeReservedOrder($this->business, $product, OrderStatus::Assigned, 2, $driver);

    expect(fn () => $this->service->cancelOrder($order, $this->owner))
        ->toThrow(InvalidOrderTransitionException::class);

    $this->service->cancelOrder($order, $this->owner, 'El cliente desistió');

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($product->fresh()->stock)->toBe(10);

    Notification::assertSentTo($driver->user, OrderCancelled::class);
});

test('an in-transit order cannot be cancelled from the owner panel', function () {
    $product = Product::factory()->create(['business_id' => $this->business->id, 'stock' => 10]);
    $driver = makeDriverFor($this->business);
    $order = makeReservedOrder($this->business, $product, OrderStatus::InTransit, 1, $driver);

    expect(fn () => $this->service->cancelOrder($order, $this->owner, 'motivo'))
        ->toThrow(InvalidOrderTransitionException::class);
});

test('reassigning an assigned order to pending requires a reason', function () {
    $product = Product::factory()->create(['business_id' => $this->business->id, 'stock' => 10]);
    $driver = makeDriverFor($this->business, ['availability' => DriverAvailability::Busy]);
    $order = makeReservedOrder($this->business, $product, OrderStatus::Assigned, 1, $driver);

    $this->service->reassignToPending($order, $this->owner, 'Repartidor inactivo');

    expect($order->fresh()->status)->toBe(OrderStatus::Pending)
        ->and($order->fresh()->delivery_driver_id)->toBeNull()
        ->and($driver->fresh()->availability)->toBe(DriverAvailability::Available);
});

// ── Delivery, incident and devolution ──────────────────────────────

test('marking an assigned order in transit records the transition', function () {
    $product = Product::factory()->create(['business_id' => $this->business->id, 'stock' => 10]);
    $driver = makeDriverFor($this->business);
    $order = makeReservedOrder($this->business, $product, OrderStatus::Assigned, 1, $driver);

    $this->service->markInTransit($order, $driver->user);

    expect($order->fresh()->status)->toBe(OrderStatus::InTransit)
        ->and(OrderStatusLog::where('order_id', $order->id)->where('to', OrderStatus::InTransit->value)->exists())->toBeTrue();
});

test('delivering an order frees the driver', function () {
    $product = Product::factory()->create(['business_id' => $this->business->id, 'stock' => 10]);
    $driver = makeDriverFor($this->business, ['availability' => DriverAvailability::Busy]);
    $order = makeReservedOrder($this->business, $product, OrderStatus::InTransit, 1, $driver);

    $this->service->markDelivered($order, $driver->user);

    expect($order->fresh()->status)->toBe(OrderStatus::Delivered)
        ->and($driver->fresh()->availability)->toBe(DriverAvailability::Available);
});

test('registering an incident does not return the stock until the devolution is confirmed', function () {
    Notification::fake();

    $product = Product::factory()->create(['business_id' => $this->business->id, 'stock' => 10, 'min_stock' => 0]);
    $driver = makeDriverFor($this->business);
    $order = makeReservedOrder($this->business, $product, OrderStatus::InTransit, 2, $driver);

    expect($product->fresh()->stock)->toBe(8);

    $this->service->registerIncident($order, $this->owner, 'Cliente ausente');

    expect($order->fresh()->status)->toBe(OrderStatus::Incident)
        ->and($product->fresh()->stock)->toBe(8);

    $this->service->confirmDevolution($order, $this->owner);

    expect($product->fresh()->stock)->toBe(10)
        ->and(InventoryMovement::where('order_id', $order->id)
            ->where('type', InventoryMovementType::Return->value)->exists())->toBeTrue();

    // Idempotent: confirming twice does not double the stock.
    $this->service->confirmDevolution($order, $this->owner);
    expect($product->fresh()->stock)->toBe(10);
});

test('confirming a devolution for a non-incident order is rejected', function () {
    $product = Product::factory()->create(['business_id' => $this->business->id, 'stock' => 10]);
    $order = makeReservedOrder($this->business, $product, OrderStatus::Pending, 1);

    expect(fn () => $this->service->confirmDevolution($order, $this->owner))
        ->toThrow(InvalidOrderTransitionException::class);
});
