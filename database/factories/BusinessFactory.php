<?php

namespace Database\Factories;

use App\Enums\BusinessStatus;
use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

class BusinessFactory extends Factory
{
    protected $model = Business::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'address' => fake()->streetAddress() . ', Bogotá D.C.',
            'phone' => '3' . fake()->numerify('#########'),
            'status' => BusinessStatus::Active,
            'suspended_at' => null,
            'inactivity_threshold_minutes' => 15,
        ];
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BusinessStatus::Suspended,
            'suspended_at' => now(),
        ]);
    }
}
