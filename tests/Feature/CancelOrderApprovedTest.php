<?php

namespace Tests\Feature;

use App\Enums\OrderEnum;
use App\Models\CancelOrderApproved;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class CancelOrderApprovedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    private function authHeaders(User $user): array
    {
        $token = JWTAuth::fromUser($user);

        return [
            'Authorization' => "Bearer $token",
        ];
    }

    private function cancelOrder(?int $orderId = null, ?string $departureDate = null): array
    {
        return [
            'order_id' => $orderId,
            'message' => fake()->text(),
        ];
    }

    //    public function test_it_lists_cancel_orders_with_default_pagination_and_ordering(): void
    //    {
    //        $user = User::factory()->create();
    //        $user->assignRole('writer');
    //        $order = Order::factory()->for($user)->create();
    //        CancelOrderApproved::factory()->forOrder($order->order_id)->create();
    //
    //        $response = $this->getJson('/api/cancel-order/list', $this->authHeaders($user));
    //
    //        $response->assertStatus(Response::HTTP_OK)
    //            ->assertJsonStructure([
    //                'data' => [
    //                    '*' => [
    //                        'id',
    //                        'message',
    //                        'status',
    //                        'order' => [
    //                            'orderId',
    //                            'name',
    //                            'destiny',
    //                            'departureDate',
    //                            'returnDate',
    //                            'status',
    //                        ]
    //                    ],
    //                ],
    //            ]);
    //    }
    //
    //    public function test_it_requires_authentication(): void
    //    {
    //        $response = $this->getJson('/api/cancel-order/list');
    //
    //        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    //    }
    //
    //    public function test_it_show_order_by_admin(): void
    //    {
    //        $cancelOrder = CancelOrderApproved::factory()->create();
    //        $admin = User::factory()->create();
    //        $admin->assignRole('admin');
    //
    //        $response = $this->getJson('/api/cancel-order/show/' . $cancelOrder->id, $this->authHeaders($admin));
    //
    //        $response->assertStatus(Response::HTTP_OK)
    //            ->assertJsonStructure([
    //                'data' => [
    //                    'id',
    //                    'message',
    //                    'status',
    //                    'order' => [
    //                        'orderId',
    //                        'name',
    //                        'destiny',
    //                        'departureDate',
    //                        'returnDate',
    //                        'status',
    //                    ]
    //                ],
    //            ]);
    //    }
    //
    //    public function test_it_show_order_by_user(): void
    //    {
    //        $user = User::factory()->create();
    //        $user->assignRole('writer');
    //        $order = Order::factory()->for($user)->create();
    //        $cancelOrder = CancelOrderApproved::factory()->forOrder($order->order_id)->create();
    //
    //        $response = $this->getJson('/api/cancel-order/show/' . $cancelOrder->id, $this->authHeaders($user));
    //
    //        $response->assertStatus(Response::HTTP_OK)
    //            ->assertJsonStructure([
    //                'data' => [
    //                    'id',
    //                    'message',
    //                    'status',
    //                    'order' => [
    //                        'orderId',
    //                        'name',
    //                        'destiny',
    //                        'departureDate',
    //                        'returnDate',
    //                        'status',
    //                    ]
    //                ],
    //            ]);
    //    }
    //
    //    public function test_it_show_order_by_other_user(): void
    //    {
    //        $user = User::factory()->create();
    //        $user->assignRole('writer');
    //        $cancelOrder = CancelOrderApproved::factory()->create();
    //
    //        $response = $this->getJson('/api/cancel-order/show/' . $cancelOrder->order_id, $this->authHeaders($user));
    //
    //        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    //    }
    //
    //    public function test_it_store_order(): void
    //    {
    //        $user = User::factory()->create();
    //        $user->assignRole('writer');
    //        $order = Order::factory()->for($user)->create(['status' => OrderEnum::STATUS_APPROVED]);
    //
    //        $response = $this->postJson(
    //            '/api/cancel-order/store',
    //            $this->cancelOrder($order->order_id),
    //            $this->authHeaders($user)
    //        );
    //
    //        $response->assertStatus(Response::HTTP_CREATED)
    //            ->assertJsonStructure([
    //                'data' => [
    //                    'id',
    //                    'message',
    //                    'status',
    //                    'order' => [
    //                        'orderId',
    //                        'name',
    //                        'destiny',
    //                        'departureDate',
    //                        'returnDate',
    //                        'status',
    //                    ]
    //                ],
    //            ]);
    //    }
    //
    //    public function test_it_store_cancel_order_with_invalid_date(): void
    //    {
    //        $user = User::factory()->create();
    //        $user->assignRole('writer');
    //
    //        $response = $this->postJson(
    //            '/api/cancel-order/store',
    //            $this->cancelOrder(),
    //            $this->authHeaders($user)
    //        );
    //
    //        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
    //            ->assertJsonStructure(['message']);
    //    }
    //
    //    public function test_it_store_cancel_order_with_cancel_order_exists(): void
    //    {
    //        $user = User::factory()->create();
    //        $user->assignRole('writer');
    //        $order = Order::factory()->for($user)->create(['status' => OrderEnum::STATUS_APPROVED]);
    //        CancelOrderApproved::factory()->forOrder($order->order_id)->create();
    //
    //        $response = $this->postJson(
    //            '/api/cancel-order/store',
    //            $this->cancelOrder($order->order_id),
    //            $this->authHeaders($user)
    //        );
    //
    //        $response->assertStatus(Response::HTTP_CONFLICT)
    //            ->assertJsonStructure(['message']);
    //    }
    //
    //    public function test_it_store_cancel_order_with_order_not_approved(): void
    //    {
    //        $user = User::factory()->create();
    //        $user->assignRole('writer');
    //        $order = Order::factory()->for($user)->create();
    //
    //        $response = $this->postJson(
    //            '/api/cancel-order/store',
    //            $this->cancelOrder($order->order_id),
    //            $this->authHeaders($user)
    //        );
    //
    //        $response->assertStatus(Response::HTTP_CONFLICT)
    //            ->assertJsonStructure(['message']);
    //    }

    public function test_it_update_status_cancel_order(): void
    {
        $order = Order::factory()->create(['status' => OrderEnum::STATUS_APPROVED]);
        $cancelOrder = CancelOrderApproved::factory()->forOrder($order->order_id)->create();

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->putJson(
            '/api/cancel-order/update-status',
            ['id' => $cancelOrder->id, 'status' => OrderEnum::STATUS_APPROVED],
            $this->authHeaders($admin)
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'message',
            ]);
    }

    public function test_it_update_status_cancel_order_approved(): void
    {

        $order = Order::factory()->create(['status' => OrderEnum::STATUS_APPROVED]);
        $cancelOrder = CancelOrderApproved::factory()->forOrder($order->order_id)->create(['status' => OrderEnum::STATUS_APPROVED]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->putJson(
            '/api/cancel-order/update-status',
            ['id' => $cancelOrder->id, 'status' => OrderEnum::STATUS_APPROVED],
            $this->authHeaders($admin)
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(['message']);
    }
}
