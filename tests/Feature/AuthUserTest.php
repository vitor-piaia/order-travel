<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    public function test_register_user_successfully()
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
}
