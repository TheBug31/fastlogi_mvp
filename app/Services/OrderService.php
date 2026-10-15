<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PackageStatus;
use App\Enums\UserRole;
use App\Exceptions\BusinessSuspendedException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidOrderTransitionException;
use App\Exceptions\OfferNotAllowedException;
use App\Jobs\ExpireOrderOffer;
use App\Models\Business;
use App\Models\DeliveryDriver;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\Package;
use App\Models\Product;
use App\Models\Scopes\BusinessScope;
use App\Models\User;
use App\Notifications\OfferRejectedOrExpired;
use App\Notifications\OrderCancelled;
use App\Notifications\OrderOffered;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public const OFFER_WINDOW_SECONDS = 90;

    public function __construct(
        private InventoryService $inventory,
        private DriverAvailabilityService $availability,
    ) {}

    /**
     * Valid transition matrix of SRS §4.1.3.
     */
    public function canTransition(OrderStatus $from, OrderStatus $to): bool
    {
        $map = [
            OrderStatus::Pending->value => [OrderStatus::Offered->value, OrderStatus::Cancelled->value],
            OrderStatus::Offered->value => [
                OrderStatus::Assigned->value,
                OrderStatus::InTransit->value,
                OrderStatus::Pending->value,
                OrderStatus::Cancelled->value,
            ],
            OrderStatus::Assigned->value => [
                OrderStatus::InTransit->value,
                OrderStatus::Cancelled->value,
                OrderStatus::Pending->value,
            ],
            OrderStatus::InTransit->value => [OrderStatus::Delivered->value, OrderStatus::Incident->value],
        ];

        return in_array($to->value, $map[$from->value] ?? [], true);
    }

    /**
     * Create an order, reserve its stock under a pessimistic row lock and, when
     * the order requires traceability, create its package (RF-001 §4.1.1).
     *
     * @param  array<string, mixed>  $data
     */
    public function createOrder(array $data, User $actor): Order
    {
        if ($actor->business && $actor->business->isSuspended()) {
            throw new BusinessSuspendedException('El negocio está suspendido; no se pueden crear pedidos.');
        }

        $items = $data['items'] ?? [];

        if (empty($items)) {
            throw new InvalidOrderTransitionException('El pedido debe incluir al menos un producto.');
        }

        $alerts = [];

        // Retry up to 3 times on a database deadlock (SRS §4.1.1).
        $order = DB::transaction(function () use ($data, $items, $actor, &$alerts) {
            $lines = array_map(fn (array $item) => [
                'product_id' => (int) $item['product_id'],
                'quantity' => (int) $item['quantity'],
            ], $items);

            $products = Product::whereIn('id', array_column($lines, 'product_id'))
                ->get()
                ->keyBy('id');

            $order = Order::create([
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'delivery_address' => $data['delivery_address'],
                'payment_method' => $data['payment_method'],
                'requires_traceability' => (bool) ($data['requires_traceability'] ?? false),
                'status' => OrderStatus::Pending,
                'total' => 0,
            ]);

            $total = 0.0;

            foreach ($lines as $line) {
                /** @var Product|null $product */
                $product = $products[$line['product_id']] ?? null;

                if (! $product) {
                    throw new InsufficientStockException('Producto no encontrado o de otro negocio.');
                }

                $subtotal = round($product->price * $line['quantity'], 2);
                $total += $subtotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ]);
            }

            $order->update(['total' => $total]);

            $alerts = $this->inventory->reserve($order, $lines, $actor);

            if ($order->requires_traceability) {
                Package::create([
                    'order_id' => $order->id,
                    'tracking_code' => $this->trackingCode($order),
                    'status' => PackageStatus::Pending,
                ]);
            }

            return $order;
        }, 3);

        $this->inventory->dispatchAlerts($alerts);

        return $order->fresh(['items', 'package']);
    }

    /**
     * Offer a pending order to a driver (RF-002).
     */
    public function offerToDriver(Order $order, DeliveryDriver $driver, User $actor): Order
    {
        $fresh = DB::transaction(function () use ($order, $driver, $actor) {
            $locked = $this->lockOrder($order);

            $this->assertTransition($locked, OrderStatus::Offered);

            if ($locked->business_id !== $driver->business_id) {
                throw new OfferNotAllowedException('El repartidor no pertenece a este negocio.');
            }

            if ($locked->business->isSuspended()) {
                throw new BusinessSuspendedException('El negocio está suspendido; no se pueden ofrecer pedidos.');
            }

            $lockedDriver = DeliveryDriver::withoutGlobalScope(BusinessScope::class)
                ->whereKey($driver->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedDriver->isOffline()) {
                throw new OfferNotAllowedException('El repartidor está desconectado.');
            }

            if ($lockedDriver->activeOrdersCount() >= $lockedDriver->max_active_orders) {
                throw new OfferNotAllowedException('El repartidor alcanzó su capacidad máxima de pedidos activos.');
            }

            $now = now();

            $locked->update([
                'status' => OrderStatus::Offered,
                'delivery_driver_id' => $lockedDriver->id,
                'offered_at' => $now,
                'offer_expires_at' => $now->copy()->addSeconds(self::OFFER_WINDOW_SECONDS),
            ]);

            $this->log($locked, OrderStatus::Pending, OrderStatus::Offered, $actor);

            return $locked->fresh(['deliveryDriver.user']);
        });

        $driverUser = $fresh->deliveryDriver?->user;

        if ($driverUser) {
            $driverUser->notify(new OrderOffered(
                $fresh->id,
                $fresh->customer_name,
                $fresh->delivery_address,
                self::OFFER_WINDOW_SECONDS,
            ));
        }

        ExpireOrderOffer::dispatch($fresh->id)->delay(now()->addSeconds(self::OFFER_WINDOW_SECONDS));

        return $fresh;
    }

    /**
     * Driver accepts an offer within the window (RF-002).
     */
    public function acceptOffer(Order $order, DeliveryDriver $driver): Order
    {
        $result = DB::transaction(function () use ($order, $driver) {
            $locked = $this->lockOrder($order);

            if ($locked->status !== OrderStatus::Offered || $locked->delivery_driver_id !== $driver->id) {
                throw new InvalidOrderTransitionException('La oferta ya no está disponible.');
            }

            // The offer may have just expired while the driver was answering.
            if ($locked->offer_expires_at && now()->greaterThan($locked->offer_expires_at)) {
                $this->returnToPending($locked, null, 'Oferta expirada');

                return ['expired' => true, 'order' => $locked->fresh()];
            }

            $target = $locked->requires_traceability
                ? OrderStatus::Assigned
                : OrderStatus::InTransit;

            $locked->update(['status' => $target]);
            $this->log($locked, OrderStatus::Offered, $target, $driver->user);

            return ['expired' => false, 'order' => $locked->fresh()];
        });

        if ($result['expired']) {
            /** @var Order $expiredOrder */
            $expiredOrder = $result['order'];
            $this->notifyOwners($expiredOrder->business, new OfferRejectedOrExpired(
                $expiredOrder->id,
                $expiredOrder->customer_name,
                'Oferta expirada',
            ));
            $this->availability->refresh($driver);

            throw new InvalidOrderTransitionException('La oferta expiró antes de la aceptación.');
        }

        /** @var Order $accepted */
        $accepted = $result['order'];
        $this->availability->refresh($driver);

        return $accepted;
    }

    /**
     * Driver rejects an offer; the order returns to pending (RF-002).
     */
    public function rejectOffer(Order $order, DeliveryDriver $driver): void
    {
        $fresh = DB::transaction(function () use ($order, $driver) {
            $locked = $this->lockOrder($order);

            if ($locked->status !== OrderStatus::Offered || $locked->delivery_driver_id !== $driver->id) {
                throw new InvalidOrderTransitionException('No hay una oferta activa para rechazar.');
            }

            $this->returnToPending($locked, $driver->user, 'Rechazada por el repartidor');

            return $locked->fresh();
        });

        $this->notifyOwners($fresh->business, new OfferRejectedOrExpired(
            $fresh->id,
            $fresh->customer_name,
            'Rechazada por el repartidor',
        ));

        $this->availability->refresh($driver);
    }

    /**
     * Return an expired offer to pending. Idempotent: does nothing when the order
     * is no longer offered (RF-002 §4.2.1).
     */
    public function expireOffer(Order $order): bool
    {
        $outcome = DB::transaction(function () use ($order) {
            $locked = $this->lockOrder($order);

            if ($locked->status !== OrderStatus::Offered) {
                return null;
            }

            if ($locked->offer_expires_at && now()->lessThan($locked->offer_expires_at)) {
                return null;
            }

            $driverId = $this->returnToPending($locked, null, 'Oferta expirada');

            return ['order' => $locked->fresh(), 'driverId' => $driverId];
        });

        if ($outcome === null) {
            return false;
        }

        /** @var Order $fresh */
        $fresh = $outcome['order'];

        $this->notifyOwners($fresh->business, new OfferRejectedOrExpired(
            $fresh->id,
            $fresh->customer_name,
            'Oferta expirada',
        ));

        if ($outcome['driverId']) {
            $driver = DeliveryDriver::withoutGlobalScope(BusinessScope::class)->find($outcome['driverId']);

            if ($driver) {
                $this->availability->refresh($driver);
            }
        }

        return true;
    }

    /**
     * Cancel an order following the cancellation rules of SRS §4.1.4.
     */
    public function cancelOrder(Order $order, User $actor, ?string $reason = null): void
    {
        $outcome = DB::transaction(function () use ($order, $actor, $reason) {
            $locked = $this->lockOrder($order);

            if (! in_array($locked->status, [OrderStatus::Pending, OrderStatus::Offered, OrderStatus::Assigned], true)) {
                throw new InvalidOrderTransitionException('El pedido no se puede cancelar en su estado actual.');
            }

            $wasAssigned = $locked->status === OrderStatus::Assigned;

            if ($wasAssigned && blank($reason)) {
                throw new InvalidOrderTransitionException('La cancelación de un pedido asignado requiere un motivo.');
            }

            $from = $locked->status;
            $driverId = $locked->delivery_driver_id;

            $alerts = $this->inventory->release($locked, $actor);

            $locked->update([
                'status' => OrderStatus::Cancelled,
                'cancel_reason' => $reason ?: 'Cancelado por el dueño',
                'delivery_driver_id' => null,
                'offered_at' => null,
                'offer_expires_at' => null,
            ]);

            $this->log($locked, $from, OrderStatus::Cancelled, $actor, $reason);

            return ['alerts' => $alerts, 'driverId' => $driverId, 'order' => $locked->fresh()];
        });

        $this->inventory->dispatchAlerts($outcome['alerts']);

        if ($outcome['driverId']) {
            $driver = DeliveryDriver::withoutGlobalScope(BusinessScope::class)->find($outcome['driverId']);

            if ($driver) {
                $driver->user?->notify(new OrderCancelled(
                    $outcome['order']->id,
                    $outcome['order']->customer_name,
                    $outcome['order']->cancel_reason ?? 'Cancelado por el negocio',
                ));
                $this->availability->refresh($driver);
            }
        }
    }

    /**
     * Reassign an assigned order back to pending, e.g. because the driver went
     * inactive (SRS §4.1.3). A reason is mandatory.
     */
    public function reassignToPending(Order $order, User $actor, string $reason): void
    {
        $outcome = DB::transaction(function () use ($order, $actor, $reason) {
            $locked = $this->lockOrder($order);

            $this->assertTransition($locked, OrderStatus::Pending);

            if (blank($reason)) {
                throw new InvalidOrderTransitionException('La reasignación requiere un motivo.');
            }

            $driverId = $this->returnToPending($locked, $actor, $reason);

            return ['order' => $locked->fresh(), 'driverId' => $driverId];
        });

        if ($outcome['driverId']) {
            $driver = DeliveryDriver::withoutGlobalScope(BusinessScope::class)->find($outcome['driverId']);

            if ($driver) {
                $this->availability->refresh($driver);
            }
        }
    }

    /**
     * Driver scans the pickup, moving an assigned order to in transit (CU14).
     */
    public function markInTransit(Order $order, User $actor): void
    {
        DB::transaction(function () use ($order, $actor) {
            $locked = $this->lockOrder($order);
            $this->assertTransition($locked, OrderStatus::InTransit);

            $from = $locked->status;
            $locked->update(['status' => OrderStatus::InTransit]);
            $this->log($locked, $from, OrderStatus::InTransit, $actor);
        });
    }

    /**
     * Mark an in-transit order as delivered (final state, CU15/CU16).
     */
    public function markDelivered(Order $order, User $actor): void
    {
        $driverId = DB::transaction(function () use ($order, $actor) {
            $locked = $this->lockOrder($order);
            $this->assertTransition($locked, OrderStatus::Delivered);

            $from = $locked->status;
            $driverId = $locked->delivery_driver_id;
            $locked->update(['status' => OrderStatus::Delivered]);
            $this->log($locked, $from, OrderStatus::Delivered, $actor);

            return $driverId;
        });

        if ($driverId) {
            $driver = DeliveryDriver::withoutGlobalScope(BusinessScope::class)->find($driverId);

            if ($driver) {
                $this->availability->refresh($driver);
            }
        }
    }

    /**
     * Register an incident for an in-transit order (SRS §4.1.3). Stock is not
     * returned yet; that happens once the devolución is confirmed.
     */
    public function registerIncident(Order $order, User $actor, string $reason): void
    {
        $driverId = DB::transaction(function () use ($order, $actor, $reason) {
            $locked = $this->lockOrder($order);
            $this->assertTransition($locked, OrderStatus::Incident);

            if (blank($reason)) {
                throw new InvalidOrderTransitionException('El registro de una incidencia requiere un motivo.');
            }

            $from = $locked->status;
            $driverId = $locked->delivery_driver_id;

            $locked->update([
                'status' => OrderStatus::Incident,
                'cancel_reason' => $reason,
            ]);

            $this->log($locked, $from, OrderStatus::Incident, $actor, $reason);

            return $driverId;
        });

        if ($driverId) {
            $driver = DeliveryDriver::withoutGlobalScope(BusinessScope::class)->find($driverId);

            if ($driver) {
                $driver->user?->notify(new OrderCancelled(
                    $order->id,
                    $order->customer_name,
                    $reason,
                    'incident',
                ));
                $this->availability->refresh($driver);
            }
        }
    }

    /**
     * Confirm the reception of a returned package, returning the stock to the
     * inventory (SRS §4.1.4, CU19). Idempotent.
     */
    public function confirmDevolution(Order $order, User $actor): void
    {
        $alerts = DB::transaction(function () use ($order, $actor) {
            $locked = $this->lockOrder($order);

            if ($locked->status !== OrderStatus::Incident) {
                throw new InvalidOrderTransitionException('Solo se puede confirmar la devolución de un pedido en incidencia.');
            }

            return $this->inventory->returnStock($locked, $actor);
        });

        $this->inventory->dispatchAlerts($alerts);
    }

    /**
     * Move an offered order back to pending, clearing the offer data. Returns the
     * previous driver id so the caller can refresh their availability.
     */
    private function returnToPending(Order $order, ?User $actor, string $reason): ?int
    {
        $previousDriverId = $order->delivery_driver_id;

        $order->update([
            'status' => OrderStatus::Pending,
            'delivery_driver_id' => null,
            'offered_at' => null,
            'offer_expires_at' => null,
        ]);

        $this->log($order, OrderStatus::Offered, OrderStatus::Pending, $actor, $reason);

        return $previousDriverId;
    }

    private function assertTransition(Order $order, OrderStatus $to): void
    {
        if (! $this->canTransition($order->status, $to)) {
            throw new InvalidOrderTransitionException(
                "Transición no permitida: {$order->status->value} → {$to->value}.",
            );
        }
    }

    private function log(Order $order, OrderStatus $from, OrderStatus $to, ?User $actor, ?string $reason = null): void
    {
        OrderStatusLog::create([
            'order_id' => $order->id,
            'from' => $from->value,
            'to' => $to->value,
            'user_id' => $actor?->id,
            'reason' => $reason,
        ]);
    }

    private function lockOrder(Order $order): Order
    {
        return Order::withoutGlobalScope(BusinessScope::class)
            ->whereKey($order->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function trackingCode(Order $order): string
    {
        return 'FL-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
    }

    private function notifyOwners(?Business $business, Notification $notification): void
    {
        if (! $business) {
            return;
        }

        $business->users()
            ->where('role', UserRole::Owner->value)
            ->get()
            ->each(fn (User $owner) => $owner->notify($notification));
    }
}
