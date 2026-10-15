<?php

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('root redirects to login', function () {
    $response = $this->get('/');
    $response->assertRedirect(route('login'));
});

test('login page renders correctly', function () {
    $response = $this->get('/login');
    $response->assertStatus(200);
});

test('guests are redirected to login from every panel', function () {
    $this->get('/superadmin')->assertRedirect(route('login'));
    $this->get('/negocio')->assertRedirect(route('login'));
    $this->get('/repartidor/rutas')->assertRedirect(route('login'));
});

test('superadmin dashboard renders correctly in scaffold', function () {
    $superadmin = User::factory()->superadmin()->create();

    $this->actingAs($superadmin)
        ->get('/superadmin')
        ->assertStatus(200);
});

test('owner views render correctly in scaffold', function () {
    $business = Business::factory()->create();
    $owner = User::factory()->owner($business)->create();

    $this->actingAs($owner);

    $this->get('/negocio')->assertStatus(200);
    $this->get('/negocio/pedidos')->assertStatus(200);
    $this->get('/negocio/pedidos/nuevo')->assertStatus(200);
    $this->get('/negocio/pedidos/1')->assertStatus(200);
    $this->get('/negocio/inventario')->assertStatus(200);
    $this->get('/negocio/repartidores')->assertStatus(200);
});

test('driver views render correctly in scaffold', function () {
    $business = Business::factory()->create();
    $driver = User::factory()->driver($business)->create();

    $this->actingAs($driver);

    $this->get('/repartidor/rutas')->assertStatus(200);
    $this->get('/repartidor/pedidos/1')->assertStatus(200);
});
