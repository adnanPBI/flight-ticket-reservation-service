<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AirportSeeder::class,
            AirlineSeeder::class,
            AdminUserSeeder::class,
            SupportAgentSeeder::class,
            MarkupRuleSeeder::class,
            PromoCodeSeeder::class,
        ]);
    }
}
