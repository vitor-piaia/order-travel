<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Order\CancelOrderExistException;
use App\Exceptions\Order\OrderExistException;
use App\Exceptions\Order\OrderNotApprovedException;
use App\Exceptions\Order\UserCreateOrderException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CancelOrder\StoreRequest;
use App\Http\Requests\CancelOrder\UpdateStatusRequest;
use App\Http\Resources\CancelOrderResource;
use App\Services\CancelOrderApprovedService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CancelOrderApprovedController extends Controller
{
    public function __construct(private readonly CancelOrderApprovedService $cancelOrderApprovedService){}

    public function list(): AnonymousResourceCollection|JsonResponse
    {
        try {
            $cancelOrders = $this->cancelOrderApprovedService->list();
            return CancelOrderResource::collection($cancelOrders);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json([
                'message' => __('message.error.default')
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id): CancelOrderResource|JsonResponse
    {
        try {
            $cancelOrder = $this->cancelOrderApprovedService->show($id);
            return new CancelOrderResource($cancelOrder);
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

    public function store(StoreRequest $request): CancelOrderResource|JsonResponse
    {
        try {
            $cancelOrder = $this->cancelOrderApprovedService->store($request->validated());
            return new CancelOrderResource($cancelOrder, Response::HTTP_CREATED);
        } catch (OrderNotApprovedException $e) {
            return response()->json([
                'message' => __('message.error.order.not-approved')
            ], Response::HTTP_CONFLICT);
        } catch (CancelOrderExistException $e) {
            return response()->json([
                'message' => __('message.error.cancel-order.exist')
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
            $this->cancelOrderApprovedService->updateStatus($request->validated());
            return response()->json([
                'message' => __('message.success.cancel-order.updated')
            ],Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json([
                'message' => __('message.error.default')
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
