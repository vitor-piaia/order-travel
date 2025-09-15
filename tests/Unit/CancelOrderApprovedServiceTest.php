<?php

namespace Tests\Unit;

use App\Enums\OrderEnum;
use App\Models\CancelOrdersApproved;
use App\Models\User;
use App\Repositories\CancelOrderApprovedRepository;
use App\Repositories\OrderRepository;
use App\Services\CancelOrderApprovedService;
use App\Services\OrderService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
use Exception;
use Mockery;

class CancelOrderApprovedServiceTest extends TestCase
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

//    public function test_it_show_cancel_order_admin()
//    {
//        $userMock = Mockery::mock(User::class);
//        $userMock->shouldReceive('hasRole')
//            ->once()
//            ->with('admin')
//            ->andReturn(true);
//
//        Auth::shouldReceive('user')
//            ->once()
//            ->andReturn($userMock);
//
//        $expectedData = [
//            'id' => fake()->numberBetween(1, 1000),
//            'message' => fake()->text(),
//            'status' => OrderEnum::STATUS_REQUESTED,
//        ];
//
//        $mockOrder = new CancelOrdersApproved($expectedData);
//        $cancelOrderApprovedRepositoryMock = Mockery::mock(CancelOrderApprovedRepository::class);
//        $cancelOrderApprovedRepositoryMock
//            ->shouldReceive('findCancelOrder')
//            ->once()
//            ->with($expectedData['id'], null)
//            ->andReturn($mockOrder);
//
//        $orderRepositoryMock = Mockery::mock(OrderRepository::class);
//        $orderService = new OrderService($orderRepositoryMock);
//
//        $cancelOrderService = new CancelOrderApprovedService($cancelOrderApprovedRepositoryMock, $orderService);
//        $cancelOrder = $cancelOrderService->show($expectedData['id']);
//
//        $this->assertEquals($expectedData, $cancelOrder->toArray());
//    }
//
//    public function test_it_show_cancel_order_user()
//    {
//        $userId = fake()->numberBetween(1, 1000);
//        $userMock = Mockery::mock(User::class);
//        $userMock->shouldReceive('hasRole')
//            ->once()
//            ->with('admin')
//            ->andReturn(false);
//
//        Auth::shouldReceive('user')
//            ->once()
//            ->andReturn($userMock);
//
//        Auth::shouldReceive('id')
//            ->once()
//            ->andReturn($userId);
//
//        $expectedData = [
//            'id' => fake()->numberBetween(1, 1000),
//            'message' => fake()->text(),
//            'status' => OrderEnum::STATUS_REQUESTED,
//        ];
//
//        $mockOrder = new CancelOrdersApproved($expectedData);
//        $cancelOrderApprovedRepositoryMock = Mockery::mock(CancelOrderApprovedRepository::class);
//        $cancelOrderApprovedRepositoryMock
//            ->shouldReceive('findCancelOrder')
//            ->once()
//            ->with($expectedData['id'], $userId)
//            ->andReturn($mockOrder);
//
//        $orderRepositoryMock = Mockery::mock(OrderRepository::class);
//        $orderService = new OrderService($orderRepositoryMock);
//
//        $cancelOrderService = new CancelOrderApprovedService($cancelOrderApprovedRepositoryMock, $orderService);
//        $cancelOrder = $cancelOrderService->show($expectedData['id']);
//
//        $this->assertEquals($expectedData, $cancelOrder->toArray());
//    }
//
//    public function test_it_show_cancel_order_exception()
//    {
//        $this->expectException(Exception::class);
//        $userMock = Mockery::mock(User::class);
//        $userMock->shouldReceive('hasRole')
//            ->once()
//            ->with('admin')
//            ->andReturn(true);
//
//        Auth::shouldReceive('user')
//            ->once()
//            ->andReturn($userMock);
//
//        $cancelOrderId = fake()->numberBetween(1, 1000);
//
//        $cancelOrderApprovedRepositoryMock = Mockery::mock(CancelOrderApprovedRepository::class);
//        $cancelOrderApprovedRepositoryMock
//            ->shouldReceive('findCancelOrder')
//            ->once()
//            ->with($cancelOrderId, null)
//            ->andThrow(new Exception());
//
//        $orderRepositoryMock = Mockery::mock(OrderRepository::class);
//        $orderService = new OrderService($orderRepositoryMock);
//
//        $cancelOrderService = new CancelOrderApprovedService($cancelOrderApprovedRepositoryMock, $orderService);
//        $cancelOrderService->show($cancelOrderId);
//    }
//
//    public function test_it_returns_paginated_cancel_orders()
//    {
//        $userId = fake()->numberBetween(1, 1000);
//        $userMock = Mockery::mock(User::class);
//
//        $userMock->shouldReceive('hasRole')
//            ->with('admin')
//            ->andReturn(false);
//
//        Auth::shouldReceive('user')
//            ->andReturn($userMock);
//
//        Auth::shouldReceive('id')
//            ->once()
//            ->andReturn($userId);
//
//        $fakePaginator = new LengthAwarePaginator(
//            collect([['id' => 1], ['id' => 2]]),
//            2,
//            10,
//            1
//        );
//        $cancelOrderApprovedRepositoryMock = Mockery::mock(CancelOrderApprovedRepository::class);
//        $cancelOrderApprovedRepositoryMock
//            ->shouldReceive('listPaginate')
//            ->once()
//            ->andReturn($fakePaginator);
//
//
//        $orderRepositoryMock = Mockery::mock(OrderRepository::class);
//        $orderService = new OrderService($orderRepositoryMock);
//
//        $cancelOrderService = new CancelOrderApprovedService($cancelOrderApprovedRepositoryMock, $orderService);
//        $result = $cancelOrderService->list();
//
//        $this->assertCount(2, $result->items());
//        $this->assertEquals(1, $result->currentPage());
//    }

