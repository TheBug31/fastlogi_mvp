<?php

use App\Enums\OrderStatus;
use App\Enums\PackageStatus;
use App\Livewire\Driver\OrderDetail;
use App\Livewire\Driver\Routes;
use App\Models\Business;
use App\Models\DeliveryDriver;
use App\Models\Order;
use App\Models\Package;
use App\Models\PackageScan;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

if (! function_exists('panelDriver')) {
    function panelDriver(Business $business, array $attributes = []): DeliveryDriver
    {
        $user = User::factory()->driver($business)->create();

        return DeliveryDriver::factory()->create(array_merge([
            'user_id' => $user->id,
            'business_id' => $business->id,
        ], $attributes));
    }
}

function attachPackage(Order $order): Package
{
    $order->update(['requires_traceability' => true]);

    return Package::create([
        'order_id' => $order->id,
        'tracking_code' => 'FL-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
        'status' => PackageStatus::Pending,
    ]);
}

beforeEach(function () {
    $this->business = Business::factory()->create();
    $this->driver = panelDriver($this->business);
    $this->driverUser = $this->driver->user;
    $this->product = Product::factory()->create([
        'business_id' => $this->business->id,
        'stock' => 10,
        'min_stock' => 0,
    ]);
});

test('the routes panel lists this driver offers and active orders', function () {
    Order::factory()->offered($this->driver)->create([
        'business_id' => $this->business->id,
        'customer_name' => 'Oferta Visible',
    ]);
    Order::factory()->inTransit($this->driver)->create([
        'business_id' => $this->business->id,
        'customer_name' => 'En Curso Visible',
    ]);

    Livewire::actingAs($this->driverUser)
        ->test(Routes::class)
        ->assertSee('Oferta Visible')
        ->assertSee('En Curso Visible');
});

test('the routes panel hides orders of other drivers', function () {
    $other = panelDriver($this->business);
    Order::factory()->offered($other)->create([
        'business_id' => $this->business->id,
        'customer_name' => 'Oferta Ajena',
    ]);

    Livewire::actingAs($this->driverUser)
        ->test(Routes::class)
        ->assertDontSee('Oferta Ajena');
});

test('opening the panel records the driver presence', function () {
    $this->driver->update(['last_seen_at' => now()->subHour()]);

    Livewire::actingAs($this->driverUser)->test(Routes::class);

    expect($this->driver->fresh()->last_seen_at->gt(now()->subMinute()))->toBeTrue();
});

test('a driver can accept an offer from the panel', function () {
    $order = Order::factory()->offered($this->driver)->create([
        'business_id' => $this->business->id,
        'requires_traceability' => false,
    ]);

    Livewire::actingAs($this->driverUser)
        ->test(Routes::class)
        ->call('acceptOffer', $order->id)
        ->assertHasNoErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::InTransit);
});

test('a driver can reject an offer from the panel', function () {
    $order = Order::factory()->offered($this->driver)->create(['business_id' => $this->business->id]);

    Livewire::actingAs($this->driverUser)
        ->test(Routes::class)
        ->call('rejectOffer', $order->id)
        ->assertHasNoErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Pending)
        ->and($order->fresh()->delivery_driver_id)->toBeNull();
});

test('the detail panel scans the pickup of a traceable order', function () {
    $order = Order::factory()->assigned($this->driver)->create(['business_id' => $this->business->id]);
    $package = attachPackage($order);

    Livewire::actingAs($this->driverUser)
        ->test(OrderDetail::class, ['orderId' => $order->id])
        ->call('scanPickup')
        ->assertHasNoErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::InTransit)
        ->and($package->fresh()->status)->toBe(PackageStatus::PickedUp)
        ->and(PackageScan::where('package_id', $package->id)->count())->toBe(1);
});

test('the detail panel scans the delivery of a traceable order', function () {
    $order = Order::factory()->assigned($this->driver)->create(['business_id' => $this->business->id]);
    $package = attachPackage($order);
    $order->update(['status' => OrderStatus::InTransit]);

    Livewire::actingAs($this->driverUser)
        ->test(OrderDetail::class, ['orderId' => $order->id])
        ->call('scanDelivery')
        ->assertHasNoErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Delivered)
        ->and($package->fresh()->status)->toBe(PackageStatus::Delivered);
});

test('a non-traceable order is completed without creating scans', function () {
    $order = Order::factory()->inTransit($this->driver)->create([
        'business_id' => $this->business->id,
        'requires_traceability' => false,
    ]);

    Livewire::actingAs($this->driverUser)
        ->test(OrderDetail::class, ['orderId' => $order->id])
        ->call('scanDelivery')
        ->assertHasNoErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Delivered)
        ->and(PackageScan::count())->toBe(0);
});

test('the detail panel can report an incident', function () {
    $order = Order::factory()->inTransit($this->driver)->create(['business_id' => $this->business->id]);

    Livewire::actingAs($this->driverUser)
        ->test(OrderDetail::class, ['orderId' => $order->id])
        ->call('openIncidentSheet')
        ->set('incidentReason', 'Cliente ausente / No contesta')
        ->set('incidentNotes', 'Nadie atendió en la dirección')
        ->call('reportIncident')
        ->assertHasNoErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Incident);
});

test('the driver pages render and isolate orders by driver', function () {
    $order = Order::factory()->assigned($this->driver)->create(['business_id' => $this->business->id]);
    attachPackage($order);

    $this->actingAs($this->driverUser)->get(route('driver.routes'))->assertOk();
    $this->actingAs($this->driverUser)->get(route('driver.orders.show', $order->id))->assertOk();

    $otherDriver = panelDriver($this->business);

    $this->actingAs($otherDriver->user)
        ->get(route('driver.orders.show', $order->id))
        ->assertNotFound();
});
