<?php

namespace App\Enums;

enum PackageStatus: string
{
    case Pending = 'pending';
    case PickedUp = 'picked_up';
    case Delivered = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::PickedUp => 'Recogido',
            self::Delivered => 'Entregado',
        };
    }
}
