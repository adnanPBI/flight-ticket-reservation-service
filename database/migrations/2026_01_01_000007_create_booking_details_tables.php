<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_passengers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('passenger_type')->index();
            $table->string('title')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->string('gender')->nullable();
            $table->string('nationality', 2)->nullable();
            $table->string('passport_number')->nullable();
            $table->date('passport_expiry_date')->nullable();
            $table->string('provider_passenger_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('booking_segments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('segment_order')->default(1);
            $table->string('airline_code')->nullable()->index();
            $table->string('flight_number')->nullable();
            $table->string('aircraft')->nullable();
            $table->string('origin', 3)->index();
            $table->string('destination', 3)->index();
            $table->timestamp('departure_at')->nullable()->index();
            $table->timestamp('arrival_at')->nullable()->index();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->string('booking_class')->nullable();
            $table->string('cabin_class')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_price_breakdowns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('type')->index();
            $table->string('currency', 3)->default('USD');
            $table->bigInteger('amount_minor')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_price_breakdowns');
        Schema::dropIfExists('booking_segments');
        Schema::dropIfExists('booking_passengers');
    }
};
