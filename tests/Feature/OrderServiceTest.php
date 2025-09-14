<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = app()->make(OrderService::class);
    }

    public function test_find_returns_correct_order()
    {
        $order = Order::factory(1)->create();

        $foundOrder = $this->orderService->find($order->order_id);

        $this->assertInstanceOf(Order::class, $foundOrder);
        $this->assertEquals($order->order_id, $foundOrder->order_id);
        $this->assertEquals($order->name, $foundOrder->name);
    }
}
