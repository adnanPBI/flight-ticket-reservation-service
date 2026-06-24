<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->foreignId('applied_promo_code_id')->nullable()->after('discount_amount_minor')->constrained('promo_codes')->nullOnDelete();
            $table->string('applied_promo_code')->nullable()->after('applied_promo_code_id')->index();
            $table->json('pricing_snapshot')->nullable()->after('applied_promo_code');
            $table->timestamp('pricing_locked_at')->nullable()->after('pricing_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropForeign(['applied_promo_code_id']);
            $table->dropColumn(['applied_promo_code_id', 'applied_promo_code', 'pricing_snapshot', 'pricing_locked_at']);
        });
    }
};
