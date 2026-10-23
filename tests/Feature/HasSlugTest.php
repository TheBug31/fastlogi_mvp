<?php

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

it('generates a slug from the name when creating a business', function () {
    $business = Business::create([
        'name' => 'Restaurante El Leñador',
        'address' => 'Calle 93 # 14-20, Bogotá',
        'phone' => '3101234567',
    ]);

    expect($business->slug)->toBe('restaurante-el-lenador');
});

it('generates a slug for a user from the name', function () {
    $user = User::factory()->create(['name' => 'Don Antonio Leñador']);

    expect($user->slug)->toBe('don-antonio-lenador');
});

it('appends a numeric suffix when the slug already exists', function () {
    Business::create(['name' => 'Tienda Doña Gloria', 'address' => 'Cra 10', 'phone' => '3001112233']);
    $second = Business::create(['name' => 'Tienda Doña Gloria', 'address' => 'Cra 11', 'phone' => '3001112244']);

    expect($second->slug)->toBe('tienda-dona-gloria-2');
});

it('scopes product slug uniqueness to the business', function () {
    $businessA = Business::factory()->create();
    $businessB = Business::factory()->create();

    $productA = Product::factory()->create(['business_id' => $businessA->id, 'name' => 'Bandeja Paisa']);
    $productB = Product::factory()->create(['business_id' => $businessB->id, 'name' => 'Bandeja Paisa']);

    expect($productA->slug)->toBe('bandeja-paisa')
        ->and($productB->slug)->toBe('bandeja-paisa');
});

it('scopes order slug uniqueness to the business', function () {
    $business = Business::factory()->create();

    $first = Order::factory()->create(['business_id' => $business->id, 'customer_name' => 'María Camila Rodríguez']);
    $second = Order::factory()->create(['business_id' => $business->id, 'customer_name' => 'María Camila Rodríguez']);

    expect($first->slug)->toBe('maria-camila-rodriguez')
        ->and($second->slug)->toBe('maria-camila-rodriguez-2');
});

it('derives the driver slug from the linked user name', function () {
    $business = Business::factory()->create();

    $user = User::factory()->create([
        'business_id' => $business->id,
        'name' => 'Carlos Gómez',
        'role' => UserRole::Driver,
        'status' => UserStatus::Active,
    ]);

    $driver = DeliveryDriver::create([
        'user_id' => $user->id,
        'business_id' => $business->id,
    ]);

    expect($driver->slug)->toBe('carlos-gomez');
});

it('derives the package slug from the tracking code', function () {
    $order = Order::factory()->create();

    $package = Package::create([
        'order_id' => $order->id,
        'tracking_code' => 'PKG-1042-BOG',
    ]);

    expect($package->slug)->toBe('pkg-1042-bog');
});

it('regenerates the slug when the source attribute changes', function () {
    $product = Product::factory()->create(['name' => 'Producto Original']);

    $product->update(['name' => 'Producto Renombrado']);

    expect($product->fresh()->slug)->toBe('producto-renombrado');
});

it('generates a slug for order items from the order and the product', function () {
    $order = Order::factory()->create();
    $product = Product::factory()->create(['business_id' => $order->business_id]);

    $item = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
    ]);

    expect($item->slug)->toBe('item-'.$order->id.'-'.$product->id);
});

it('generates a slug for inventory movements from the product and the type', function () {
    $product = Product::factory()->create();

    $movement = InventoryMovement::factory()->reserve()->create(['product_id' => $product->id]);

    expect($movement->slug)->toBe('movimiento-'.$product->id.'-reserve');
});

it('generates a slug for package scans from the package and the type', function () {
    $scan = PackageScan::factory()->pickup()->create();

    expect($scan->slug)->toBe('escaneo-'.$scan->package_id.'-pickup');
});

it('generates a slug for order status logs from the order and the target status', function () {
    $log = OrderStatusLog::factory()->create();

    expect($log->slug)->toBe('registro-'.$log->order_id.'-offered');
});

it('exposes the slug as the route key and resolves orders by slug or id', function () {
    $order = Order::factory()->create(['customer_name' => 'Ana Pérez']);

    expect($order->getRouteKeyName())->toBe('slug')
        ->and($order->getRouteKey())->toBe('ana-perez')
        ->and(route('owner.orders.show', $order))->toEndWith('/pedidos/ana-perez')
        ->and(Order::query()->whereKeyOrSlug($order->slug)->firstOrFail()->id)->toBe($order->id)
        ->and(Order::query()->whereKeyOrSlug((string) $order->id)->firstOrFail()->id)->toBe($order->id);
});
