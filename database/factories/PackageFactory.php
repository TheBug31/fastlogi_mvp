<?php

namespace Database\Factories;

use App\Enums\PackageStatus;
use App\Models\Order;
use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory()->withTraceability(),
            'tracking_code' => 'PKG-' . strtoupper(fake()->bothify('####-????')),
            'status' => PackageStatus::Pending,
        ];
    }

    public function pickedUp(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PackageStatus::PickedUp,
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PackageStatus::Delivered,
        ]);
    }
}
