<?php

use App\Enums\InventoryMovementType;
use App\Enums\OrderStatus;
use App\Models\Business;
use App\Models\DeliveryDriver;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

if (! function_exists('makeReservedOrder')) {
    /**
     * Create an order that holds a real stock reservation.
     *
     * Simulates what OrderService will do in F5: decrement the product stock and
     * record a `reserve` movement, so the suspension release logic can be tested
     * against a realistic starting point.
     */
    function makeReservedOrder(
        Business $business,
        Product $product,
        OrderStatus $status,
        int $quantity = 1,
        ?DeliveryDriver $driver = null,
    ): Order {
        $product->decrement('stock', $quantity);

        $order = Order::factory()->create([
            'business_id' => $business->id,
            'status' => $status,
            'delivery_driver_id' => $driver?->id,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $product->price,
            'subtotal' => $quantity * $product->price,
        ]);

        InventoryMovement::create([
            'product_id' => $product->id,
            'order_id' => $order->id,
            'type' => InventoryMovementType::Reserve,
            'quantity' => -$quantity,
            'user_id' => null,
        ]);

        return $order;
    }
}
