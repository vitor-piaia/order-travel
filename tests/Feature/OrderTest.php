<?php

namespace Tests\Feature;

use App\Enums\OrderEnum;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderTest extends TestCase
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

    private function order(?int $orderId = null, ?string $departureDate = null): array
    {
        return [
            'order_id' => $orderId ?? fake()->numberBetween(1, 1000),
            'name' => fake()->name(),
            'destiny' => fake()->city(),
            'departure_date' => $departureDate ?? now()->addDay()->format('d/m/Y'),
            'return_date' => now()->addDays(3)->format('d/m/Y'),
        ];
    }

    public function test_it_lists_orders_with_default_pagination_and_ordering(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');
        Order::factory()->count(3)->for($user)->create();

        $response = $this->getJson('/api/order/list', $this->authHeaders($user));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'orderId',
                        'name',
                        'destiny',
                        'departureDate',
                        'returnDate',
                        'status',
                    ],
                ],
            ]);
    }

    public function test_it_lists_orders_with_custom_ordering(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');

        Order::factory()->for($user)->create(['created_at' => now()->subDays(2)]);
        $newerOrder = Order::factory()->for($user)->create(['created_at' => now()]);

        $response = $this->getJson('/api/order/list?order_by=desc', $this->authHeaders($user));

        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals(
            $newerOrder->order_id,
            $response->json('data')[0]['orderId']
        );
    }

    public function test_it_requires_authentication(): void
    {
        $response = $this->getJson('/api/order/list');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_it_show_order_by_admin(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->getJson('/api/order/show/'.$order->order_id, $this->authHeaders($admin));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'orderId',
                    'name',
                    'destiny',
                    'departureDate',
                    'returnDate',
                    'status',
                ],
            ]);
    }

    public function test_it_show_order_by_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');
        $order = Order::factory()->for($user)->create();

        $response = $this->getJson('/api/order/show/'.$order->order_id, $this->authHeaders($user));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'orderId',
                    'name',
                    'destiny',
                    'departureDate',
                    'returnDate',
                    'status',
                ],
            ]);
    }

    public function test_it_show_order_by_other_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');

        $userTwo = User::factory()->create();
        $order = Order::factory()->for($userTwo)->create();

        $response = $this->getJson('/api/order/show/'.$order->order_id, $this->authHeaders($user));

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function test_it_store_order(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');

        $response = $this->postJson('/api/order/store', $this->order(), $this->authHeaders($user));

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'orderId',
                    'name',
                    'destiny',
                    'departureDate',
                    'returnDate',
                    'status',
                ],
            ]);
    }

    public function test_it_store_order_with_invalid_date(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');

        $response = $this->postJson(
            '/api/order/store',
            $this->order(departureDate: now()->subDay()->format('d/m/Y')),
            $this->authHeaders($user)
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(['message']);
    }

    public function test_it_store_order_with_order_exists(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');
        $order = Order::factory()->for($user)->create();

        $response = $this->postJson(
            '/api/order/store',
            $this->order(orderId: $order->order_id),
            $this->authHeaders($user)
        );

        $response->assertStatus(Response::HTTP_CONFLICT)
            ->assertJsonStructure(['message']);
    }

    public function test_it_update_order(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');
        $order = Order::factory()->for($user)->create();

        $response = $this->putJson(
            '/api/order/update',
            $this->order(orderId: $order->order_id),
            $this->authHeaders($user)
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'message',
            ]);
    }

    public function test_it_update_order_with_invalid_date(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');
        $order = Order::factory()->for($user)->create();

        $response = $this->putJson(
            '/api/order/update',
            $this->order(orderId: $order->order_id, departureDate: now()->subDay()->format('d/m/Y')),
            $this->authHeaders($user)
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(['message']);
    }

    public function test_it_update_status_order(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $order = Order::factory()->create();

        $response = $this->putJson(
            '/api/order/update-status',
            ['order_id' => $order->order_id, 'status' => OrderEnum::STATUS_APPROVED],
            $this->authHeaders($admin)
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'message',
            ]);
    }

    public function test_it_update_status_order_approved(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');
        $order = Order::factory()->for($user)->create(['status' => OrderEnum::STATUS_APPROVED]);

        $response = $this->putJson(
            '/api/order/update',
            $this->order(orderId: $order->order_id, departureDate: now()->subDay()->format('d/m/Y')),
            $this->authHeaders($user)
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(['message']);
    }
}
