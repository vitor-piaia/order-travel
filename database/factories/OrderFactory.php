<?php

namespace Database\Factories;

use App\Enums\OrderEnum;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    protected static ?string $status;

    public function definition(): array
    {
        return [
            'order_id' => $this->faker->numberBetween(1, 1000),
            'name' => $this->faker->name(),
            'destiny' => $this->faker->city(),
            'departure_date' => Carbon::parse($this->faker->date())->format('d/m/Y'),
            'return_date' => Carbon::parse($this->faker->date())->format('d/m/Y'),
            'status' => $status ?? OrderEnum::STATUS_REQUESTED,
            'user_id' => User::factory(),
            'admin_id' => null,
        ];
    }
}
