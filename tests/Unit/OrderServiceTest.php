<?php

namespace Tests\Unit;

use App\Enums\OrderEnum;
use App\Exceptions\Order\OrderExistException;
use App\Models\Order;
use App\Models\User;
use App\Repositories\OrderRepository;
use App\Services\OrderService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
use Exception;
use Mockery;

class OrderServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }
    protected function tearDown(): void
    {
        Mockery::close();
        Facade::clearResolvedInstances();
        parent::tearDown();
    }

    public function test_it_show_order_admin()
    {
        $userMock = Mockery::mock(User::class);
        $userMock->shouldReceive('hasRole')
            ->once()
            ->with('admin')
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($userMock);

        $expectedData = [
            'order_id' => fake()->numberBetween(1, 1000),
            'name' => fake()->name(),
            'destiny' => fake()->city(),
            'departure_date' => now()->format('d/m/Y'),
            'return_date' => now()->addDays(3)->format('d/m/Y'),
            'status' => OrderEnum::STATUS_REQUESTED,
        ];
        $userId = null;

        $mockOrder = new Order($expectedData);
        $orderRepositoryMock = Mockery::mock(OrderRepository::class);
        $orderRepositoryMock
            ->shouldReceive('findOrder')
            ->once()
            ->with($expectedData['order_id'], $userId)
            ->andReturn($mockOrder);

        $orderService = new OrderService($orderRepositoryMock);
        $order = $orderService->show($expectedData['order_id']);

        $this->assertEquals($expectedData, $order->toArray());
    }

    public function test_it_show_order_user()
    {
        $userId = fake()->numberBetween(1, 1000);
        $userMock = Mockery::mock(User::class);
        $userMock->shouldReceive('hasRole')
            ->once()
            ->with('admin')
            ->andReturn(false);

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($userMock);

        Auth::shouldReceive('id')
            ->once()
            ->andReturn($userId);

        $expectedData = [
            'order_id' => fake()->numberBetween(1, 1000),
            'name' => fake()->name(),
            'destiny' => fake()->city(),
            'departure_date' => now()->format('d/m/Y'),
            'return_date' => now()->addDays(3)->format('d/m/Y'),
            'status' => OrderEnum::STATUS_REQUESTED,
        ];

        $mockOrder = new Order($expectedData);
        $orderRepositoryMock = Mockery::mock(OrderRepository::class);
        $orderRepositoryMock
            ->shouldReceive('findOrder')
            ->once()
            ->with($expectedData['order_id'], $userId)
            ->andReturn($mockOrder);

        $orderService = new OrderService($orderRepositoryMock);
        $order = $orderService->show($expectedData['order_id']);

        $this->assertEquals($expectedData, $order->toArray());
    }

    public function test_it_show_order_exception()
    {
        $this->expectException(Exception::class);
        $userMock = Mockery::mock(User::class);
        $userMock->shouldReceive('hasRole')
            ->once()
            ->with('admin')
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($userMock);

        $orderId = fake()->numberBetween(1, 1000);
        $userId = null;

        $orderRepositoryMock = Mockery::mock(OrderRepository::class);
        $orderRepositoryMock
            ->shouldReceive('findOrder')
            ->once()
            ->with($orderId, $userId)
            ->andThrow(new Exception());

        $orderService = new OrderService($orderRepositoryMock);
        $orderService->show($orderId);
    }

    public function test_it_returns_paginated_orders()
    {
        $userId = fake()->numberBetween(1, 1000);
        $userMock = Mockery::mock(User::class);

        $userMock->shouldReceive('hasRole')
            ->with('admin')
            ->andReturn(false);

        Auth::shouldReceive('user')
            ->andReturn($userMock);

        Auth::shouldReceive('id')
            ->once()
            ->andReturn($userId);

        $fakePaginator = new LengthAwarePaginator(
            collect([['id' => 1], ['id' => 2]]),
            2,
            10,
            1
        );

        $orderRepositoryMock = Mockery::mock(OrderRepository::class);
        $orderRepositoryMock
            ->shouldReceive('listPaginate')
            ->once()
            ->andReturn($fakePaginator);

        $orderService = new OrderService($orderRepositoryMock);
        $result = $orderService->list();

        $this->assertCount(2, $result->items());
        $this->assertEquals(1, $result->currentPage());
    }

    public function test_it_stores_order_successfully()
    {
        $inputData = [
            'order_id' => fake()->numberBetween(1, 1000),
            'name' => fake()->name(),
            'destiny' => fake()->city(),
            'departure_date' => now()->format('d/m/Y'),
            'return_date' => now()->addDays(3)->format('d/m/Y'),
        ];

        $userId = fake()->numberBetween(1, 1000);
        $expectedData = array_merge($inputData, [
            'status' => OrderEnum::STATUS_REQUESTED,
            'user_id' => $userId
        ]);

        $orderRepositoryMock = Mockery::mock(OrderRepository::class);
        $orderRepositoryMock
            ->shouldReceive('checkOrderIdExists')
            ->once()
            ->with($inputData['order_id'])
            ->andReturn(false);

        Auth::shouldReceive('id')
            ->once()
            ->andReturn($userId);

        $mockOrder = new Order($expectedData);
        $orderRepositoryMock->shouldReceive('create')
            ->once()
            ->with($expectedData)
            ->andReturn($mockOrder);

        $orderService = new OrderService($orderRepositoryMock);
        $order = $orderService->store($inputData);

        unset($expectedData['user_id']);
        $this->assertEquals($expectedData, $order->toArray());
    }

    public function test_it_stores_order_exception_order_exist()
    {
        $this->expectException(OrderExistException::class);
        $inputData = [
            'order_id' => fake()->numberBetween(1, 1000),
            'name' => fake()->name(),
            'destiny' => fake()->city(),
            'departure_date' => now()->format('d/m/Y'),
            'return_date' => now()->addDays(3)->format('d/m/Y'),
        ];

        $orderRepositoryMock = Mockery::mock(OrderRepository::class);
        $orderRepositoryMock
            ->shouldReceive('checkOrderIdExists')
            ->once()
            ->with($inputData['order_id'])
            ->andReturn(true);

        $orderService = new OrderService($orderRepositoryMock);
        $orderService->store($inputData);
    }

    public function test_it_stores_order_exception()
    {
        $this->expectException(Exception::class);
        $inputData = [
            'order_id' => fake()->numberBetween(1, 1000),
            'name' => fake()->name(),
            'destiny' => fake()->city(),
            'departure_date' => now()->format('d/m/Y'),
            'return_date' => now()->addDays(3)->format('d/m/Y'),
        ];

        $orderRepositoryMock = Mockery::mock(OrderRepository::class);
        $orderRepositoryMock
            ->shouldReceive('checkOrderIdExists')
            ->once()
            ->with($inputData['order_id'])
            ->andReturn(false);

        $userId = fake()->numberBetween(1, 1000);
        Auth::shouldReceive('id')
            ->once()
            ->andReturn($userId);

        $orderRepositoryMock->shouldReceive('create')
            ->once()
            ->andThrow(new Exception());

        $orderService = new OrderService($orderRepositoryMock);
        $orderService->store($inputData);
    }

    public function test_it_update_order_successfully()
    {
        $orderId = fake()->numberBetween(1, 1000);
        $inputData = [
            'destiny' => fake()->city(),
            'departure_date' => now()->format('d/m/Y'),
            'return_date' => now()->addDays(3)->format('d/m/Y'),
        ];

        $orderRepositoryMock = Mockery::mock(OrderRepository::class);

        $orderRepositoryMock->shouldReceive('update')
            ->once()
            ->with($inputData, $orderId)
            ->andReturn(true);

        $inputData['order_id'] = $orderId;
        $orderService = new OrderService($orderRepositoryMock);
        $order = $orderService->update($inputData);

        $this->assertTrue($order);
    }

    public function test_it_update_order_exception()
    {
        $this->expectException(Exception::class);
        $orderId = fake()->numberBetween(1, 1000);
        $inputData = [
            'destiny' => fake()->city(),
            'departure_date' => now()->format('d/m/Y'),
            'return_date' => now()->addDays(3)->format('d/m/Y'),
        ];

        $orderRepositoryMock = Mockery::mock(OrderRepository::class);

        $inputData['order_id'] = $orderId;
        $orderRepositoryMock->shouldReceive('update')
            ->once()
            ->andThrow(new Exception());

        $orderService = new OrderService($orderRepositoryMock);
        $orderService->update($inputData);
    }

//    public function test_it_update_status_order_successfully()
//    {
//        $inputData = [
//            'order_id' => fake()->numberBetween(1, 1000),
//            'status' => OrderEnum::STATUS_APPROVED,
//        ];
//
//        $orderRepositoryMock = Mockery::mock(OrderRepository::class);
//
//        $orderRepositoryMock->shouldReceive('updateMultiple')
//            ->once()
//            ->with(['status' => $inputData['status']], ['order_id' => $inputData['order_id']])
//            ->andReturn(true);
//
//        Config::set('mail.active', false);
//
//        $orderService = new OrderService($orderRepositoryMock);
//        $update = $orderService->updateStatus($inputData);
//
//        $this->assertTrue($update);
//    }

    public function test_it_update_status_order_exception()
    {
        $this->expectException(Exception::class);
        $inputData = [
            'order_id' => fake()->numberBetween(1, 1000),
            'status' => OrderEnum::STATUS_APPROVED,
        ];

        $orderRepositoryMock = Mockery::mock(OrderRepository::class);

        $orderRepositoryMock->shouldReceive('updateMultiple')
            ->once()
            ->andThrow(new Exception());

        $orderService = new OrderService($orderRepositoryMock);
        $orderService->updateStatus($inputData);
    }
}
