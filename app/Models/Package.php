<?php

namespace App\Models;

use App\Enums\PackageStatus;
use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'order_id',
        'tracking_code',
        'slug',
        'status',
    ];

    protected $casts = [
        'status' => PackageStatus::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Packages are identified by their tracking code, so the slug mirrors it.
     */
    protected function slugSource(): ?string
    {
        return 'tracking_code';
    }

    public function scans(): HasMany
    {
        return $this->hasMany(PackageScan::class)->orderBy('scanned_at', 'asc');
    }
}
