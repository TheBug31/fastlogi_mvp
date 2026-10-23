<?php

namespace App\Models;

use App\Enums\InventoryMovementType;
use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'slug',
        'product_id',
        'order_id',
        'type',
        'quantity',
        'user_id',
    ];

    protected $casts = [
        'type' => InventoryMovementType::class,
        'quantity' => 'integer',
    ];

    /**
     * Movements have no name: the slug describes product and movement type.
     */
    protected function slugSource(): ?string
    {
        return null;
    }

    protected function slugSourceValue(): string
    {
        return 'movimiento '.$this->product_id.' '.self::stringifySlugValue($this->type);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
