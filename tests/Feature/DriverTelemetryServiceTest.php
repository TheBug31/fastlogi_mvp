<?php

use App\Enums\DriverAvailability;
use App\Jobs\DetectInactiveDrivers;
use App\Models\Business;
use App\Models\DeliveryDriver;
use App\Models\Order;
use App\Models\User;
use App\Notifications\DriverInactive;
use App\Services\DriverTelemetryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

if (! function_exists('telemetryDriver')) {
    function telemetryDriver(Business $business, array $attributes = []): DeliveryDriver
    {
        $user = User::factory()->driver($business)->create();

        return DeliveryDriver::factory()->create(array_merge([
            'user_id' => $user->id,
            'business_id' => $business->id,
        ], $attributes));
    }
}

beforeEach(function () {
    $this->business = Business::factory()->create(['inactivity_threshold_minutes' => 15]);
    $this->telemetry = app(DriverTelemetryService::class);
});

test('recording a location stores coordinates, last_seen_at and marks the driver busy with active orders', function () {
    $driver = telemetryDriver($this->business);
    Order::factory()->inTransit($driver)->create(['business_id' => $this->business->id]);

    $this->telemetry->record($driver, 4.65, -74.05);

    $driver->refresh();
    expect($driver->latitude)->toBe(4.65)
        ->and($driver->longitude)->toBe(-74.05)
        ->and($driver->last_seen_at)->not->toBeNull()
        ->and($driver->availability)->toBe(DriverAvailability::Busy);
});

test('recording a location marks the driver available when they have no active orders', function () {
    $driver = telemetryDriver($this->business, ['availability' => DriverAvailability::Busy]);

    $this->telemetry->record($driver, 4.65, -74.05);

    expect($driver->fresh()->availability)->toBe(DriverAvailability::Available);
});

test('reconnecting restores availability over an inactivity offline state', function () {
    $driver = telemetryDriver($this->business, ['availability' => DriverAvailability::Offline]);
    Order::factory()->assigned($driver)->create(['business_id' => $this->business->id]);

    $this->telemetry->touch($driver);

    expect($driver->fresh()->availability)->toBe(DriverAvailability::Busy);
});

test('inactiveDrivers returns drivers past the business threshold', function () {
    $driver = telemetryDriver($this->business, [
        'last_seen_at' => now()->subMinutes(20),
    ]);
    Order::factory()->inTransit($driver)->create(['business_id' => $this->business->id]);

    expect($this->telemetry->inactiveDrivers()->pluck('id')->all())->toBe([$driver->id]);
});

test('inactiveDrivers ignores drivers within the threshold', function () {
    $driver = telemetryDriver($this->business, [
        'last_seen_at' => now()->subMinutes(5),
    ]);
    Order::factory()->inTransit($driver)->create(['business_id' => $this->business->id]);

    expect($this->telemetry->inactiveDrivers())->toBeEmpty();
});

test('inactiveDrivers ignores drivers without active orders', function () {
    $driver = telemetryDriver($this->business, [
        'last_seen_at' => now()->subMinutes(50),
    ]);

    expect($this->telemetry->inactiveDrivers())->toBeEmpty();
});

test('inactiveDrivers respects a custom business threshold', function () {
    $business = Business::factory()->create(['inactivity_threshold_minutes' => 60]);
    $driver = telemetryDriver($business, ['last_seen_at' => now()->subMinutes(30)]);
    Order::factory()->assigned($driver)->create(['business_id' => $business->id]);

    expect($this->telemetry->inactiveDrivers())->toBeEmpty();
});

test('flagInactive sets the driver offline and notifies the owner once', function () {
    Notification::fake();

    $owner = User::factory()->owner($this->business)->create();
    $driver = telemetryDriver($this->business);
    Order::factory()->inTransit($driver)->create(['business_id' => $this->business->id]);

    $this->telemetry->flagInactive($driver);

    expect($driver->fresh()->availability)->toBe(DriverAvailability::Offline);
    Notification::assertSentTo($owner, DriverInactive::class);

    // Idempotent: a second flag on an offline driver sends nothing.
    $this->telemetry->flagInactive($driver->fresh());
    Notification::assertSentToTimes($owner, DriverInactive::class, 1);
});

test('the DetectInactiveDrivers job flags every inactive driver', function () {
    Notification::fake();

    User::factory()->owner($this->business)->create();
    $inactive = telemetryDriver($this->business, ['last_seen_at' => now()->subMinutes(30)]);
    Order::factory()->inTransit($inactive)->create(['business_id' => $this->business->id]);

    $fresh = telemetryDriver($this->business, ['last_seen_at' => now()->subMinute()]);
    Order::factory()->inTransit($fresh)->create(['business_id' => $this->business->id]);

    (new DetectInactiveDrivers)->handle($this->telemetry);

    expect($inactive->fresh()->availability)->toBe(DriverAvailability::Offline)
        ->and($fresh->fresh()->availability)->not->toBe(DriverAvailability::Offline);
});
