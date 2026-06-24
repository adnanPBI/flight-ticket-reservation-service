<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('markup_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('scope')->default('global')->index();
            $table->json('match_rules')->nullable();
            $table->string('calculation_type')->index();
            $table->decimal('value', 12, 4)->default(0);
            $table->string('currency', 3)->nullable();
            $table->unsignedInteger('priority')->default(100)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('promo_codes', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->string('discount_type')->index();
            $table->decimal('value', 12, 4)->default(0);
            $table->string('currency', 3)->nullable();
            $table->unsignedBigInteger('max_discount_minor')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
        Schema::dropIfExists('markup_rules');
    }
};
