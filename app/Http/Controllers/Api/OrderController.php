<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Order\OrderExistException;
use App\Exceptions\Order\UserCreateOrderException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreRequest;
use App\Http\Requests\Order\UpdateRequest;
use App\Http\Requests\Order\UpdateStatusRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService){}

    public function list(): AnonymousResourceCollection|JsonResponse
    {
        try {
            $orders = $this->orderService->list();
            return OrderResource::collection($orders);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json([
                'message' => __('message.error.default')
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $orderId): OrderResource|JsonResponse
    {
        try {
            $order = $this->orderService->show($orderId);
            return new OrderResource($order);
        } catch (UserCreateOrderException $e) {
            return response()->json([
                'message' => __('message.error.order.user-create-order')
            ], Response::HTTP_CONFLICT);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json([
                'message' => __('message.error.default')
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreRequest $request): OrderResource|JsonResponse
    {
        try {
            $order = $this->orderService->store($request->validated());
            return new OrderResource($order, Response::HTTP_CREATED);
        } catch (OrderExistException $e) {
            return response()->json([
                'message' => __('message.error.order.exist')
            ], Response::HTTP_CONFLICT);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json([
                'message' => __('message.error.default')
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateRequest $request): JsonResponse
    {
        try {
            $this->orderService->update($request->validated());
            return response()->json([
                'message' => __('message.success.order.updated')
            ],Response::HTTP_OK);
        } catch (UserCreateOrderException $e) {
            return response()->json([
                'message' => __('message.error.order.user-create-order')
            ], Response::HTTP_CONFLICT);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json([
                'message' => __('message.error.default')
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateStatus(UpdateStatusRequest $request): JsonResponse
    {
        try {
            $this->orderService->updateStatus($request->validated());
            return response()->json([
                'message' => __('message.success.order.updated')
            ],Response::HTTP_OK);
        } catch (UserCreateOrderException $e) {
            return response()->json([
                'message' => __('message.error.order.user-create-order')
            ], Response::HTTP_CONFLICT);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json([
                'message' => __('message.error.default')
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
