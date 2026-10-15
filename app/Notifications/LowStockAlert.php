<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Persistent low-stock / out-of-stock alert (RF-008 §4.8.1).
 *
 * It is queued so the email is dispatched by a worker, outside of the stock
 * transaction, while the database notification keeps the alert visible in the
 * owner's notification center until it is marked as read.
 */
class LowStockAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public const LOW_STOCK = 'low_stock';

    public const OUT_OF_STOCK = 'out_of_stock';

    public function __construct(
        public Product $product,
        public string $kind = self::LOW_STOCK,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->kind,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'stock' => $this->product->stock,
            'min_stock' => $this->product->min_stock,
            'message' => $this->message(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->kind === self::OUT_OF_STOCK
            ? 'Producto agotado'
            : 'Alerta de stock bajo';

        return (new MailMessage)
            ->subject("[FastLogi] {$subject}: {$this->product->name}")
            ->greeting('Hola '.$notifiable->name)
            ->line($this->message())
            ->line('Ingresa a tu panel de inventario en FastLogi para reabastecer a tiempo.');
    }

    private function message(): string
    {
        return $this->kind === self::OUT_OF_STOCK
            ? "El producto «{$this->product->name}» se quedó sin existencias."
            : "El producto «{$this->product->name}» bajó del stock mínimo ({$this->product->stock} de {$this->product->min_stock}).";
    }
}
