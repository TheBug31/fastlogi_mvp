<?php

use App\Enums\DriverAvailability;
use App\Enums\OrderStatus;
use App\Livewire\Owner\Orders;
use App\Models\Business;
use App\Models\DeliveryDriver;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->business = Business::factory()->create();
    $this->owner = User::factory()->owner($this->business)->create();
});

function ownerDriver(Business $business, array $attributes = []): DeliveryDriver
{
    $user = User::factory()->driver($business)->create();

    return DeliveryDriver::factory()->create(array_merge([
        'user_id' => $user->id,
        'business_id' => $business->id,
    ], $attributes));
}

test('the order list only shows orders of the authenticated business', function () {
    Order::factory()->create(['business_id' => $this->business->id, 'customer_name' => 'Cliente Propio']);
    $other = Business::factory()->create();
    Order::factory()->create(['business_id' => $other->id, 'customer_name' => 'Cliente Ajeno']);

    Livewire::actingAs($this->owner)
        ->test(Orders::class)
        ->assertSee('Cliente Propio')
        ->assertDontSee('Cliente Ajeno');
});

test('an owner can create an order from the panel and stock is reserved', function () {
    $product = Product::factory()->create([
        'business_id' => $this->business->id,
        'price' => 8000,
        'stock' => 10,
        'min_stock' => 0,
    ]);

    Livewire::actingAs($this->owner)
        ->test(Orders::class)
        ->call('openCreateModal')
        ->set('customer_name', 'Pedro Gómez')
        ->set('customer_phone', '3001234567')
        ->set('delivery_address', 'Calle 5 # 5-5')
        ->set('payment_method', 'Efectivo')
        ->set('lines', [['product_id' => $product->id, 'quantity' => 2]])
        ->call('createOrder')
        ->assertHasNoErrors()
        ->assertSet('showCreateModal', false);

    $order = Order::where('customer_name', 'Pedro Gómez')->first();

    expect($order)->not->toBeNull()
        ->and($order->total)->toBe(16000.0)
        ->and($product->fresh()->stock)->toBe(8);
});

test('creating an order with insufficient stock shows an error and reserves nothing', function () {
    $product = Product::factory()->create([
        'business_id' => $this->business->id,
        'price' => 8000,
        'stock' => 1,
        'min_stock' => 0,
    ]);

    Livewire::actingAs($this->owner)
        ->test(Orders::class)
        ->call('openCreateModal')
        ->set('customer_name', 'Sobre Venta')
        ->set('customer_phone', '3001234567')
        ->set('delivery_address', 'Calle 5')
        ->set('payment_method', 'Efectivo')
        ->set('lines', [['product_id' => $product->id, 'quantity' => 9]])
        ->call('createOrder')
        ->assertHasErrors('lines');

    expect($product->fresh()->stock)->toBe(1);
});

test('the offer modal lists the available drivers of the business', function () {
    $driver = ownerDriver($this->business);
    $order = Order::factory()->create(['business_id' => $this->business->id]);

    Livewire::actingAs($this->owner)
        ->test(Orders::class)
        ->call('openOfferModal', $order->id)
        ->assertSee($driver->user->name);
});

test('an owner can offer an order to a driver', function () {
    $driver = ownerDriver($this->business);
    $order = Order::factory()->create(['business_id' => $this->business->id]);

    Livewire::actingAs($this->owner)
        ->test(Orders::class)
        ->call('openOfferModal', $order->id)
        ->call('offerToDriver', $driver->id)
        ->assertHasNoErrors()
        ->assertSet('showOfferModal', false);

    expect($order->fresh()->status)->toBe(OrderStatus::Offered)
        ->and($order->fresh()->delivery_driver_id)->toBe($driver->id);
});

test('offering to an offline driver surfaces an error', function () {
    $driver = ownerDriver($this->business, ['availability' => DriverAvailability::Offline]);
    $order = Order::factory()->create(['business_id' => $this->business->id]);

    Livewire::actingAs($this->owner)
        ->test(Orders::class)
        ->call('openOfferModal', $order->id)
        ->call('offerToDriver', $driver->id)
        ->assertHasErrors('offer');

    expect($order->fresh()->status)->toBe(OrderStatus::Pending);
});

test('an owner can cancel a pending order', function () {
    $product = Product::factory()->create(['business_id' => $this->business->id, 'stock' => 10, 'min_stock' => 0]);
    $order = makeReservedOrder($this->business, $product, OrderStatus::Pending, 2);

    Livewire::actingAs($this->owner)
        ->test(Orders::class)
        ->call('openCancelModal', $order->id)
        ->call('cancelOrder')
        ->assertHasNoErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($product->fresh()->stock)->toBe(10);
});

test('reassigning requires a reason', function () {
    $product = Product::factory()->create(['business_id' => $this->business->id, 'stock' => 10]);
    $driver = ownerDriver($this->business);
    $order = makeReservedOrder($this->business, $product, OrderStatus::Assigned, 1, $driver);

    Livewire::actingAs($this->owner)
        ->test(Orders::class)
        ->call('openReassignModal', $order->id)
        ->call('reassign')
        ->assertHasErrors('reassign_reason');

    expect($order->fresh()->status)->toBe(OrderStatus::Assigned);
});

test('an incident is registered and the devolution returns the stock', function () {
    $product = Product::factory()->create(['business_id' => $this->business->id, 'stock' => 10, 'min_stock' => 0]);
    $driver = ownerDriver($this->business);
    $order = makeReservedOrder($this->business, $product, OrderStatus::InTransit, 3, $driver);

    expect($product->fresh()->stock)->toBe(7);

    Livewire::actingAs($this->owner)
        ->test(Orders::class)
        ->call('openIncidentModal', $order->id)
        ->set('incident_reason', 'Cliente ausente')
        ->call('registerIncident')
        ->assertHasNoErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Incident)
        ->and($product->fresh()->stock)->toBe(7);

    Livewire::actingAs($this->owner)
        ->test(Orders::class)
        ->call('confirmDevolution', $order->id);

    expect($product->fresh()->stock)->toBe(10);
});

test('the status filter narrows the list', function () {
    Order::factory()->create(['business_id' => $this->business->id, 'customer_name' => 'Pendiente Uno', 'status' => OrderStatus::Pending]);
    Order::factory()->create(['business_id' => $this->business->id, 'customer_name' => 'EnCamino Dos', 'status' => OrderStatus::InTransit]);

    Livewire::actingAs($this->owner)
        ->test(Orders::class)
        ->set('statusFilter', OrderStatus::InTransit->value)
        ->assertSee('EnCamino Dos')
        ->assertDontSee('Pendiente Uno');
});

test('the order pages render for the authenticated owner', function () {
    $order = Order::factory()->create(['business_id' => $this->business->id]);

    $this->actingAs($this->owner)
        ->get(route('owner.orders.index'))
        ->assertOk();

    $this->actingAs($this->owner)
        ->get(route('owner.orders.create'))
        ->assertOk();

    $this->actingAs($this->owner)
        ->get(route('owner.orders.show', $order->id))
        ->assertOk();
});
