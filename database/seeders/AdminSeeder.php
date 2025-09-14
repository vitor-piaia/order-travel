<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name' => 'admin',
            'email' => 'admin@teste.com',
            'password' => Hash::make('admin123456'),
        ]);

        $user->assignRole(RoleEnum::ADMIN);
    }
}
