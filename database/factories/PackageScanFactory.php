<?php

namespace Database\Factories;

use App\Enums\PackageScanType;
use App\Models\Package;
use App\Models\PackageScan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageScanFactory extends Factory
{
    protected $model = PackageScan::class;

    public function definition(): array
    {
        return [
            'package_id' => Package::factory(),
            'type' => PackageScanType::Pickup,
            'user_id' => User::factory()->driver(),
            'latitude' => 4.6097100,
            'longitude' => -74.0817500,
            'scanned_at' => now(),
        ];
    }

    public function pickup(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PackageScanType::Pickup,
        ]);
    }

    public function delivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PackageScanType::Delivery,
        ]);
    }
}
