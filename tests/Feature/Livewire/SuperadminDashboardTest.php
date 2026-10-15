<?php

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Livewire\Superadmin\Dashboard;
use App\Models\Business;
use App\Models\DeliveryDriver;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->superadmin()->create();
});

test('the dashboard lists the businesses with their owner', function () {
    $business = Business::factory()->create(['name' => 'Distribuidora Los Alpes']);
    User::factory()->owner($business)->create(['name' => 'Carlos Pérez']);

    Livewire::actingAs($this->superadmin)
        ->test(Dashboard::class)
        ->assertSee('Distribuidora Los Alpes')
        ->assertSee('Carlos Pérez')
        ->assertSee('Activo');
});

test('a superadmin can create a business with its owner', function () {
    Livewire::actingAs($this->superadmin)
        ->test(Dashboard::class)
        ->call('openCreateModal')
        ->set('name', 'Tienda Doña Rosa')
        ->set('address', 'Calle 45 # 12-30, Medellín')
        ->set('phone', '6044445555')
        ->set('owner_name', 'Rosa María Gómez')
        ->set('owner_email', 'rosa@tiendarosa.test')
        ->set('password', 'secret123')
        ->call('createBusiness')
        ->assertHasNoErrors()
        ->assertSet('showCreateModal', false);

    $business = Business::where('name', 'Tienda Doña Rosa')->first();

    expect($business)->not->toBeNull()
        ->and($business->status->value)->toBe('active');

    $owner = User::where('email', 'rosa@tiendarosa.test')->first();

    expect($owner)->not->toBeNull()
        ->and($owner->business_id)->toBe($business->id)
        ->and($owner->role)->toBe(UserRole::Owner);
});

test('creating a business with a duplicate email fails and creates nothing', function () {
    User::factory()->owner(Business::factory()->create())
        ->create(['email' => 'duplicado@fastlogi.test']);

    $before = Business::count();

    Livewire::actingAs($this->superadmin)
        ->test(Dashboard::class)
        ->call('openCreateModal')
        ->set('name', 'Otro Negocio')
        ->set('address', 'Carrera 1 # 2-3')
        ->set('phone', '3001112233')
        ->set('owner_name', 'Otro Dueño')
        ->set('owner_email', 'duplicado@fastlogi.test')
        ->set('password', 'secret123')
        ->call('createBusiness')
        ->assertHasErrors(['owner_email' => 'unique']);

    expect(Business::count())->toBe($before);
});

test('creating a business with a short password fails validation', function () {
    Livewire::actingAs($this->superadmin)
        ->test(Dashboard::class)
        ->call('openCreateModal')
        ->set('name', 'Negocio Corto')
        ->set('address', 'Calle 9 # 9-9')
        ->set('phone', '3009998877')
        ->set('owner_name', 'Dueño Corto')
        ->set('owner_email', 'corto@fastlogi.test')
        ->set('password', '123')
        ->call('createBusiness')
        ->assertHasErrors(['password' => 'min']);

    expect(Business::where('name', 'Negocio Corto')->exists())->toBeFalse();
});

test('a superadmin can suspend a business from the panel', function () {
    $business = Business::factory()->create();
    $owner = User::factory()->owner($business)->create();

    Livewire::actingAs($this->superadmin)
        ->test(Dashboard::class)
        ->call('openSuspendModal', $business->id)
        ->assertSet('suspendTargetName', $business->name)
        ->set('suspendConfirmed', true)
        ->call('suspend')
        ->assertHasNoErrors()
        ->assertSet('showSuspendModal', false);

    expect($business->fresh()->isSuspended())->toBeTrue()
        ->and($owner->fresh()->status->value)->toBe('suspended');
});

test('suspending requires the confirmation checkbox', function () {
    $business = Business::factory()->create();

    Livewire::actingAs($this->superadmin)
        ->test(Dashboard::class)
        ->call('openSuspendModal', $business->id)
        ->call('suspend')
        ->assertHasErrors(['suspendConfirmed']);

    expect($business->fresh()->isSuspended())->toBeFalse();
});

test('a superadmin can reactivate a suspended business', function () {
    $business = Business::factory()->suspended()->create();
    User::factory()->owner($business)->suspended()->create();

    Livewire::actingAs($this->superadmin)
        ->test(Dashboard::class)
        ->call('reactivate', $business->id);

    expect($business->fresh()->isSuspended())->toBeFalse();
});

test('a superadmin can force the closure of the grace window', function () {
    $business = Business::factory()->suspended()->create();
    $product = Product::factory()->create(['business_id' => $business->id, 'stock' => 5]);
    $driverUser = User::factory()->driver($business)->create();
    $driver = DeliveryDriver::factory()->create(['user_id' => $driverUser->id, 'business_id' => $business->id]);

    $order = makeReservedOrder($business, $product, OrderStatus::Assigned, 1, $driver);

    Livewire::actingAs($this->superadmin)
        ->test(Dashboard::class)
        ->call('closeGrace', $business->id);

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($product->fresh()->stock)->toBe(5);
});

test('the search filters businesses by owner name', function () {
    $alpes = Business::factory()->create(['name' => 'Los Alpes']);
    User::factory()->owner($alpes)->create(['name' => 'Carlos Pérez']);

    $andina = Business::factory()->create(['name' => 'La Andina']);
    User::factory()->owner($andina)->create(['name' => 'Marta Ruiz']);

    Livewire::actingAs($this->superadmin)
        ->test(Dashboard::class)
        ->set('search', 'Marta')
        ->assertSee('La Andina')
        ->assertDontSee('Los Alpes');
});

test('the status filter shows only suspended businesses', function () {
    $active = Business::factory()->create(['name' => 'Negocio Activo']);
    $suspended = Business::factory()->suspended()->create(['name' => 'Negocio Suspendido']);

    Livewire::actingAs($this->superadmin)
        ->test(Dashboard::class)
        ->set('filter', 'suspendidos')
        ->assertSee('Negocio Suspendido')
        ->assertDontSee('Negocio Activo');
});
