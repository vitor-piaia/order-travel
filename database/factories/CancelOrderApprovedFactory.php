<?php

namespace Database\Factories;

use App\Enums\OrderEnum;
use App\Models\CancelOrderApproved;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class CancelOrderApprovedFactory extends Factory
{
    protected $model = CancelOrderApproved::class;

    protected static ?string $status;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'message' => $this->faker->name(),
            'status' => $status ?? OrderEnum::STATUS_REQUESTED,
            'admin_id' => null,
        ];
    }

    public function forOrder($orderId): static
    {
        return $this->state(fn () => ['order_id' => $orderId]);
    }
}
