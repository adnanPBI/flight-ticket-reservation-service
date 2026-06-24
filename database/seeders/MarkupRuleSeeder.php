<?php

namespace Database\Seeders;

use App\Models\MarkupRule;
use Illuminate\Database\Seeder;

class MarkupRuleSeeder extends Seeder
{
    public function run(): void
    {
        MarkupRule::query()->updateOrCreate(
            ['name' => 'Default global markup'],
            [
                'scope' => 'global',
                'match_rules' => [],
                'calculation_type' => 'fixed',
                'value' => 10.00,
                'currency' => null,
                'priority' => 100,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(6),
            ]
        );
    }
}
