<?php

namespace App\Enums;

enum UserRole: string
{
    case Superadmin = 'superadmin';
    case Owner = 'owner';
    case Driver = 'driver';

    public function label(): string
    {
        return match ($this) {
            self::Superadmin => 'Super Administrador',
            self::Owner => 'Dueño de Negocio',
            self::Driver => 'Repartidor',
        };
    }
}
