<?php

use App\Enums\OrderStatus;
use App\Jobs\CloseGraceOrders;
use App\Jobs\ExpireOrderOffer;
use App\Models\Order;
use App\Models\Scopes\BusinessScope;
use App\Services\BusinessSuspensionService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    app(BusinessSuspensionService::class)
        ->expiredGraceBusinesses()
        ->each(fn ($business) => CloseGraceOrders::dispatch($business));
})
    ->everyMinute()
    ->name('close-expired-grace-windows')
    ->description('Cierra los pedidos en ventana de gracia vencidos (24 h)');

// Safety net for RF-002: if the delayed ExpireOrderOffer job is missed (e.g. the
// worker was down), this sweep returns any expired offer to pending.
Schedule::call(function () {
    Order::withoutGlobalScope(BusinessScope::class)
        ->where('status', OrderStatus::Offered->value)
        ->whereNotNull('offer_expires_at')
        ->where('offer_expires_at', '<=', now())
        ->pluck('id')
        ->each(fn (int $id) => ExpireOrderOffer::dispatch($id));
})
    ->everyMinute()
    ->name('expire-order-offers')
    ->description('Revierte las ofertas de pedido vencidas (90 s) no procesadas por el job');
