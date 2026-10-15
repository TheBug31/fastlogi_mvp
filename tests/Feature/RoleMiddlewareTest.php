<?php

use App\Enums\BusinessStatus;
use App\Enums\UserStatus;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $this->get('/negocio')->assertRedirect(route('login'));
    $this->get('/superadmin')->assertRedirect(route('login'));
    $this->get('/repartidor/rutas')->assertRedirect(route('login'));
});

test('a user cannot access a panel that belongs to another role', function () {
    $business = Business::factory()->create();

    $superadmin = User::factory()->superadmin()->create();
    $owner = User::factory()->owner($business)->create();
    $driver = User::factory()->driver($business)->create();

    // Owner cannot reach superadmin or driver panels
    $this->actingAs($owner)->get('/superadmin')->assertForbidden();
    $this->actingAs($owner)->get('/repartidor/rutas')->assertForbidden();

    // Driver cannot reach owner or superadmin panels
    $this->actingAs($driver)->get('/negocio')->assertForbidden();
    $this->actingAs($driver)->get('/superadmin')->assertForbidden();

    // Superadmin cannot reach owner or driver panels
    $this->actingAs($superadmin)->get('/negocio')->assertForbidden();
    $this->actingAs($superadmin)->get('/repartidor/rutas')->assertForbidden();
});

test('each role can access its own panel', function () {
    $business = Business::factory()->create();

    $superadmin = User::factory()->superadmin()->create();
    $owner = User::factory()->owner($business)->create();
    $driver = User::factory()->driver($business)->create();

    $this->actingAs($superadmin)->get('/superadmin')->assertOk();
    $this->actingAs($owner)->get('/negocio')->assertOk();
    $this->actingAs($driver)->get('/repartidor/rutas')->assertOk();
});

test('a user suspended after logging in is logged out on the next request', function () {
    $business = Business::factory()->create();
    $owner = User::factory()->owner($business)->create();

    $owner->update(['status' => UserStatus::Suspended]);

    $this->actingAs($owner)
        ->get('/negocio')
        ->assertRedirect(route('login'))
        ->assertSessionHas('suspended', true);

    $this->assertGuest();
});

test('an owner whose business is suspended is logged out on the next request', function () {
    $business = Business::factory()->create();
    $owner = User::factory()->owner($business)->create();

    $business->update(['status' => BusinessStatus::Suspended, 'suspended_at' => now()]);

    $this->actingAs($owner)
        ->get('/negocio')
        ->assertRedirect(route('login'))
        ->assertSessionHas('suspended', true);

    $this->assertGuest();
});
