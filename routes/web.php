<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::view('/login', 'auth.login')->name('login');

// ── SuperAdmin ──────────────────────────────────────────────
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    Route::view('/', 'superadmin.dashboard')->name('dashboard');
    Route::view('/negocios', 'superadmin.dashboard')->name('businesses.index');
});

// ── Dueño de negocio ────────────────────────────────────────
Route::prefix('negocio')->name('owner.')->group(function () {
    Route::view('/', 'owner.dashboard')->name('dashboard');
    Route::view('/pedidos', 'owner.orders.index')->name('orders.index');
    Route::view('/pedidos/nuevo', 'owner.orders.create')->name('orders.create');
    Route::view('/pedidos/{order?}', 'owner.orders.show')->name('orders.show');
    Route::view('/inventario', 'owner.inventory')->name('inventory.index');
    Route::view('/repartidores', 'owner.drivers')->name('drivers.index');
});

// ── Repartidor (móvil) ──────────────────────────────────────
Route::prefix('repartidor')->name('driver.')->group(function () {
    Route::view('/rutas', 'driver.routes')->name('routes');
    Route::view('/pedidos/{order?}', 'driver.order-detail')->name('orders.show');
});
