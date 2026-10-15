<?php

use App\Exceptions\InsufficientStockException;
use App\Models\Business;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;

/*
 * RF-001 §4.1.1 requires the stock reservation to be protected with a pessimistic
 * row lock (SELECT ... FOR UPDATE). These tests run against MySQL using *two
 * independent connections*: no in-memory database is involved. They deliberately
 * do not wrap the test in a transaction (DatabaseMigrations) so the data written
 * on the default connection is visible to the second one.
 */
uses(DatabaseMigrations::class);

test('a second concurrent transaction is blocked by the row lock held on the product', function () {
    $business = Business::factory()->create();
    Product::factory()->create([
        'business_id' => $business->id,
        'stock' => 1,
        'min_stock' => 0,
    ]);

    $productId = Product::query()->value('id');

    // Independent connection to the same MySQL database.
    config(['database.connections.mysql_alt' => config('database.connections.mysql')]);
    $alternative = DB::connection('mysql_alt');
    $alternative->statement('SET SESSION innodb_lock_wait_timeout = 1');

    $default = DB::connection('mysql');

    // First transaction locks the row (exactly what InventoryService::lockProduct does).
    $default->beginTransaction();
    $default->table('products')->where('id', $productId)->lockForUpdate()->first();

    // The second transaction tries to lock the same row and must wait, then fail.
    $alternative->beginTransaction();

    expect(fn () => $alternative->table('products')->where('id', $productId)->lockForUpdate()->first())
        ->toThrow(QueryException::class);

    $alternative->rollBack();
    $default->rollBack();
});

test('only one of two attempts to reserve the last unit succeeds', function () {
    $business = Business::factory()->create();
    $owner = User::factory()->owner($business)->create();
    $product = Product::factory()->create([
        'business_id' => $business->id,
        'stock' => 1,
        'min_stock' => 0,
    ]);

    $this->actingAs($owner);
    $service = app(OrderService::class);

    $payload = [
        'customer_name' => 'Cliente Uno',
        'customer_phone' => '3001234567',
        'delivery_address' => 'Calle 1 # 1-1',
        'payment_method' => 'Efectivo',
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ];

    $service->createOrder($payload, $owner);

    expect($product->fresh()->stock)->toBe(0);

    // The second attempt finds zero stock and is rejected without overselling.
    expect(fn () => $service->createOrder($payload, $owner))
        ->toThrow(InsufficientStockException::class);

    expect($product->fresh()->stock)->toBe(0);
});
