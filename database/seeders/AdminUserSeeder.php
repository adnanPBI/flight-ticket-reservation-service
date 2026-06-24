<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        AdminUser::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'OTA Admin',
                'password' => Hash::make('ChangeMe123!'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );
    }
}
