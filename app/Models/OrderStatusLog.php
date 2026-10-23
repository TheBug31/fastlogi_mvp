<?php

namespace App\Models;

use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusLog extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'slug',
        'order_id',
        'from',
        'to',
        'user_id',
        'reason',
    ];

    /**
     * Status logs have no name: the slug enumerates order and target status.
     */
    protected function slugSource(): ?string
    {
        return null;
    }

    protected function slugSourceValue(): string
    {
        return 'registro '.$this->order_id.' '.self::stringifySlugValue($this->to);
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
