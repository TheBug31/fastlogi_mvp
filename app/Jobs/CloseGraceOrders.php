<?php

namespace App\Jobs;

use App\Models\Business;
use App\Services\BusinessSuspensionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CloseGraceOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Business $business) {}

    public function handle(BusinessSuspensionService $suspension): void
    {
        $suspension->closeGraceOrders(
            $this->business,
            null,
            BusinessSuspensionService::REASON_GRACE_EXPIRED,
        );
    }
}
