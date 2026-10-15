<?php

namespace App\Models;

use App\Enums\PackageScanType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageScan extends Model
{
    use HasFactory;

    protected $fillable = [
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

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
