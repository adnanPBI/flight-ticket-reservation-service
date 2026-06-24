<?php

namespace Database\Seeders;

use App\Models\PromoCode;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        PromoCode::query()->updateOrCreate(
            ['code' => 'WELCOME10'],
            [
                'description' => 'Test promo: 10% off, capped at 25 USD equivalent minor units for USD bookings.',
                'discount_type' => 'percentage',
                'value' => 10,
                'currency' => null,
                'max_discount_minor' => 2500,
                'usage_limit' => 1000,
                'used_count' => 0,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(6),
            ]
        );
    }
}
