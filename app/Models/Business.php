<?php

namespace App\Models;

use App\Enums\BusinessStatus;
use App\Enums\OrderStatus;
use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'address',
        'phone',
        'status',
        'suspended_at',
        'inactivity_threshold_minutes',
    ];

    protected $casts = [
        'status' => BusinessStatus::class,
        'suspended_at' => 'datetime',
        'inactivity_threshold_minutes' => 'integer',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function deliveryDrivers(): HasMany
    {
        return $this->hasMany(DeliveryDriver::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isSuspended(): bool
    {
        return $this->status === BusinessStatus::Suspended;
    }

    public function isActive(): bool
    {
        return $this->status === BusinessStatus::Active;
    }

    public function graceOrders(): HasMany
    {
        return $this->orders()->whereIn('status', [OrderStatus::Assigned, OrderStatus::InTransit]);
    }

    public function graceOrdersCount(): int
    {
        return $this->graceOrders()->count();
    }
}
