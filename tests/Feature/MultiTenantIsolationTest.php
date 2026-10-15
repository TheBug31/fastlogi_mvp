<?php

use App\Models\Business;
use App\Models\DeliveryDriver;
use App\Models\Order;
use App\Models\Product;
use App\Models\Scopes\BusinessScope;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('owner only sees products and orders belonging to their own business', function () {
    $businessA = Business::factory()->create(['name' => 'Negocio Alfa']);
    $businessB = Business::factory()->create(['name' => 'Negocio Beta']);

    $ownerA = User::factory()->owner($businessA)->create();
    $ownerB = User::factory()->owner($businessB)->create();

    $productA = Product::factory()->create(['business_id' => $businessA->id, 'name' => 'Producto Alfa']);
    $productB = Product::factory()->create(['business_id' => $businessB->id, 'name' => 'Producto Beta']);

    $orderA = Order::factory()->create(['business_id' => $businessA->id]);
    $orderB = Order::factory()->create(['business_id' => $businessB->id]);

    $driverA = DeliveryDriver::factory()->create(['business_id' => $businessA->id]);
    $driverB = DeliveryDriver::factory()->create(['business_id' => $businessB->id]);

    // Acting as Owner A
    $this->actingAs($ownerA);

    expect(Product::count())->toBe(1)
        ->and(Product::first()->id)->toBe($productA->id)
        ->and(Product::find($productB->id))->toBeNull()
        ->and(Order::count())->toBe(1)
        ->and(Order::first()->id)->toBe($orderA->id)
        ->and(Order::find($orderB->id))->toBeNull()
        ->and(DeliveryDriver::count())->toBe(1)
        ->and(DeliveryDriver::first()->id)->toBe($driverA->id)
        ->and(DeliveryDriver::find($driverB->id))->toBeNull();

    // Acting as Owner B
    $this->actingAs($ownerB);

    expect(Product::count())->toBe(1)
        ->and(Product::first()->id)->toBe($productB->id)
        ->and(Product::find($productA->id))->toBeNull()
        ->and(Order::count())->toBe(1)
        ->and(Order::first()->id)->toBe($orderB->id)
        ->and(Order::find($orderA->id))->toBeNull()
        ->and(DeliveryDriver::count())->toBe(1)
        ->and(DeliveryDriver::first()->id)->toBe($driverB->id)
        ->and(DeliveryDriver::find($driverA->id))->toBeNull();
});

test('superadmin can see records across all tenants without scope filter', function () {
    $businessA = Business::factory()->create();
    $businessB = Business::factory()->create();

    $superadmin = User::factory()->superadmin()->create();

    Product::factory()->create(['business_id' => $businessA->id]);
    Product::factory()->create(['business_id' => $businessB->id]);

    Order::factory()->create(['business_id' => $businessA->id]);
    Order::factory()->create(['business_id' => $businessB->id]);

    $this->actingAs($superadmin);

    expect(Product::count())->toBe(2)
        ->and(Order::count())->toBe(2);
});

test('creating model automatically populates business_id from authenticated tenant user', function () {
    $business = Business::factory()->create();
    $owner = User::factory()->owner($business)->create();

    $this->actingAs($owner);

    $product = Product::create([
        'name' => 'Empanada de Carne',
        'price' => 3500,
        'stock' => 50,
        'min_stock' => 10,
        'unit' => 'unidad',
    ]);

    expect($product->business_id)->toBe($business->id);

    $order = Order::create([
        'customer_name' => 'Cliente Demo',
        'customer_phone' => '3001234567',
        'delivery_address' => 'Calle 10 # 5-20',
        'payment_method' => 'Efectivo',
        'total' => 3500,
    ]);

    expect($order->business_id)->toBe($business->id);
});

test('withoutGlobalScope allows background workers to load orders regardless of session', function () {
    $business = Business::factory()->create();
    $order = Order::factory()->create(['business_id' => $business->id]);

    $foundOrder = Order::withoutGlobalScope(BusinessScope::class)
        ->where('id', $order->id)
        ->where('business_id', $business->id)
        ->first();

    expect($foundOrder)->not->toBeNull()
        ->and($foundOrder->id)->toBe($order->id);
});
