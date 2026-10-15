<?php

namespace App\Services;

use App\Enums\BusinessStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Business;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Scopes\BusinessScope;
use App\Models\User;
use App\Notifications\OrderCancelled;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BusinessSuspensionService
{
    public const GRACE_WINDOW_HOURS = 24;

    public const REASON_SUSPENDED = 'Negocio suspendido';

    public const REASON_ADMIN_CLOSE = 'Cierre administrativo de la ventana de gracia';

    public const REASON_GRACE_EXPIRED = 'Vencimiento de la ventana de gracia (24 h)';

    public function __construct(private InventoryService $inventory) {}

    /**
     * Suspend a business and apply the rules of SRS §4.3.1 (RF-003).
     *
     * The owner is blocked immediately, `offered` orders are cancelled releasing
     * their reservation, `pending` orders stay frozen and `assigned`/`in_transit`
     * orders keep a 24-hour grace window.
     */
    public function suspend(Business $business, ?User $actor = null): void
    {
        if ($business->isSuspended()) {
            return;
        }

        $notifications = [];

        DB::transaction(function () use ($business, $actor, &$notifications) {
            $business->forceFill([
                'status' => BusinessStatus::Suspended,
                'suspended_at' => now(),
            ])->save();

            // Block the owner's login at once (RF-003 / RF-010).
            $business->users()
                ->where('role', UserRole::Owner->value)
                ->update(['status' => UserStatus::Suspended]);

            $offered = $this->tenantOrders($business)
                ->where('status', OrderStatus::Offered->value)
                ->lockForUpdate()
                ->get();

            foreach ($offered as $order) {
                $notifications[] = $this->cancelOrder($order, $actor, self::REASON_SUSPENDED);
            }
        });

        $this->notifyDrivers($notifications);
    }

    /**
     * Reactivate a business: restores the owner's access and normal operation.
     */
    public function reactivate(Business $business): void
    {
        DB::transaction(function () use ($business) {
            $business->forceFill([
                'status' => BusinessStatus::Active,
                'suspended_at' => null,
            ])->save();

            $business->users()
                ->where('role', UserRole::Owner->value)
                ->update(['status' => UserStatus::Active]);
        });
    }

    /**
     * Close the grace-window orders of a suspended business (RF-003, CU20).
     *
     * `assigned` orders become cancelled (releasing their reservation) and
     * `in_transit` orders become incidents. Returns how many orders were closed.
     */
    public function closeGraceOrders(
        Business $business,
        ?User $actor = null,
        string $reason = self::REASON_ADMIN_CLOSE,
    ): int {
        if (! $business->isSuspended()) {
            return 0;
        }

        $notifications = [];

        DB::transaction(function () use ($business, $actor, $reason, &$notifications) {
            $orders = $this->tenantOrders($business)
                ->whereIn('status', [OrderStatus::Assigned->value, OrderStatus::InTransit->value])
                ->lockForUpdate()
                ->get();

            foreach ($orders as $order) {
                $notifications[] = $order->status === OrderStatus::Assigned
                    ? $this->cancelOrder($order, $actor, $reason)
                    : $this->markIncident($order, $actor, $reason);
            }
        });

        $this->notifyDrivers($notifications);

        return count($notifications);
    }

    /**
     * Suspended businesses whose 24-hour grace window has elapsed and still hold
     * orders in a grace state.
     *
     * @return Collection<int, Business>
     */
    public function expiredGraceBusinesses(): Collection
    {
        return Business::query()
            ->where('status', BusinessStatus::Suspended->value)
            ->whereNotNull('suspended_at')
            ->where('suspended_at', '<=', now()->subHours(self::GRACE_WINDOW_HOURS))
            ->whereHas('orders', function (Builder $query) {
                $query->whereIn('status', [
                    OrderStatus::Assigned->value,
                    OrderStatus::InTransit->value,
                ]);
            })
            ->get();
    }

    /**
     * Close every business whose grace window has expired (scheduled task).
     */
    public function closeExpiredGraceWindows(): int
    {
        return $this->expiredGraceBusinesses()->reduce(
            fn (int $total, Business $business) => $total + $this->closeGraceOrders(
                $business,
                null,
                self::REASON_GRACE_EXPIRED,
            ),
            0,
        );
    }

    /**
     * Cancel an order, releasing its reservation, and return the data needed to
     * notify the assigned driver afterwards (outside the transaction).
     *
     * @return array{driver: ?User, order: Order, outcome: string}
     */
    private function cancelOrder(Order $order, ?User $actor, string $reason): array
    {
        $previous = $order->status;

        $this->inventory->releaseForOrder($order, $actor);

        $order->forceFill([
            'status' => OrderStatus::Cancelled,
            'cancel_reason' => $reason,
        ])->save();

        $this->logStatus($order, $previous, OrderStatus::Cancelled, $actor, $reason);

        return ['driver' => $order->deliveryDriver?->user, 'order' => $order, 'outcome' => 'cancelled'];
    }

    /**
     * Register an incident for an in-transit order. Stock is not released here:
     * the reservation is returned only when the devolución is confirmed (CU19).
     *
     * @return array{driver: ?User, order: Order, outcome: string}
     */
    private function markIncident(Order $order, ?User $actor, string $reason): array
    {
        $previous = $order->status;

        $order->forceFill([
            'status' => OrderStatus::Incident,
            'cancel_reason' => $reason,
        ])->save();

        $this->logStatus($order, $previous, OrderStatus::Incident, $actor, $reason);

        return ['driver' => $order->deliveryDriver?->user, 'order' => $order, 'outcome' => 'incident'];
    }

    private function logStatus(Order $order, OrderStatus $from, OrderStatus $to, ?User $actor, string $reason): void
    {
        OrderStatusLog::create([
            'order_id' => $order->id,
            'from' => $from->value,
            'to' => $to->value,
            'user_id' => $actor?->id,
            'reason' => $reason,
        ]);
    }

    /**
     * @param  array<int, array{driver: ?User, order: Order, outcome: string}>  $notifications
     */
    private function notifyDrivers(array $notifications): void
    {
        foreach ($notifications as $item) {
            if ($item['driver'] === null) {
                continue;
            }

            $item['driver']->notify(new OrderCancelled(
                $item['order']->id,
                $item['order']->customer_name,
                $item['order']->cancel_reason ?? '',
                $item['outcome'],
            ));
        }
    }

    /**
     * Orders of a business ignoring the multi-tenant scope (workers/SuperAdmin).
     */
    private function tenantOrders(Business $business): Builder
    {
        return Order::withoutGlobalScope(BusinessScope::class)
            ->where('business_id', $business->id);
    }
}
