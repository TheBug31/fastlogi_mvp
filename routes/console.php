<?php

use App\Jobs\CloseGraceOrders;
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
