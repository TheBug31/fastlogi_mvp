<?php

use App\Enums\DriverAvailability;
use App\Enums\UserRole;
use App\Models\Business;
use App\Models\Order;
use App\Services\DriverManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->business = Business::factory()->create();
    $this->service = app(DriverManagementService::class);
});

test('creating a driver persists the user and the profile in a single transaction', function () {
    $driver = $this->service->createDriver($this->business, [
        'name' => 'Nuevo Repartidor',
        'email' => 'nuevo@fastlogi.test',
        'phone' => '3001234567',
        'password' => 'password123',
        'max_active_orders' => 5,
    ]);

    expect($driver->business_id)->toBe($this->business->id)
        ->and($driver->availability)->toBe(DriverAvailability::Available)
        ->and($driver->max_active_orders)->toBe(5)
        ->and($driver->last_seen_at)->toBeNull();

    $user = $driver->user;

    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(UserRole::Driver)
        ->and($user->business_id)->toBe($this->business->id)
        ->and($user->email)->toBe('nuevo@fastlogi.test');

    expect(DB::table('users')->where('email', 'nuevo@fastlogi.test')->count())->toBe(1);
});

test('the capacity must stay within the configurable range of 1 to 8', function () {
    expect(fn () => $this->service->createDriver($this->business, [
        'name' => 'Fuera de rango',
        'email' => 'rango@fastlogi.test',
        'phone' => '3000000000',
        'password' => 'password123',
        'max_active_orders' => 9,
    ]))->toThrow(InvalidArgumentException::class);

    $driver = $this->service->createDriver($this->business, [
        'name' => 'En rango',
        'email' => 'ok@fastlogi.test',
        'phone' => '3000000001',
        'password' => 'password123',
        'max_active_orders' => 3,
    ]);

    expect(fn () => $this->service->updateCapacity($driver, 0))
        ->toThrow(InvalidArgumentException::class);

    expect($this->service->updateCapacity($driver, 8)->max_active_orders)->toBe(8);
});

test('updating a driver changes the profile and the capacity limit', function () {
    $driver = $this->service->createDriver($this->business, [
        'name' => 'Antiguo Nombre',
        'email' => 'antiguo@fastlogi.test',
        'phone' => '3001111111',
        'password' => 'password123',
    ]);

    $updated = $this->service->updateDriver($driver, [
        'name' => 'Nuevo Nombre',
        'email' => 'nuevo-nombre@fastlogi.test',
        'phone' => '3002222222',
        'max_active_orders' => 6,
    ]);

    expect($updated->max_active_orders)->toBe(6)
        ->and($updated->user->name)->toBe('Nuevo Nombre')
        ->and($updated->user->email)->toBe('nuevo-nombre@fastlogi.test')
        ->and($updated->user->phone)->toBe('3002222222');
});

test('marking a driver offline then online recomputes busy from the workload', function () {
    $driver = $this->service->createDriver($this->business, [
        'name' => 'Con Carga',
        'email' => 'carga@fastlogi.test',
        'phone' => '3003333333',
        'password' => 'password123',
    ]);

    $driver->update(['availability' => DriverAvailability::Busy]);

    Order::factory()->assigned($driver)->create(['business_id' => $this->business->id]);

    $offline = $this->service->setAvailability($driver, DriverAvailability::Offline);
    expect($offline->availability)->toBe(DriverAvailability::Offline);

    $online = $this->service->setAvailability($offline, DriverAvailability::Available);

    expect($online->availability)->toBe(DriverAvailability::Busy);
});

test('bringing an idle driver back online leaves them available', function () {
    $driver = $this->service->createDriver($this->business, [
        'name' => 'Sin Carga',
        'email' => 'sincarga@fastlogi.test',
        'phone' => '3004444444',
        'password' => 'password123',
    ]);

    $this->service->setAvailability($driver, DriverAvailability::Offline);
    $online = $this->service->setAvailability($driver, DriverAvailability::Available);

    expect($online->availability)->toBe(DriverAvailability::Available);
});
