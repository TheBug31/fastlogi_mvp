<?php

use App\Enums\DriverAvailability;
use App\Enums\UserRole;
use App\Livewire\Owner\Drivers;
use App\Models\Business;
use App\Models\DeliveryDriver;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->business = Business::factory()->create();
    $this->owner = User::factory()->owner($this->business)->create();
});

if (! function_exists('fleetDriver')) {
    function fleetDriver(Business $business, array $attributes = []): DeliveryDriver
    {
        $user = User::factory()->driver($business)->create();

        return DeliveryDriver::factory()->create(array_merge([
            'user_id' => $user->id,
            'business_id' => $business->id,
        ], $attributes));
    }
}

test('the fleet list only shows drivers of the authenticated business', function () {
    fleetDriver($this->business, ['availability' => DriverAvailability::Available]);
    fleetDriver(Business::factory()->create());

    Livewire::actingAs($this->owner)
        ->test(Drivers::class)
        ->assertViewHas('drivers', fn ($drivers) => $drivers->count() === 1);
});

test('the fleet KPI cards count each availability state', function () {
    fleetDriver($this->business, ['availability' => DriverAvailability::Available]);
    fleetDriver($this->business, ['availability' => DriverAvailability::Available]);
    fleetDriver($this->business, ['availability' => DriverAvailability::Busy]);
    fleetDriver($this->business, ['availability' => DriverAvailability::Offline]);

    Livewire::actingAs($this->owner)
        ->test(Drivers::class)
        ->assertViewHas('kpis', function (array $kpis) {
            return $kpis['total'] === 4
                && $kpis['disponibles'] === 2
                && $kpis['ocupados'] === 1
                && $kpis['offline'] === 1;
        });
});

test('the owner can onboard a new driver from the panel', function () {
    Livewire::actingAs($this->owner)
        ->test(Drivers::class)
        ->call('openCreateModal')
        ->set('name', 'Pedro Rueda')
        ->set('email', 'pedro.rueda@fastlogi.test')
        ->set('phone', '3001234567')
        ->set('password', 'secreta123')
        ->set('max_active_orders', 5)
        ->call('createDriver')
        ->assertHasNoErrors()
        ->assertSet('showCreateModal', false);

    $user = User::where('email', 'pedro.rueda@fastlogi.test')->firstOrFail();

    expect($user->role)->toBe(UserRole::Driver)
        ->and($user->business_id)->toBe($this->business->id);

    $driver = $user->deliveryDriver;

    expect($driver)->not->toBeNull()
        ->and($driver->max_active_orders)->toBe(5)
        ->and($driver->availability)->toBe(DriverAvailability::Available);
});

test('onboarding validates email uniqueness and the capacity range', function () {
    $existing = User::factory()->create(['email' => 'ocupado@fastlogi.test']);

    Livewire::actingAs($this->owner)
        ->test(Drivers::class)
        ->call('openCreateModal')
        ->set('name', 'Repetido')
        ->set('email', 'ocupado@fastlogi.test')
        ->set('phone', '3000000001')
        ->set('password', 'secreta123')
        ->call('createDriver')
        ->assertHasErrors(['email']);

    Livewire::actingAs($this->owner)
        ->test(Drivers::class)
        ->call('openCreateModal')
        ->set('name', 'Demasiados')
        ->set('email', 'demasiados@fastlogi.test')
        ->set('phone', '3000000002')
        ->set('password', 'secreta123')
        ->set('max_active_orders', 9)
        ->call('createDriver')
        ->assertHasErrors(['max_active_orders']);

    Livewire::actingAs($this->owner)
        ->test(Drivers::class)
        ->call('openCreateModal')
        ->set('name', 'Sin Clave')
        ->set('email', 'sinclave@fastlogi.test')
        ->set('phone', '3000000003')
        ->set('password', '123')
        ->call('createDriver')
        ->assertHasErrors(['password']);

    expect(User::whereKey($existing->id)->exists())->toBeTrue()
        ->and(User::whereIn('email', ['demasiados@fastlogi.test', 'sinclave@fastlogi.test'])->exists())->toBeFalse();
});

test('the owner can edit the profile and the capacity limit of a driver', function () {
    $driver = fleetDriver($this->business);

    Livewire::actingAs($this->owner)
        ->test(Drivers::class)
        ->call('openEditModal', $driver->id)
        ->assertSet('showEditModal', true)
        ->set('edit_name', 'Nombre Editado')
        ->set('edit_email', 'editado@fastlogi.test')
        ->set('edit_phone', '3009999999')
        ->set('edit_max_active_orders', 7)
        ->call('updateDriver')
        ->assertHasNoErrors()
        ->assertSet('showEditModal', false);

    $driver->refresh();

    expect($driver->max_active_orders)->toBe(7)
        ->and($driver->user->name)->toBe('Nombre Editado')
        ->and($driver->user->email)->toBe('editado@fastlogi.test');
});

test('the owner cannot open the profile of a driver from another business', function () {
    $foreign = fleetDriver(Business::factory()->create());

    expect(fn () => Livewire::actingAs($this->owner)
        ->test(Drivers::class)
        ->call('openEditModal', $foreign->id))
        ->toThrow(ModelNotFoundException::class);
});

test('the owner can toggle a driver between offline and online', function () {
    $driver = fleetDriver($this->business, ['availability' => DriverAvailability::Available]);

    Livewire::actingAs($this->owner)
        ->test(Drivers::class)
        ->call('toggleAvailability', $driver->id);

    expect($driver->fresh()->availability)->toBe(DriverAvailability::Offline);

    Livewire::actingAs($this->owner)
        ->test(Drivers::class)
        ->call('toggleAvailability', $driver->id);

    expect($driver->fresh()->availability)->toBe(DriverAvailability::Available);
});
