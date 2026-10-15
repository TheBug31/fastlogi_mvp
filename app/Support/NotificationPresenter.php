<?php

namespace App\Support;

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
            return route('owner.orders.show', $data['order_id']);
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
     * Material Symbols icon name for the notification type.
     *
     * @param  array<string, mixed>  $data
     */
    public static function icon(array $data): string
    {
        return match ($data['type'] ?? null) {
            'low_stock' => 'inventory_2',
            'out_of_stock' => 'production_quantity_limits',
            'offer_rejected_or_expired' => 'replay',
            'order_cancelled' => 'cancel',
            'order_incident' => 'report',
            'driver_inactive' => 'wifi_off',
            'order_offered' => 'local_shipping',
            default => 'notifications',
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
            'out_of_stock', 'order_incident', 'driver_inactive' => 'text-error',
            'low_stock', 'offer_rejected_or_expired', 'order_cancelled' => 'text-tertiary',
            default => 'text-primary',
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
