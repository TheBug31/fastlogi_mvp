<?php

use App\Enums\OrderStatus;
use App\Livewire\Owner\Reports;
use App\Models\Business;
use App\Models\DeliveryDriver;
use App\Models\Order;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->business = Business::factory()->create();
    $this->owner = User::factory()->owner($this->business)->create();

    $this->filters = [
        'desde' => now()->startOfMonth()->toDateString(),
        'hasta' => now()->toDateString(),
        'estado' => 'todos',
        'driver_id' => null,
        'payment_method' => 'todos',
    ];
});

if (! function_exists('reportDriver')) {
    function reportDriver(Business $business): DeliveryDriver
    {
        $user = User::factory()->driver($business)->create();

        return DeliveryDriver::factory()->create([
            'user_id' => $user->id,
            'business_id' => $business->id,
        ]);
    }
}

test('the reports page renders with a live preview of the results', function () {
    Order::factory()->create(['business_id' => $this->business->id, 'total' => 25000]);

    Livewire::actingAs($this->owner)
        ->test(Reports::class)
        ->assertSee('Reportes')
        ->assertSee('Descargar pedidos (PDF)')
        ->assertViewHas('orders', fn ($orders) => $orders->count() === 1)
        ->assertViewHas('orderTotal', fn ($total) => (float) $total === 25000.0);
});

test('the report queries are isolated per business', function () {
    $this->actingAs($this->owner);

    Order::factory()->create(['business_id' => $this->business->id]);
    reportDriver($this->business);

    $other = Business::factory()->create();
    Order::factory()->count(3)->create(['business_id' => $other->id]);
    reportDriver($other);

    $service = app(ReportService::class);

    expect($service->orders($this->filters))->toHaveCount(1)
        ->and($service->drivers($this->filters))->toHaveCount(1);
});

test('the orders report honours the status and date filters', function () {
    Order::factory()->create([
        'business_id' => $this->business->id,
        'status' => OrderStatus::Delivered,
        'created_at' => now(),
    ]);
    Order::factory()->create([
        'business_id' => $this->business->id,
        'status' => OrderStatus::Pending,
        'created_at' => now(),
    ]);
    Order::factory()->create([
        'business_id' => $this->business->id,
        'status' => OrderStatus::Delivered,
        'created_at' => now()->subMonths(3),
    ]);

    $service = app(ReportService::class);

    expect($service->orders(array_merge($this->filters, ['estado' => OrderStatus::Delivered->value])))
        ->toHaveCount(1);

    expect($service->orders([
        'desde' => now()->subMonths(4)->toDateString(),
        'hasta' => now()->toDateString(),
        'estado' => 'todos',
        'driver_id' => null,
        'payment_method' => 'todos',
    ]))->toHaveCount(3);
});

test('the drivers report aggregates deliveries within the period', function () {
    $driver = reportDriver($this->business);

    Order::factory()->count(2)->delivered($driver)->create([
        'business_id' => $this->business->id,
        'total' => 30000,
    ]);
    Order::factory()->delivered($driver)->create([
        'business_id' => $this->business->id,
        'total' => 10000,
        'updated_at' => now()->subMonths(3),
    ]);
    Order::factory()->assigned($driver)->create(['business_id' => $this->business->id]);

    $report = app(ReportService::class)->drivers($this->filters)->first();

    expect($report->id)->toBe($driver->id)
        ->and((int) $report->delivered_count)->toBe(2)
        ->and((float) $report->delivered_total)->toBe(60000.0)
        ->and((int) $report->active_count)->toBe(1);
});

test('the owner can download the orders and drivers PDF files', function () {
    $driver = reportDriver($this->business);
    Order::factory()->delivered($driver)->create(['business_id' => $this->business->id]);

    $orders = $this->actingAs($this->owner)->get(route('owner.reports.orders', $this->filters));
    $orders->assertOk();
    expect($orders->headers->get('content-type'))->toContain('application/pdf');
    expect($orders->headers->get('content-disposition'))->toContain('attachment');
    expect(str_starts_with($orders->getContent(), '%PDF'))->toBeTrue();

    $drivers = $this->actingAs($this->owner)->get(route('owner.reports.drivers', $this->filters));
    $drivers->assertOk();
    expect($drivers->headers->get('content-type'))->toContain('application/pdf');
    expect(str_starts_with($drivers->getContent(), '%PDF'))->toBeTrue();
});

test('an invalid date range is rejected before generating the pdf', function () {
    $this->actingAs($this->owner)
        ->get(route('owner.reports.orders', [
            'desde' => now()->toDateString(),
            'hasta' => now()->subWeek()->toDateString(),
        ]))
        ->assertSessionHasErrors('hasta');
});

test('guests are redirected and other roles are forbidden', function () {
    $this->get(route('owner.reports.orders'))->assertRedirect(route('login'));

    $driverUser = User::factory()->driver($this->business)->create();

    $this->actingAs($driverUser)->get(route('owner.reports.orders'))->assertForbidden();
    $this->actingAs($driverUser)->get(route('owner.reports.index'))->assertForbidden();
});
