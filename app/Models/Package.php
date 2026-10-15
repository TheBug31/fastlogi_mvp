<?php

namespace App\Models;

use App\Enums\PackageStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'tracking_code',
        'status',
    ];

    protected $casts = [
        'status' => PackageStatus::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scans(): HasMany
    {
        return $this->hasMany(PackageScan::class)->orderBy('scanned_at', 'asc');
    }
}
