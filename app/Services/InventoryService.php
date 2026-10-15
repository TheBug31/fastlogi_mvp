<?php

namespace App\Services;

use App\Enums\InventoryMovementType;
use App\Enums\UserRole;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\ProductInUseException;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\Scopes\BusinessScope;
use App\Models\User;
use App\Notifications\LowStockAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class InventoryService
{
    /**
     * Create a product and evaluate its initial alert state (RF-008).
     *
     * @param  array<string, mixed>  $data
     */
    public function createProduct(array $data, ?User $actor = null): Product
    {
        $product = Product::create([
            'name' => $data['name'],
            'barcode' => $data['barcode'] ?? null,
            'price' => $data['price'],
            'stock' => $data['stock'] ?? 0,
            'min_stock' => $data['min_stock'] ?? 0,
            'unit' => $data['unit'] ?? 'unidad',
            'low_stock_alerted' => false,
            'out_of_stock_alerted' => false,
        ]);

        $alerts = [];

        DB::transaction(function () use ($product, &$alerts) {
            $locked = $this->lockProduct($product->business_id, $product->id);
            $this->reconcileAlertFlags($locked, $alerts);
            $locked->save();
        });

        $this->dispatchAlerts($alerts);

        return $product->refresh();
    }

    /**
     * Update a product. Stock changes are recorded as an `adjustment` movement and
     * trigger/re-arm the low-stock alerts; other fields are updated in place.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProduct(Product $product, array $data, ?User $actor = null): Product
    {
        $alerts = [];

        DB::transaction(function () use ($product, $data, $actor, &$alerts) {
            $locked = $this->lockProduct($product->business_id, $product->id);

            $locked->fill([
                'name' => $data['name'] ?? $locked->name,
                'barcode' => $data['barcode'] ?? null,
                'price' => $data['price'] ?? $locked->price,
                'min_stock' => $data['min_stock'] ?? $locked->min_stock,
                'unit' => $data['unit'] ?? $locked->unit,
            ]);

            $newStock = (int) ($data['stock'] ?? $locked->stock);
            $delta = $newStock - $locked->stock;

            if ($delta !== 0) {
                $this->applyDelta($locked, $delta, InventoryMovementType::Adjustment, null, $actor, $alerts);
            } else {
                $this->reconcileAlertFlags($locked, $alerts);
                $locked->save();
            }
        });

        $this->dispatchAlerts($alerts);

        return $product->refresh();
    }

    /**
     * Add stock (restock). Re-arms the low-stock alerts when the minimum is reached.
     */
    public function restock(Product $product, int $quantity, ?User $actor = null): void
    {
        if ($quantity <= 0) {
            throw new InsufficientStockException('La cantidad a reponer debe ser mayor que cero.');
        }

        $alerts = [];

        DB::transaction(function () use ($product, $quantity, $actor, &$alerts) {
            $locked = $this->lockProduct($product->business_id, $product->id);
            $this->applyDelta($locked, $quantity, InventoryMovementType::Restock, null, $actor, $alerts);
        });

        $this->dispatchAlerts($alerts);
    }

    /**
     * Set an absolute stock value, recording an `adjustment` movement.
     */
    public function adjustStock(Product $product, int $newStock, ?User $actor = null): void
    {
        if ($newStock < 0) {
            throw new InsufficientStockException('El stock no puede ser negativo.');
        }

        $alerts = [];

        DB::transaction(function () use ($product, $newStock, $actor, &$alerts) {
            $locked = $this->lockProduct($product->business_id, $product->id);
            $delta = $newStock - $locked->stock;

            if ($delta !== 0) {
                $this->applyDelta($locked, $delta, InventoryMovementType::Adjustment, null, $actor, $alerts);
            }
        });

        $this->dispatchAlerts($alerts);
    }

    /**
     * Reserve stock for an order (RF-001). Products are locked in ascending
     * product_id order to avoid deadlocks; if any line has insufficient stock the
     * whole reservation is rolled back and nothing is discounted.
     *
     * @param  array<int, array{product_id: int, quantity: int}>  $lines
     */
    public function reserveForOrder(Order $order, array $lines, ?User $actor = null): void
    {
        $alerts = [];

        DB::transaction(function () use ($order, $lines, $actor, &$alerts) {
            usort($lines, fn (array $a, array $b) => $a['product_id'] <=> $b['product_id']);

            foreach ($lines as $line) {
                $product = $this->lockProduct($order->business_id, (int) $line['product_id']);

                $this->applyDelta(
                    $product,
                    -abs((int) $line['quantity']),
                    InventoryMovementType::Reserve,
                    $order,
                    $actor,
                    $alerts,
                );
            }
        });

        $this->dispatchAlerts($alerts);
    }

    /**
     * Release the stock reserved by an order back into inventory (RF-001, RF-003).
     * Idempotent: an order is only released once.
     */
    public function releaseForOrder(Order $order, ?User $actor = null): void
    {
        $this->moveOrderStock($order, InventoryMovementType::Release, $actor);
    }

    /**
     * Return the stock of an order after a confirmed devolución (RF-001, CU19).
     * Idempotent: an order is only returned once.
     */
    public function returnForOrder(Order $order, ?User $actor = null): void
    {
        $this->moveOrderStock($order, InventoryMovementType::Return, $actor);
    }

    /**
     * Delete a product. Products that already belong to orders cannot be removed
     * because the foreign keys cascade and would corrupt the order history.
     */
    public function deleteProduct(Product $product): void
    {
        if ($product->orderItems()->exists()) {
            throw new ProductInUseException('No se puede eliminar un producto que ya tiene pedidos asociados.');
        }

        $product->delete();
    }

    /**
     * @param  InventoryMovementType  $type  Release or Return.
     */
    private function moveOrderStock(Order $order, InventoryMovementType $type, ?User $actor): void
    {
        $alerts = [];

        DB::transaction(function () use ($order, $type, $actor, &$alerts) {
            $items = $order->items()->orderBy('product_id')->get();

            if ($items->isEmpty()) {
                return;
            }

            // Idempotency guard: never release/return the same order twice.
            $alreadyDone = InventoryMovement::where('order_id', $order->id)
                ->where('type', $type->value)
                ->exists();

            if ($alreadyDone) {
                return;
            }

            foreach ($items as $item) {
                $product = $this->lockProduct($order->business_id, $item->product_id);
                $this->applyDelta($product, $item->quantity, $type, $order, $actor, $alerts);
            }
        });

        $this->dispatchAlerts($alerts);
    }

    /**
     * Apply a stock delta to a locked product, record the movement and update the
     * alert flags.
     *
     * @param  array<int, array{product: Product, type: string}>  $alerts
     */
    private function applyDelta(
        Product $product,
        int $delta,
        InventoryMovementType $type,
        ?Order $order,
        ?User $actor,
        array &$alerts,
    ): void {
        $after = $product->stock + $delta;

        if ($after < 0) {
            throw new InsufficientStockException(
                "Stock insuficiente para «{$product->name}»: disponible {$product->stock}, solicitado ".abs($delta).'.',
            );
        }

        $product->stock = $after;

        $this->reconcileAlertFlags($product, $alerts);
        $product->save();

        if ($delta !== 0) {
            InventoryMovement::create([
                'product_id' => $product->id,
                'order_id' => $order?->id,
                'type' => $type,
                'quantity' => $delta,
                'user_id' => $actor?->id,
            ]);
        }
    }

    /**
     * Evaluate the low-stock / out-of-stock alert rules of SRS §4.8.1.
     *
     * Fires the low-stock alert when the product is below its minimum and was not
     * already alerted; fires the out-of-stock alert once when it reaches zero and
     * was not already alerted. Re-arms both flags when stock returns to or above
     * the minimum. Alerts are only evaluated when the business tracks a minimum.
     *
     * @param  array<int, array{product: Product, type: string}>  $alerts
     */
    private function reconcileAlertFlags(Product $product, array &$alerts): void
    {
        if ($product->min_stock <= 0) {
            return;
        }

        if ($product->stock >= $product->min_stock) {
            $product->low_stock_alerted = false;
            $product->out_of_stock_alerted = false;

            return;
        }

        if (! $product->low_stock_alerted) {
            $product->low_stock_alerted = true;
            $alerts[] = ['product' => $product, 'type' => LowStockAlert::LOW_STOCK];
        }

        if ($product->stock === 0 && ! $product->out_of_stock_alerted) {
            $product->out_of_stock_alerted = true;
            $alerts[] = ['product' => $product, 'type' => LowStockAlert::OUT_OF_STOCK];
        }
    }

    /**
     * @param  array<int, array{product: Product, type: string}>  $alerts
     */
    private function dispatchAlerts(array $alerts): void
    {
        foreach ($alerts as $alert) {
            /** @var Product $product */
            $product = $alert['product'];

            $owners = $product->business->users()
                ->where('role', UserRole::Owner->value)
                ->get();

            Notification::send($owners, new LowStockAlert($product, $alert['type']));
        }
    }

    private function lockProduct(int $businessId, int $productId): Product
    {
        $product = Product::withoutGlobalScope(BusinessScope::class)
            ->where('business_id', $businessId)
            ->whereKey($productId)
            ->lockForUpdate()
            ->first();

        if (! $product) {
            throw new InsufficientStockException('Producto no encontrado.');
        }

        return $product;
    }
}
