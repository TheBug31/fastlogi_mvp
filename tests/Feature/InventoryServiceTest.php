<?php

use App\Enums\InventoryMovementType;
use App\Enums\OrderStatus;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\ProductInUseException;
use App\Models\Business;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Services\InventoryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->business = Business::factory()->create();
    $this->owner = User::factory()->owner($this->business)->create();
    $this->service = app(InventoryService::class);
    $this->actingAs($this->owner);
});

test('creating a product below its minimum fires the low stock alert', function () {
    Notification::fake();

    $product = $this->service->createProduct([
        'name' => 'Aceite',
        'price' => 12000,
        'stock' => 3,
        'min_stock' => 10,
        'unit' => 'unidad',
    ], $this->owner);

    expect($product->low_stock_alerted)->toBeTrue()
        ->and($product->stock)->toBe(3);

    Notification::assertSentTo($this->owner, LowStockAlert::class);
});

test('creating a product at or above its minimum does not fire an alert', function () {
    Notification::fake();

    $product = $this->service->createProduct([
        'name' => 'Arroz',
        'price' => 4000,
        'stock' => 20,
        'min_stock' => 10,
        'unit' => 'unidad',
    ], $this->owner);

    expect($product->low_stock_alerted)->toBeFalse();

    Notification::assertNothingSent();
});

test('crossing the minimum fires the low stock alert only once', function () {
    Notification::fake();

    $product = Product::factory()->create([
        'business_id' => $this->business->id,
        'stock' => 8,
        'min_stock' => 5,
    ]);

    $this->service->adjustStock($product, 4, $this->owner); // crosses down (8 -> 4)
    $this->service->adjustStock($product->fresh(), 2, $this->owner); // still below, no repeat

    expect($product->fresh()->low_stock_alerted)->toBeTrue();

    Notification::assertSentToTimes($this->owner, LowStockAlert::class, 1);
});

test('reaching zero fires the out of stock alert once in addition to the low stock alert', function () {
    Notification::fake();

    $product = Product::factory()->create([
        'business_id' => $this->business->id,
        'stock' => 6,
        'min_stock' => 5,
    ]);

    $this->service->adjustStock($product, 0, $this->owner); // crosses min and reaches zero
    $this->service->adjustStock($product->fresh(), 0, $this->owner); // no changes, no repeat

    $product->refresh();
    expect($product->out_of_stock_alerted)->toBeTrue();

    Notification::assertSentTo($this->owner, LowStockAlert::class, function (LowStockAlert $notification) {
        return $notification->kind === LowStockAlert::OUT_OF_STOCK;
    });
    Notification::assertSentToTimes($this->owner, LowStockAlert::class, 2);
});

test('restocking re-arms the alerts so a later crossing fires again', function () {
    Notification::fake();

    $product = Product::factory()->create([
        'business_id' => $this->business->id,
        'stock' => 4,
        'min_stock' => 5,
        'low_stock_alerted' => true,
    ]);

    $this->service->restock($product, 10, $this->owner); // 14, re-arms

    $product->refresh();
    expect($product->low_stock_alerted)->toBeFalse()
        ->and($product->out_of_stock_alerted)->toBeFalse()
        ->and($product->stock)->toBe(14);

    $this->service->adjustStock($product, 3, $this->owner); // crosses again

    Notification::assertSentToTimes($this->owner, LowStockAlert::class, 1);
});

test('stock movements record the type, quantity and actor', function () {
    $product = Product::factory()->create([
        'business_id' => $this->business->id,
        'stock' => 10,
        'min_stock' => 0,
    ]);

    $this->service->restock($product, 5, $this->owner);
    $this->service->adjustStock($product->fresh(), 12, $this->owner);

    $movements = InventoryMovement::where('product_id', $product->id)->orderBy('id')->get();

    expect($movements)->toHaveCount(2)
        ->and($movements[0]->type)->toBe(InventoryMovementType::Restock)
        ->and($movements[0]->quantity)->toBe(5)
        ->and($movements[1]->type)->toBe(InventoryMovementType::Adjustment)
        ->and($movements[1]->quantity)->toBe(-3)
        ->and($movements[0]->user_id)->toBe($this->owner->id);
});

test('reserving stock decrements availability and records a reserve movement', function () {
    $product = Product::factory()->create([
        'business_id' => $this->business->id,
        'stock' => 10,
        'min_stock' => 0,
    ]);
    $order = Order::factory()->create(['business_id' => $this->business->id]);

    $this->service->reserveForOrder($order, [
        ['product_id' => $product->id, 'quantity' => 3],
    ], $this->owner);

    expect($product->fresh()->stock)->toBe(7)
        ->and(InventoryMovement::where('order_id', $order->id)
            ->where('type', InventoryMovementType::Reserve->value)
            ->where('quantity', -3)
            ->exists())->toBeTrue();
});

test('a reservation with insufficient stock rolls back and discounts nothing', function () {
    $product = Product::factory()->create([
        'business_id' => $this->business->id,
        'stock' => 2,
        'min_stock' => 0,
    ]);
    $order = Order::factory()->create(['business_id' => $this->business->id]);

    expect(fn () => $this->service->reserveForOrder($order, [
        ['product_id' => $product->id, 'quantity' => 5],
    ], $this->owner))->toThrow(InsufficientStockException::class);

    expect($product->fresh()->stock)->toBe(2)
        ->and(InventoryMovement::where('order_id', $order->id)->count())->toBe(0);
});

test('the low stock alert implements ShouldQueue so the email is sent by a worker', function () {
    $product = Product::factory()->create([
        'business_id' => $this->business->id,
        'stock' => 1,
        'min_stock' => 5,
    ]);

    expect(new LowStockAlert($product))->toBeInstanceOf(ShouldQueue::class);
});

test('the alert is delivered to the business owners only', function () {
    Notification::fake();

    $otherBusiness = Business::factory()->create();
    $otherOwner = User::factory()->owner($otherBusiness)->create();
    $driver = User::factory()->driver($this->business)->create();

    $product = Product::factory()->create([
        'business_id' => $this->business->id,
        'stock' => 8,
        'min_stock' => 5,
    ]);

    $this->service->adjustStock($product, 1, $this->owner);

    Notification::assertSentTo($this->owner, LowStockAlert::class);
    Notification::assertNotSentTo($driver, LowStockAlert::class);
    Notification::assertNotSentTo($otherOwner, LowStockAlert::class);
});

test('a product with orders cannot be deleted', function () {
    $product = Product::factory()->create(['business_id' => $this->business->id, 'stock' => 10]);
    makeReservedOrder($this->business, $product, OrderStatus::Pending, 1);

    expect(fn () => $this->service->deleteProduct($product))
        ->toThrow(ProductInUseException::class);

    expect(Product::whereKey($product->id)->exists())->toBeTrue();
});

test('an unused product can be deleted', function () {
    $product = Product::factory()->create(['business_id' => $this->business->id]);

    $this->service->deleteProduct($product);

    expect(Product::whereKey($product->id)->exists())->toBeFalse();
});
