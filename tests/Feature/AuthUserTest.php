<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    public function test_register_user_successfully(): void
    {
        $password = fake()->password();
        $inputData = [
            'name' => fake()->name(),
            'email' => fake()->email(),
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $userMock = Mockery::mock(User::class)->makePartial();
        $userMock->id = 1;

        $response = $this->postJson('api/register', $inputData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(['token']);
    }

    public function test_register_fails_if_passwords_do_not_match(): void
    {
        $inputData = [
            'name' => fake()->name(),
            'email' => fake()->email(),
            'password' => fake()->password(),
            'password_confirmation' => fake()->password(),
        ];

        $response = $this->postJson('/api/register', $inputData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(['message']);
    }

    public function test_register_fails_if_email_already_exists(): void
    {
        $email = fake()->email();
        User::factory()->create([
            'email' => $email,
        ]);

        $inputData = [
            'name' => fake()->name(),
            'email' => fake()->email(),
            'password' => fake()->password(),
            'password_confirmation' => fake()->password(),
        ];

        $response = $this->postJson('/api/register', $inputData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(['message']);
    }

    public function test_it_logs_in_with_valid_credentials_and_returns_jwt_token(): void
    {
        $password = fake()->password();
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        $inputData = [
            'email' => $user->email,
            'password' => $password,
        ];

        $response = $this->postJson('/api/login', $inputData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['token']);
    }

    public function test_it_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('correct-password'),
        ]);

        $inputData = [
            'email' => $user->email,
            'password' => 'wrong-password',
        ];

        $response = $this->postJson('/api/login', $inputData);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJsonStructure(['error']);
    }

    public function test_it_returns_error_if_jwt_cannot_be_created(): void
    {
        $inputData = [
            'email' => 'not-an-email',
            'password' => 'short',
        ];

        $response = $this->postJson('/api/login', $inputData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_it_logs_out_with_valid_token(): void
    {
        $user = User::factory()->create();

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => __('message.success.logout'),
            ]);
    }

    public function test_it_fails_to_logout_without_token(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJsonStructure(['message']);
    }
}
