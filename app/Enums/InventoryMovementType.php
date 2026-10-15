<?php

namespace App\Enums;

enum InventoryMovementType: string
{
    case Reserve = 'reserve';
    case Release = 'release';
    case Restock = 'restock';
    case Return = 'return';
    case Adjustment = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::Reserve => 'Reserva de stock',
            self::Release => 'Liberación de reserva',
            self::Restock => 'Reabastecimiento',
            self::Return => 'Devolución',
            self::Adjustment => 'Ajuste manual',
        };
    }
}
