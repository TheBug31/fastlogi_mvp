<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Scopes\BusinessScope;
use App\Services\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Reverts an order to `pending` when the driver does not answer the offer within
 * the 90 second window (RF-002 §4.2.1). The scheduler runs an equivalent sweep
 * every minute as a safety net in case the worker misses the delayed job.
 */
class ExpireOrderOffer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId) {}

    public function handle(OrderService $orders): void
    {
        $order = Order::withoutGlobalScope(BusinessScope::class)->find($this->orderId);

        if (! $order) {
            return;
        }

        $orders->expireOffer($order);
    }
}
