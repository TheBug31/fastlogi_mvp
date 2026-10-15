<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * In-app offer sent to a driver when the owner offers them a pending order
 * (RF-002 §4.2.1). The driver's panel polls for it every 10 seconds.
 */
class OrderOffered extends Notification
{
    public function __construct(
        public int $orderId,
        public string $customerName,
        public string $deliveryAddress,
        public int $expiresInSeconds,
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
            'type' => 'order_offered',
            'order_id' => $this->orderId,
            'customer_name' => $this->customerName,
            'delivery_address' => $this->deliveryAddress,
            'expires_in_seconds' => $this->expiresInSeconds,
            'message' => "Nueva oferta de entrega para {$this->customerName}. Tienes {$this->expiresInSeconds} segundos para responder.",
        ];
    }
}
