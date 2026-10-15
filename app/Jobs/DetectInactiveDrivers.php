<?php

namespace App\Jobs;

use App\Services\DriverTelemetryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Scheduled sweep that marks drivers with accepted orders as offline when their
 * last GPS ping exceeds the business inactivity threshold (RF-011, CU17).
 */
class DetectInactiveDrivers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(DriverTelemetryService $telemetry): void
    {
        $telemetry->inactiveDrivers()
            ->each(fn ($driver) => $telemetry->flagInactive($driver));
    }
}
