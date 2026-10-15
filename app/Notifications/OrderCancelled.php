<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class OrderCancelled extends Notification
{
    public function __construct(
        public int $orderId,
        public string $customerName,
        public string $reason,
        public string $outcome = 'cancelled',
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
        $verb = $this->outcome === 'incident'
            ? 'pasó a incidencia'
            : 'fue cancelado';

        return [
            'type' => 'order_'.$this->outcome,
            'order_id' => $this->orderId,
            'reason' => $this->reason,
            'message' => "El pedido #{$this->orderId} de {$this->customerName} {$verb}. Motivo: {$this->reason}.",
        ];
    }
}
