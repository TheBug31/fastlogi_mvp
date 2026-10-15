<?php

test('root redirects to login', function () {
    $response = $this->get('/');
    $response->assertRedirect(route('login'));
});

test('login page renders correctly', function () {
    $response = $this->get('/login');
    $response->assertStatus(200);
});

test('superadmin dashboard renders correctly in scaffold', function () {
    $response = $this->get('/superadmin');
    $response->assertStatus(200);
});

test('owner views render correctly in scaffold', function () {
    $this->get('/negocio')->assertStatus(200);
    $this->get('/negocio/pedidos')->assertStatus(200);
    $this->get('/negocio/pedidos/nuevo')->assertStatus(200);
    $this->get('/negocio/pedidos/1')->assertStatus(200);
    $this->get('/negocio/inventario')->assertStatus(200);
    $this->get('/negocio/repartidores')->assertStatus(200);
});

test('driver views render correctly in scaffold', function () {
    $this->get('/repartidor/rutas')->assertStatus(200);
    $this->get('/repartidor/pedidos/1')->assertStatus(200);
});
