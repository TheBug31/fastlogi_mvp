<?php

namespace App\Enums;

enum PackageScanType: string
{
    case Pickup = 'pickup';
    case Delivery = 'delivery';

    public function label(): string
    {
        return match ($this) {
            self::Pickup => 'Recogida',
            self::Delivery => 'Entrega',
        };
    }
}
