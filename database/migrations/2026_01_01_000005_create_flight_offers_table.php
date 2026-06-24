<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('flight_offers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('flight_search_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->index();
            $table->string('provider_offer_id')->index();
            $table->string('airline_code')->nullable()->index();
            $table->string('airline_name')->nullable();
            $table->string('origin', 3)->index();
            $table->string('destination', 3)->index();
            $table->timestamp('departure_at')->nullable()->index();
            $table->timestamp('arrival_at')->nullable()->index();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->unsignedTinyInteger('stops')->default(0);
            $table->string('cabin_class')->default('economy')->index();
            $table->string('fare_brand')->nullable();
            $table->string('baggage_summary')->nullable();
            $table->string('refundability')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->unsignedBigInteger('base_amount_minor')->default(0);
            $table->unsignedBigInteger('tax_amount_minor')->default(0);
            $table->unsignedBigInteger('fee_amount_minor')->default(0);
            $table->unsignedBigInteger('markup_amount_minor')->default(0);
            $table->unsignedBigInteger('discount_amount_minor')->default(0);
            $table->unsignedBigInteger('total_amount_minor')->default(0)->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->json('normalized_payload')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_offer_id']);
            $table->index(['flight_search_id', 'total_amount_minor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_offers');
    }
};
