<?php

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToBusiness, HasFactory, HasSlug;

    protected $fillable = [
        'business_id',
        'name',
        'slug',
        'barcode',
        'price',
        'stock',
        'min_stock',
        'unit',
        'low_stock_alerted',
        'out_of_stock_alerted',
    ];

    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'low_stock_alerted' => 'boolean',
        'out_of_stock_alerted' => 'boolean',
    ];

    protected $appends = [
        'is_out_of_stock',
        'is_low_stock',
        'status_badge',
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    public function isLowStock(): bool
    {
        return $this->stock > 0 && $this->min_stock > 0 && $this->stock < $this->min_stock;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->isOutOfStock();
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->isLowStock();
    }

    protected function statusBadge(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->stock <= 0) {
                    return ['color' => 'red', 'label' => 'Agotado'];
                }

                if ($this->min_stock > 0 && $this->stock < $this->min_stock) {
                    return ['color' => 'amber', 'label' => 'Stock bajo'];
                }

                return ['color' => 'green', 'label' => 'Normal'];
            },
        );
    }
}
