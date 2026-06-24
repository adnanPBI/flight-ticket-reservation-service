<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\SupportAgent;
use Illuminate\Database\Seeder;

class SupportAgentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = AdminUser::query()->where('email', 'admin@example.com')->first();

        if ($admin) {
            SupportAgent::query()->updateOrCreate(
                ['admin_user_id' => $admin->id],
                ['display_name' => 'Support Desk', 'is_online' => false]
            );
        }
    }
}
