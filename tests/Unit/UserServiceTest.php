<?php

namespace Tests\Unit;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
use Exception;
use Mockery;

class UserServiceTest extends TestCase
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

    public function test_it_register_user()
    {
        $inputData = [
            'name' => fake()->name(),
            'email' => fake()->email()
        ];

        $userMock = Mockery::mock(User::class)->makePartial();
        $userMock->id = 1;

        $userMock->shouldReceive('assignRole')
            ->once()
            ->with(RoleEnum::WRITER)
            ->andReturnSelf();

        $repositoryMock = Mockery::mock(UserRepository::class);
        $repositoryMock->shouldReceive('create')
            ->once()
            ->with($inputData)
            ->andReturn($userMock);

        $service = new UserService($repositoryMock);

        $result = $service->store($inputData);

        $this->assertSame($userMock, $result);
    }

    public function test_store_throws_exception_if_user_not_created()
    {
        $inputData = [
            'name' => fake()->name(),
            'email' => fake()->email()
        ];

        $userMock = Mockery::mock(User::class)->makePartial();
        $userMock->id = null;

        $repositoryMock = Mockery::mock(UserRepository::class);
        $repositoryMock->shouldReceive('create')
            ->once()
            ->with($inputData)
            ->andReturn($userMock);

        $service = new UserService($repositoryMock);

        $this->expectException(Exception::class);

        $service->store($inputData);
    }
}
