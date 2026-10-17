<?php

namespace App\Support;

use App\Models\Order;

/**
 * Maps the `data` payload of the in-app notifications (RF-002, RF-008, RF-011)
 * to presentation helpers shared by the header bell and the notification center.
 */
class NotificationPresenter
{
    /**
     * Deep link to the resource a notification refers to, if any.
     *
     * @param  array<string, mixed>  $data
     */
    public static function url(array $data): ?string
    {
        if (! empty($data['order_id'])) {
            $order = Order::withoutGlobalScopes()->find($data['order_id']);

            return route('owner.orders.show', $order ?? $data['order_id']);
        }

        if (! empty($data['product_id'])) {
            return route('owner.inventory.index');
        }

        if (! empty($data['driver_id'])) {
            return route('owner.drivers.index');
        }

        return null;
    }

    /**
     * Heroicons outline icon name (sin el prefijo "heroicon-o-") para el tipo de
     * notificación; lo consume <x-icon :name="...">.
     *
     * @param  array<string, mixed>  $data
     */
    public static function icon(array $data): string
    {
        return match ($data['type'] ?? null) {
            'low_stock' => 'archive-box',
            'out_of_stock' => 'archive-box-x-mark',
            'offer_rejected_or_expired' => 'arrow-path',
            'order_cancelled' => 'x-circle',
            'order_incident' => 'exclamation-triangle',
            'driver_inactive' => 'signal-slash',
            'order_offered' => 'truck',
            default => 'bell',
        };
    }

    /**
     * Tailwind text color class used to tint the icon per severity.
     *
     * @param  array<string, mixed>  $data
     */
    public static function tone(array $data): string
    {
        return match ($data['type'] ?? null) {
            'out_of_stock', 'order_incident', 'driver_inactive' => 'text-red-600',
            'low_stock', 'offer_rejected_or_expired', 'order_cancelled' => 'text-amber-600',
            default => 'text-teal-600',
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function message(array $data): string
    {
        return (string) ($data['message'] ?? 'Nueva notificación.');
    }
}
