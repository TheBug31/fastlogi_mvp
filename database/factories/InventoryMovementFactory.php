<?php

namespace Database\Factories;

use App\Enums\InventoryMovementType;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryMovementFactory extends Factory
{
    protected $model = InventoryMovement::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'order_id' => null,
            'type' => InventoryMovementType::Reserve,
            'quantity' => 1,
            'user_id' => User::factory()->owner(),
        ];
    }

    public function reserve(?Order $order = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => InventoryMovementType::Reserve,
            'order_id' => $order?->id,
            'quantity' => -1,
        ]);
    }

    public function release(?Order $order = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => InventoryMovementType::Release,
            'order_id' => $order?->id,
            'quantity' => 1,
        ]);
    }

    public function restock(int $qty = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => InventoryMovementType::Restock,
            'quantity' => $qty,
        ]);
    }

    public function return(?Order $order = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => InventoryMovementType::Return,
            'order_id' => $order?->id,
            'quantity' => 1,
        ]);
    }
}
