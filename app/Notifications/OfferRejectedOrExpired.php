<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Notifies the owner that an offer was rejected by the driver or expired after
 * the 90 second window, so the order is back to `pending` and must be reassigned
 * (RF-002).
 */
class OfferRejectedOrExpired extends Notification
{
    public function __construct(
        public int $orderId,
        public string $customerName,
        public string $reason,
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
            'type' => 'offer_rejected_or_expired',
            'order_id' => $this->orderId,
            'customer_name' => $this->customerName,
            'reason' => $this->reason,
            'message' => "El pedido de {$this->customerName} volvió a pendiente ({$this->reason}). Asígnalo a otro repartidor.",
        ];
    }
}
