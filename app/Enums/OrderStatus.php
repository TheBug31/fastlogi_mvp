<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Offered = 'offered';
    case Assigned = 'assigned';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Incident = 'incident';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Offered => 'Ofrecido',
            self::Assigned => 'Asignado',
            self::InTransit => 'En Tránsito',
            self::Delivered => 'Entregado',
            self::Cancelled => 'Cancelado',
            self::Incident => 'Incidencia',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Delivered, self::Cancelled, self::Incident]);
    }

    public function isActiveForDriver(): bool
    {
        return in_array($this, [self::Offered, self::Assigned, self::InTransit]);
    }
}
