<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use BelongsToBusiness, HasFactory, HasSlug;

    protected $fillable = [
        'business_id',
        'slug',
        'customer_name',
        'customer_phone',
        'delivery_address',
        'payment_method',
        'total',
        'status',
        'requires_traceability',
        'delivery_driver_id',
        'offered_at',
        'offer_expires_at',
        'cancel_reason',
    ];

    protected $casts = [
        'total' => 'float',
        'status' => OrderStatus::class,
        'requires_traceability' => 'boolean',
        'offered_at' => 'datetime',
        'offer_expires_at' => 'datetime',
    ];

    public function deliveryDriver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class);
    }

    /**
     * Orders have no `name`: the slug is derived from the customer name.
     */
    protected function slugSource(): ?string
    {
        return 'customer_name';
    }

    /**
     * Resolve an order by numeric id or by slug, so the public URLs can be
     * human-readable while legacy id-based links keep working.
     */
    public function scopeWhereKeyOrSlug(Builder $query, string|int|null $key): Builder
    {
        $key = (string) $key;

        if ($key === '') {
            return $query->whereRaw('1 = 0');
        }

        return ctype_digit($key)
            ? $query->whereKey($key)
            : $query->where('slug', $key);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function package(): HasOne
    {
        return $this->hasOne(Package::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    public function isOfferExpired(): bool
    {
        if ($this->status !== OrderStatus::Offered || ! $this->offer_expires_at) {
            return false;
        }

        return now()->greaterThan($this->offer_expires_at);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            OrderStatus::Pending,
            OrderStatus::Offered,
            OrderStatus::Assigned,
        ]);
    }

    public function canRegisterIncident(): bool
    {
        return in_array($this->status, [
            OrderStatus::InTransit,
            OrderStatus::Delivered,
        ]);
    }
}
