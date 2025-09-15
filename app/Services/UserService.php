<?php

namespace App\Services;

use App\Enums\RoleEnum;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;

class UserService
{
    public function __construct(protected UserRepository $userRepository) {}

    public function store($data): Model
    {
        $user = $this->userRepository->create($data);

        if (! $user->id) {
            throw new Exception;
        }

        $user->assignRole(RoleEnum::WRITER);

        return $user;
    }
}