//    public function test_it_stores_cancel_order_successfully()
//    {
//        $userMock = Mockery::mock(User::class);
//        $userMock->shouldReceive('hasRole')
//            ->once()
//            ->with('admin')
//            ->andReturn(false);
//
//        Auth::shouldReceive('user')
//            ->once()
//            ->andReturn($userMock);
//
//        $inputData = [
//            'order_id' => fake()->numberBetween(1, 1000),
//            'message' => fake()->text(),
//        ];
//
//        $expectedData = array_merge($inputData, [
//            'id' => fake()->numberBetween(1, 1000),
//            'status' => OrderEnum::STATUS_REQUESTED,
//            'created_at' => fake()->dateTime,
//            'updated_at' => fake()->dateTime,
//        ]);
//
//        $orderRepositoryMock = Mockery::mock(OrderRepository::class);
//        $orderRepositoryMock
//            ->shouldReceive('checkOrderIsApproved')
//            ->once()
//            ->with($inputData['order_id'])
//            ->andReturn(true);
//
//        $orderService = new OrderService($orderRepositoryMock);
//
//        $cancelOrderApprovedRepositoryMock = Mockery::mock(CancelOrderApprovedRepository::class);
//        $cancelOrderApprovedRepositoryMock
//            ->shouldReceive('checkOrderIdExists')
//            ->once()
//            ->with($inputData['order_id'])
//            ->andReturn(false);
//
//        $mockCancelOrder = new CancelOrdersApproved($expectedData);
//        $cancelOrderApprovedRepositoryMock->shouldReceive('create')
//            ->once()
//            ->with($expectedData)
//            ->andReturn($mockCancelOrder);
//
//        $cancelOrderApprovedService = new CancelOrderApprovedService($cancelOrderApprovedRepositoryMock, $orderService);
//        $cancelOrder = $cancelOrderApprovedService->store($inputData);
//
//        $this->assertEquals($expectedData, $cancelOrder->toArray());
//    }

    public function test_it_update_status_cancel_order_successfully()
    {
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->never();

        $id = fake()->numberBetween(1, 1000);
        $status = OrderEnum::STATUS_CANCELED;

        $cancelOrderApprovedRepositoryMock = Mockery::mock(CancelOrderApprovedRepository::class);
        $cancelOrderApprovedRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with(['status' => $status], $id)
            ->andReturn(true);

        $orderRepositoryMock = Mockery::mock(OrderRepository::class);
        $orderService = new OrderService($orderRepositoryMock);

        $cancelOrderApprovedService = new CancelOrderApprovedService($cancelOrderApprovedRepositoryMock, $orderService);
        $cancelOrder = $cancelOrderApprovedService->updateStatus([
            'id' => $id,
            'status' => $status
        ]);

        $this->assertTrue($cancelOrder);
    }

    public function test_it_update_status_cancel_order_exception()
    {
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->never();
        DB::shouldReceive('rollBack')->once();
        $this->expectException(Exception::class);

        $id = fake()->numberBetween(1, 1000);
        $status = OrderEnum::STATUS_CANCELED;

        $cancelOrderApprovedRepositoryMock = Mockery::mock(CancelOrderApprovedRepository::class);
        $cancelOrderApprovedRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->andReturn(false);

        $orderRepositoryMock = Mockery::mock(OrderRepository::class);
        $orderService = new OrderService($orderRepositoryMock);

        $cancelOrderApprovedService = new CancelOrderApprovedService($cancelOrderApprovedRepositoryMock, $orderService);
        $cancelOrderApprovedService->updateStatus([
            'id' => $id,
            'status' => $status
        ]);
    }
}
