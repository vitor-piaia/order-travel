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
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService){}

    /**
     * @OA\Get(
     *     path="/api/order/list",
     *     tags={"Orders"},
     *     summary="List orders",
     *     description="List orders with pagination and filters.",
     *     @OA\Parameter(
     *         name="filter[destiny]",
     *         in="query",
     *         description="Filter by detiny",
     *         required=false,
     *         @OA\Schema(type="string", example="São Paulo")
     *     ),
     *     @OA\Parameter(
     *         name="filter[departure_date]",
     *         in="query",
     *         description="Filter by departure date",
     *         required=false,
     *         @OA\Schema(type="string", example="10/10/2025")
     *     ),
     *     @OA\Parameter(
     *         name="filter[return_date]",
     *         in="query",
     *         description="Filter by return date",
     *         required=false,
     *         @OA\Schema(type="string", example="10/10/2025")
     *     ),
     *     @OA\Parameter(
     *         name="filter[status]",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", example="approved")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Identify page access",
     *         required=false,
     *         @OA\Schema(type="integer", example="1")
     *     ),
     *     @OA\Parameter(
     *         name="order_by",
     *         in="query",
     *         description="Order the list",
     *         required=false,
     *         @OA\Schema(type="string", example="asc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List paginated orders",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="orderId", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="João Silva"),
     *                     @OA\Property(property="destiny", type="string", example="São Paulo"),
     *                     @OA\Property(property="departureDate", type="string", example="10/10/2025"),
     *                     @OA\Property(property="returnDate", type="string", example="10/10/2025"),
     *                     @OA\Property(property="status", type="string", example="requested")
     *                 )
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=2),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="last_page", type="integer", example=5)
     *             )
     *         )
     *     )
     * )
     */
    public function list(Request $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $page = $request->get('page', 1);
            $orderBy = $request->get('order_by', 'asc');
            $orders = $this->orderService->list($page, $orderBy);
            return OrderResource::collection($orders);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json([
                'message' => __('message.error.default')
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/order/show",
     *     tags={"Orders"},
     *     summary="Show order",
     *     description="Show order.",
     *     @OA\Response(
     *         response=200,
     *         description="Detail order",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="orderId", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="João Silva"),
     *                     @OA\Property(property="destiny", type="string", example="São Paulo"),
     *                     @OA\Property(property="departureDate", type="string", example="10/10/2025"),
     *                     @OA\Property(property="returnDate", type="string", example="10/10/2025"),
     *                     @OA\Property(property="status", type="string", example="requested")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An error has occurred, please try again later")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/order/store",
     *     tags={"Orders"},
     *     summary="Create order",
     *     description="Create order.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id","name","destiny","departure_date", "return_date"},
     *             @OA\Property(property="order_id", type="integer", example="1"),
     *             @OA\Property(property="name", type="string", example="Joao"),
     *             @OA\Property(property="destiny", type="string", example="São Paulo"),
     *             @OA\Property(property="departure_date", type="string", example="10/10/2025"),
     *             @OA\Property(property="return_date", type="string", example="10/10/2025")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="orderId", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="João Silva"),
     *                     @OA\Property(property="destiny", type="string", example="São Paulo"),
     *                     @OA\Property(property="departureDate", type="string", example="10/10/2025"),
     *                     @OA\Property(property="returnDate", type="string", example="10/10/2025"),
     *                     @OA\Property(property="status", type="string", example="requested")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The order id field must be an integer.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An error has occurred, please try again later")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/order/update",
     *     tags={"Orders"},
     *     summary="Update order",
     *     description="Update order.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id","destiny","departure_date", "return_date"},
     *             @OA\Property(property="order_id", type="integer", example="1"),
     *             @OA\Property(property="destiny", type="string", example="São Paulo"),
     *             @OA\Property(property="departure_date", type="string", example="10/10/2025"),
     *             @OA\Property(property="return_date", type="string", example="10/10/2025")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The order id field must be an integer.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An error has occurred, please try again later")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/order/update-status",
     *     tags={"Orders"},
     *     summary="Update status order",
     *     description="Update status order.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id","status"},
     *             @OA\Property(property="order_id", type="integer", example="1"),
     *             @OA\Property(property="status", type="string", example="approved")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The order id field must be an integer.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An error has occurred, please try again later")
     *         )
     *     )
     * )
     */
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
