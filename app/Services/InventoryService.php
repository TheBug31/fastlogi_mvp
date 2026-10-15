<?php

namespace App\Services;

use App\Enums\InventoryMovementType;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\Scopes\BusinessScope;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Release the stock reserved by an order back into inventory (RF-001, RF-003).
     *
     * Locks the affected product rows in ascending product_id order to avoid
     * deadlocks, increments their stock and records a `release` movement.
     * The operation is idempotent: an order is only released once.
     */
    public function releaseForOrder(Order $order, ?User $actor = null): void
    {
        DB::transaction(function () use ($order, $actor) {
            $items = $order->items()->orderBy('product_id')->get();

            if ($items->isEmpty()) {
                return;
            }

            // Idempotency guard: never release the same order twice.
            $alreadyReleased = InventoryMovement::where('order_id', $order->id)
                ->where('type', InventoryMovementType::Release->value)
                ->exists();

            if ($alreadyReleased) {
                return;
            }

            foreach ($items as $item) {
                $product = Product::withoutGlobalScope(BusinessScope::class)
                    ->where('business_id', $order->business_id)
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
                    continue;
                }

                $product->stock += $item->quantity;

                // Re-arm the low stock alert once stock reaches the minimum again.
                if ($product->stock >= $product->min_stock) {
                    $product->low_stock_alerted = false;
                }

                $product->save();

                InventoryMovement::create([
                    'product_id' => $product->id,
                    'order_id' => $order->id,
                    'type' => InventoryMovementType::Release,
                    'quantity' => $item->quantity,
                    'user_id' => $actor?->id,
                ]);
            }
        });
    }
}
