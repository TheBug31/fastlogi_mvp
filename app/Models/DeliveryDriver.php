<?php

namespace App\Models;

use App\Enums\DriverAvailability;
use App\Enums\OrderStatus;
use App\Models\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryDriver extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'user_id',
        'business_id',
        'availability',
        'max_active_orders',
        'latitude',
        'longitude',
        'last_seen_at',
    ];

    protected $casts = [
        'availability' => DriverAvailability::class,
        'max_active_orders' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'last_seen_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function activeOrders(): HasMany
    {
        return $this->orders()->whereIn('status', [
            OrderStatus::Offered,
            OrderStatus::Assigned,
            OrderStatus::InTransit,
        ]);
    }

    public function activeOrdersCount(): int
    {
        return $this->activeOrders()->count();
    }

    public function hasCapacity(): bool
    {
        if ($this->availability === DriverAvailability::Offline) {
            return false;
        }

        return $this->activeOrdersCount() < $this->max_active_orders;
    }

    public function isAvailable(): bool
    {
        return $this->availability === DriverAvailability::Available;
    }

    public function isBusy(): bool
    {
        return $this->availability === DriverAvailability::Busy;
    }

    public function isOffline(): bool
    {
        return $this->availability === DriverAvailability::Offline;
    }
}
