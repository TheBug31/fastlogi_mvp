<?php

namespace App\Enums;

enum DriverAvailability: string
{
    case Available = 'available';
    case Busy = 'busy';
    case Offline = 'offline';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::Busy => 'Ocupado',
            self::Offline => 'Desconectado',
        };
    }
}
