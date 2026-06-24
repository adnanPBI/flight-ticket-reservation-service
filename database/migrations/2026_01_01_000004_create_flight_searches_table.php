<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('flight_searches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('search_reference')->unique();
            $table->string('provider')->index();
            $table->string('origin', 3)->index();
            $table->string('destination', 3)->index();
            $table->date('departure_date')->index();
            $table->date('return_date')->nullable()->index();
            $table->string('trip_type')->index();
            $table->unsignedTinyInteger('adult_count')->default(1);
            $table->unsignedTinyInteger('child_count')->default(0);
            $table->unsignedTinyInteger('infant_count')->default(0);
            $table->string('cabin_class')->default('economy')->index();
            $table->string('currency', 3)->default('USD');
            $table->timestamp('expires_at')->nullable()->index();
            $table->json('raw_request')->nullable();
            $table->json('raw_response_summary')->nullable();
            $table->timestamps();

            $table->index(['origin', 'destination', 'departure_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_searches');
    }
};
