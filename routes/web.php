<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Driver\DriverTelemetryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// ── Autenticación ───────────────────────────────────────────
// The GET is intentionally not behind 'guest': the controller decides where an
// already-authenticated user should go (by role). The POST stays guest-only.
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.store')->middleware('guest');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ── SuperAdmin ──────────────────────────────────────────────
Route::prefix('superadmin')
    ->name('superadmin.')
    ->middleware(['auth', 'role:superadmin'])
    ->group(function () {
        Route::view('/', 'superadmin.dashboard')->name('dashboard');
        Route::view('/negocios', 'superadmin.dashboard')->name('businesses.index');
    });

// ── Dueño de negocio ────────────────────────────────────────
Route::prefix('negocio')
    ->name('owner.')
    ->middleware(['auth', 'role:owner'])
    ->group(function () {
        Route::view('/', 'owner.dashboard')->name('dashboard');
        Route::view('/pedidos', 'owner.orders.index')->name('orders.index');
        Route::view('/pedidos/nuevo', 'owner.orders.create')->name('orders.create');
        Route::view('/pedidos/{order?}', 'owner.orders.show')->name('orders.show');
        Route::view('/inventario', 'owner.inventory')->name('inventory.index');
        Route::view('/repartidores', 'owner.drivers')->name('drivers.index');
    });

// ── Repartidor (móvil) ──────────────────────────────────────
Route::prefix('repartidor')
    ->name('driver.')
    ->middleware(['auth', 'role:driver'])
    ->group(function () {
        Route::view('/rutas', 'driver.routes')->name('routes');
        Route::view('/pedidos/{order?}', 'driver.order-detail')->name('orders.show');
        Route::post('/telemetria', DriverTelemetryController::class)->name('telemetry');
    });
