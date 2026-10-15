<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderStatusLogFactory extends Factory
{
    protected $model = OrderStatusLog::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'from' => OrderStatus::Pending->value,
            'to' => OrderStatus::Offered->value,
            'user_id' => User::factory()->owner(),
            'reason' => 'Oferta asignada a repartidor',
        ];
    }
}
