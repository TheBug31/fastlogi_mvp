<?php

namespace App\Models;

use App\Enums\PackageScanType;
use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageScan extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'slug',
        'package_id',
        'type',
        'user_id',
        'latitude',
        'longitude',
        'scanned_at',
    ];

    protected $casts = [
        'type' => PackageScanType::class,
        'latitude' => 'float',
        'longitude' => 'float',
        'scanned_at' => 'datetime',
    ];

    /**
     * Scans have no name: the slug describes package and scan type.
     */
    protected function slugSource(): ?string
    {
        return null;
    }

    protected function slugSourceValue(): string
    {
        return 'escaneo '.$this->package_id.' '.self::stringifySlugValue($this->type);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
