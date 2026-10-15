<?php

namespace Database\Factories;

use App\Enums\DriverAvailability;
use App\Models\Business;
use App\Models\DeliveryDriver;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryDriverFactory extends Factory
{
    protected $model = DeliveryDriver::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->driver(),
            'business_id' => fn (array $attributes) => User::find($attributes['user_id'])->business_id ?? Business::factory(),
            'availability' => DriverAvailability::Available,
            'max_active_orders' => 3,
            'latitude' => 4.6097100,
            'longitude' => -74.0817500,
            'last_seen_at' => now(),
        ];
    }

    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'availability' => DriverAvailability::Available,
        ]);
    }

    public function busy(): static
    {
        return $this->state(fn (array $attributes) => [
            'availability' => DriverAvailability::Busy,
        ]);
    }

    public function offline(): static
    {
        return $this->state(fn (array $attributes) => [
            'availability' => DriverAvailability::Offline,
        ]);
    }
}
