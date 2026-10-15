<?php

use App\Models\Business;
use App\Models\DeliveryDriver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->business = Business::factory()->create();
    $this->driverUser = User::factory()->driver($this->business)->create();
    $this->driver = DeliveryDriver::factory()->create([
        'user_id' => $this->driverUser->id,
        'business_id' => $this->business->id,
    ]);
});

test('a driver can post a telemetry ping', function () {
    $this->actingAs($this->driverUser)
        ->postJson(route('driver.telemetry'), ['latitude' => 4.65, 'longitude' => -74.05])
        ->assertOk()
        ->assertJson(['ok' => true]);

    $this->driver->refresh();
    expect($this->driver->latitude)->toBe(4.65)
        ->and($this->driver->longitude)->toBe(-74.05)
        ->and($this->driver->last_seen_at)->not->toBeNull();
});

test('a telemetry ping validates its coordinates', function () {
    $seenBefore = $this->driver->last_seen_at;

    $this->actingAs($this->driverUser)
        ->postJson(route('driver.telemetry'), ['latitude' => 200, 'longitude' => -74.05])
        ->assertStatus(422)
        ->assertJsonValidationErrors('latitude');

    expect($this->driver->fresh()->last_seen_at->timestamp)->toBe($seenBefore->timestamp);
});

test('a non-driver cannot post telemetry', function () {
    $owner = User::factory()->owner($this->business)->create();

    $this->actingAs($owner)
        ->postJson(route('driver.telemetry'), ['latitude' => 4.65, 'longitude' => -74.05])
        ->assertForbidden();
});
