<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Warns the owner that a driver with accepted orders stopped reporting location
 * (RF-011). The owner can then reassign `assigned` orders or register an incident
 * for `in_transit` ones.
 */
class DriverInactive extends Notification
{
    public function __construct(
        public int $driverId,
        public string $driverName,
        public int $thresholdMinutes,
        public int $pendingOrders,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'driver_inactive',
            'driver_id' => $this->driverId,
            'driver_name' => $this->driverName,
            'threshold_minutes' => $this->thresholdMinutes,
            'pending_orders' => $this->pendingOrders,
            'message' => "El repartidor {$this->driverName} lleva más de {$this->thresholdMinutes} minutos sin reportar ubicación y tiene {$this->pendingOrders} pedido(s) activo(s).",
        ];
    }
}
