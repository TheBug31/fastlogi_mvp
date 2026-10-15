<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => fake()->words(2, true),
            'barcode' => fake()->ean13(),
            'price' => fake()->numberBetween(50, 300) * 1000, // Precios comunes en COP (50.000 - 300.000)
            'stock' => 25,
            'min_stock' => 5,
            'unit' => 'unidad',
            'low_stock_alerted' => false,
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 3,
            'min_stock' => 10,
            'low_stock_alerted' => true,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
            'min_stock' => 10,
            'low_stock_alerted' => true,
        ]);
    }
}
