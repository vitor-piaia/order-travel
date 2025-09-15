<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CancelOrder\CancelOrderExistException;
use App\Exceptions\Order\OrderNotApprovedException;
use App\Exceptions\Order\UserCreateOrderException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CancelOrder\StoreRequest;
use App\Http\Requests\CancelOrder\UpdateStatusRequest;
use App\Http\Resources\CancelOrderResource;
use App\Services\CancelOrderApprovedService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CancelOrderApprovedController extends Controller
{
    public function __construct(private readonly CancelOrderApprovedService $cancelOrderApprovedService) {}

    /**
     * @OA\Get(
     *     path="/api/cancel-order/list",
     *     tags={"Cancel Orders"},
     *     summary="List cancel orders",
     *     description="List cancel orders with pagination and filters.",
     *
     *     @OA\Parameter(
     *         name="filter[status]",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *
     *         @OA\Schema(type="string", example="approved")
     *     ),
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Identify page access",
     *         required=false,
     *
     *         @OA\Schema(type="integer", example="1")
     *     ),
     *
     *     @OA\Parameter(
     *         name="order_by",
     *         in="query",
     *         description="Order the list",
     *         required=false,
     *
     *         @OA\Schema(type="string", example="asc")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List paginated orders",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="array",
     *
     *                 @OA\Items(
     *
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="message", type="string", example="Cancel"),
     *                     @OA\Property(property="status", type="string", example="requested"),
     *                     @OA\Property(property="order", type="array",
     *
     *                         @OA\Items(
     *
     *                             @OA\Property(property="orderId", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="João Silva"),
     *                             @OA\Property(property="destiny", type="string", example="São Paulo"),
     *                             @OA\Property(property="departureDate", type="string", example="10/10/2025"),
     *                             @OA\Property(property="returnDate", type="string", example="10/10/2025"),
     *                             @OA\Property(property="status", type="string", example="requested")
     *                         )
     *                     ),
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
            $cancelOrders = $this->cancelOrderApprovedService->list($page, $orderBy);

            return CancelOrderResource::collection($cancelOrders);
        } catch (Exception $e) {
            Log::error($e);

            return response()->json([
                'message' => __('message.error.default'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/cancel-order/show",
     *     tags={"Cancel Orders"},
     *     summary="show cancel order",
     *     description="Show cancel order.",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Show cancel order",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="array",
     *
     *                 @OA\Items(
     *
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="message", type="string", example="Cancel"),
     *                     @OA\Property(property="status", type="string", example="requested"),
     *                     @OA\Property(property="order", type="array",
     *
     *                         @OA\Items(
     *
     *                             @OA\Property(property="orderId", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="João Silva"),
     *                             @OA\Property(property="destiny", type="string", example="São Paulo"),
     *                             @OA\Property(property="departureDate", type="string", example="10/10/2025"),
     *                             @OA\Property(property="returnDate", type="string", example="10/10/2025"),
     *                             @OA\Property(property="status", type="string", example="requested")
     *                         )
     *                     ),
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Error validation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="An error has occurred, please try again later")
     *         )
     *     )
     * )
     */
    public function show(int $id): CancelOrderResource|JsonResponse
    {
        try {
            $cancelOrder = $this->cancelOrderApprovedService->show($id);

            return new CancelOrderResource($cancelOrder);
        } catch (UserCreateOrderException $e) {
            return response()->json([
                'message' => __('message.error.order.user-create-order'),
            ], Response::HTTP_CONFLICT);
        } catch (Exception $e) {
            Log::error($e);

            return response()->json([
                'message' => __('message.error.default'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/cancel-order/store",
     *     tags={"Cancel Orders"},
     *     summary="Create cancel order",
     *     description="Create cancel order.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"order_id","message"},
     *
     *             @OA\Property(property="order_id", type="integer", example="1"),
     *             @OA\Property(property="message", type="string", example="test"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Cancel order created",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="array",
     *
     *                 @OA\Items(
     *
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="message", type="string", example="Cancel"),
     *                     @OA\Property(property="status", type="string", example="requested"),
     *                     @OA\Property(property="order", type="array",
     *
     *                         @OA\Items(
     *
     *                             @OA\Property(property="orderId", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="João Silva"),
     *                             @OA\Property(property="destiny", type="string", example="São Paulo"),
     *                             @OA\Property(property="departureDate", type="string", example="10/10/2025"),
     *                             @OA\Property(property="returnDate", type="string", example="10/10/2025"),
     *                             @OA\Property(property="status", type="string", example="requested")
     *                         )
     *                     ),
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Error validation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="The order id field must be an integer.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Error validation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="An error has occurred, please try again later")
     *         )
     *     )
     * )
     */
    public function store(StoreRequest $request): CancelOrderResource|JsonResponse
    {
        try {
            $cancelOrder = $this->cancelOrderApprovedService->store($request->validated());

            return new CancelOrderResource($cancelOrder, Response::HTTP_CREATED);
        } catch (OrderNotApprovedException $e) {
            return response()->json([
                'message' => __('message.error.order.not-approved'),
            ], Response::HTTP_CONFLICT);
        } catch (CancelOrderExistException $e) {
            return response()->json([
                'message' => __('message.error.cancel-order.exist'),
            ], Response::HTTP_CONFLICT);
        } catch (Exception $e) {
            Log::error($e);

            return response()->json([
                'message' => __('message.error.default'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/cancel-order/update",
     *     tags={"Cancel Orders"},
     *     summary="Update status cancel order",
     *     description="Update status cancel order.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"id","status"},
     *
     *             @OA\Property(property="id", type="integer", example="1"),
     *             @OA\Property(property="status", type="string", example="approved"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Cancel order updated",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Cancel order updated successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Error validation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="The id field must be an integer.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Error validation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="An error has occurred, please try again later")
     *         )
     *     )
     * )
     */
    public function updateStatus(UpdateStatusRequest $request): JsonResponse
    {
        try {
            $this->cancelOrderApprovedService->updateStatus($request->validated());

            return response()->json([
                'message' => __('message.success.cancel-order.updated'),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e);

            return response()->json([
                'message' => __('message.error.default'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
