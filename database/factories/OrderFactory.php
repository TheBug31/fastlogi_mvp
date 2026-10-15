<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Business;
use App\Models\DeliveryDriver;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'customer_name' => fake()->name(),
            'customer_phone' => '3' . fake()->numerify('#########'),
            'delivery_address' => fake()->streetAddress() . ', Bogotá D.C.',
            'payment_method' => fake()->randomElement(['Contra Entrega (Efectivo)', 'Nequi / Daviplata', 'Transferencia Bancaria']),
            'total' => fake()->numberBetween(30, 200) * 1000,
            'status' => OrderStatus::Pending,
            'requires_traceability' => false,
            'delivery_driver_id' => null,
            'offered_at' => null,
            'offer_expires_at' => null,
            'cancel_reason' => null,
        ];
    }

    public function withTraceability(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_traceability' => true,
        ]);
    }

    public function offered(?DeliveryDriver $driver = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Offered,
            'delivery_driver_id' => $driver?->id,
            'offered_at' => now(),
            'offer_expires_at' => now()->addSeconds(90),
        ]);
    }

    public function assigned(?DeliveryDriver $driver = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Assigned,
            'requires_traceability' => true,
            'delivery_driver_id' => $driver?->id,
            'offered_at' => now()->subMinutes(5),
            'offer_expires_at' => now()->subMinutes(3),
        ]);
    }

    public function inTransit(?DeliveryDriver $driver = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::InTransit,
            'delivery_driver_id' => $driver?->id,
            'offered_at' => now()->subMinutes(15),
            'offer_expires_at' => now()->subMinutes(13),
        ]);
    }

    public function delivered(?DeliveryDriver $driver = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Delivered,
            'delivery_driver_id' => $driver?->id,
            'offered_at' => now()->subHours(2),
            'offer_expires_at' => now()->subHours(2)->addSeconds(90),
        ]);
    }

    public function cancelled(?string $reason = 'Cancelado por solicitud del cliente'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Cancelled,
            'cancel_reason' => $reason,
        ]);
    }

    public function incident(?string $reason = 'Paquete averiado o cliente ausente'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Incident,
            'cancel_reason' => $reason,
        ]);
    }
}
