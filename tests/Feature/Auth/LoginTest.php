<?php

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('login form is displayed to guests', function () {
    $this->get(route('login'))
        ->assertStatus(200)
        ->assertSee('Iniciar sesión');
});

test('an authenticated user is redirected away from the login form', function () {
    $business = Business::factory()->create();
    $owner = User::factory()->owner($business)->create();

    $this->actingAs($owner)
        ->get(route('login'))
        ->assertRedirect(route('owner.dashboard'));
});

test('a user can log in with their email', function () {
    $business = Business::factory()->create();
    $owner = User::factory()->owner($business)->create([
        'email' => 'dueno@negocio.test',
        'password' => 'password123',
    ]);

    $this->post(route('login.store'), [
        'identifier' => 'dueno@negocio.test',
        'password' => 'password123',
    ])->assertRedirect(route('owner.dashboard'));

    $this->assertAuthenticatedAs($owner);
});

test('a user can log in with their phone number', function () {
    $business = Business::factory()->create();
    $owner = User::factory()->owner($business)->create([
        'phone' => '3101234567',
        'password' => 'password123',
    ]);

    $this->post(route('login.store'), [
        'identifier' => '3101234567',
        'password' => 'password123',
    ])->assertRedirect(route('owner.dashboard'));

    $this->assertAuthenticatedAs($owner);
});

test('each role is redirected to its own panel', function () {
    $business = Business::factory()->create();

    $superadmin = User::factory()->superadmin()->create(['password' => 'password123']);
    $driver = User::factory()->driver($business)->create(['password' => 'password123']);

    $this->post(route('login.store'), [
        'identifier' => $superadmin->email,
        'password' => 'password123',
    ])->assertRedirect(route('superadmin.dashboard'));

    $this->post(route('logout'));

    $this->post(route('login.store'), [
        'identifier' => $driver->email,
        'password' => 'password123',
    ])->assertRedirect(route('driver.routes'));
});

test('invalid credentials are rejected without authenticating', function () {
    $business = Business::factory()->create();
    User::factory()->owner($business)->create([
        'email' => 'dueno@negocio.test',
        'password' => 'password123',
    ]);

    $this->from(route('login'))
        ->post(route('login.store'), [
            'identifier' => 'dueno@negocio.test',
            'password' => 'contrasena-incorrecta',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('identifier');

    $this->assertGuest();
});

test('a suspended user is rejected with the suspended flash', function () {
    $business = Business::factory()->create();
    $owner = User::factory()->owner($business)->suspended()->create([
        'email' => 'suspendido@negocio.test',
        'password' => 'password123',
    ]);

    $this->from(route('login'))
        ->post(route('login.store'), [
            'identifier' => 'suspendido@negocio.test',
            'password' => 'password123',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHas('suspended', true);

    $this->assertGuest();
});

test('an inactive user is rejected with the suspended flash', function () {
    $business = Business::factory()->create();
    User::factory()->owner($business)->inactive()->create([
        'email' => 'inactivo@negocio.test',
        'password' => 'password123',
    ]);

    $this->from(route('login'))
        ->post(route('login.store'), [
            'identifier' => 'inactivo@negocio.test',
            'password' => 'password123',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHas('suspended', true);

    $this->assertGuest();
});

test('the owner of a suspended business cannot log in', function () {
    $business = Business::factory()->suspended()->create();
    $owner = User::factory()->owner($business)->create([
        'email' => 'dueno@negocio.test',
        'password' => 'password123',
    ]);

    $this->from(route('login'))
        ->post(route('login.store'), [
            'identifier' => 'dueno@negocio.test',
            'password' => 'password123',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHas('suspended', true);

    $this->assertGuest();
});

test('a driver of a suspended business can still log in for the grace window', function () {
    $business = Business::factory()->suspended()->create();
    $driver = User::factory()->driver($business)->create([
        'email' => 'repartidor@negocio.test',
        'password' => 'password123',
    ]);

    $this->post(route('login.store'), [
        'identifier' => 'repartidor@negocio.test',
        'password' => 'password123',
    ])->assertRedirect(route('driver.routes'));

    $this->assertAuthenticatedAs($driver);
});

test('a user can log out', function () {
    $business = Business::factory()->create();
    $owner = User::factory()->owner($business)->create();

    $this->actingAs($owner)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
