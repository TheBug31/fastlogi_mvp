<?php

use App\Enums\DriverAvailability;
use App\Enums\OrderStatus;
use App\Livewire\Owner\Dashboard;
use App\Livewire\Owner\Drivers;
use App\Livewire\Owner\Notifications;
use App\Models\Business;
use App\Models\DeliveryDriver;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->business = Business::factory()->create();
    $this->owner = User::factory()->owner($this->business)->create();
});

if (! function_exists('dashboardDriver')) {
    function dashboardDriver(Business $business, array $attributes = []): DeliveryDriver
    {
        $user = User::factory()->driver($business)->create();

        return DeliveryDriver::factory()->create(array_merge([
            'user_id' => $user->id,
            'business_id' => $business->id,
        ], $attributes));
    }
}

test('the dashboard renders the operations board for the owner', function () {
    Livewire::actingAs($this->owner)
        ->test(Dashboard::class)
        ->assertSee('Panel de Operaciones')
        ->assertSee('Pedidos en curso')
        ->assertSee('Flota')
        ->assertSee('Actividad reciente');
});

test('the owner pages render through their routes with the notification bell', function () {
    $this->actingAs($this->owner)->get(route('owner.dashboard'))
        ->assertOk()
        ->assertSeeLivewire(Dashboard::class);

    $this->actingAs($this->owner)->get(route('owner.drivers.index'))
        ->assertOk()
        ->assertSeeLivewire(Drivers::class);

    $this->actingAs($this->owner)->get(route('owner.notifications.index'))
        ->assertOk()
        ->assertSeeLivewire(Notifications::class);
});

test('the dashboard KPIs are scoped to the authenticated business', function () {
    Order::factory()->create([
        'business_id' => $this->business->id,
        'status' => OrderStatus::Pending,
        'total' => 10000,
    ]);
    Order::factory()->inTransit()->create([
        'business_id' => $this->business->id,
        'total' => 20000,
    ]);
    Order::factory()->delivered()->create([
        'business_id' => $this->business->id,
        'total' => 30000,
    ]);

    $other = Business::factory()->create();
    Order::factory()->create(['business_id' => $other->id, 'status' => OrderStatus::Pending]);
    Order::factory()->inTransit()->create(['business_id' => $other->id]);

    Livewire::actingAs($this->owner)
        ->test(Dashboard::class)
        ->assertViewHas('kpis', function (array $kpis) {
            return (int) $kpis['activos'] === 2
                && (int) $kpis['entregadosHoy'] === 1
                && (float) $kpis['ventasHoy'] === 30000.0
                && (int) $kpis['pedidosHoy'] === 3;
        });
});

test('the dashboard reports the available fleet and its capacity', function () {
    dashboardDriver($this->business, ['availability' => DriverAvailability::Available]);
    dashboardDriver($this->business, ['availability' => DriverAvailability::Busy]);
    dashboardDriver($this->business, ['availability' => DriverAvailability::Offline]);

    $other = Business::factory()->create();
    dashboardDriver($other, ['availability' => DriverAvailability::Available]);

    Livewire::actingAs($this->owner)
        ->test(Dashboard::class)
        ->assertViewHas('kpis', fn (array $kpis) => (int) $kpis['disponibles'] === 1)
        ->assertViewHas('drivers', fn ($drivers) => $drivers->count() === 3);
});

test('the dashboard flags products below the minimum stock', function () {
    Product::factory()->create([
        'business_id' => $this->business->id,
        'stock' => 1,
        'min_stock' => 5,
    ]);
    Product::factory()->create([
        'business_id' => $this->business->id,
        'stock' => 20,
        'min_stock' => 5,
    ]);
    Product::factory()->create([
        'business_id' => $this->business->id,
        'stock' => 0,
        'min_stock' => 0,
    ]);

    Livewire::actingAs($this->owner)
        ->test(Dashboard::class)
        ->assertViewHas('kpis', fn (array $kpis) => (int) $kpis['stockBajo'] === 1)
        ->assertSee('stock mínimo');
});

test('the activity timeline only shows movements of the authenticated business', function () {
    $order = Order::factory()->create(['business_id' => $this->business->id]);
    OrderStatusLog::factory()->create([
        'order_id' => $order->id,
        'from' => OrderStatus::InTransit->value,
        'to' => OrderStatus::Delivered->value,
        'reason' => 'Entrega confirmada en portería',
    ]);

    $otherOrder = Order::factory()->create(['business_id' => Business::factory()->create()->id]);
    OrderStatusLog::factory()->create([
        'order_id' => $otherOrder->id,
        'from' => OrderStatus::Assigned->value,
        'to' => OrderStatus::InTransit->value,
        'reason' => 'Motivo ajeno confidencial',
    ]);

    Livewire::actingAs($this->owner)
        ->test(Dashboard::class)
        ->assertSee('Entrega confirmada en portería')
        ->assertDontSee('Motivo ajeno confidencial');
});
