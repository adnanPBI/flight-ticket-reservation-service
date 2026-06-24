<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('airports', function (Blueprint $table): void {
            $table->id();
            $table->string('iata_code', 3)->unique();
            $table->string('icao_code', 4)->nullable()->index();
            $table->string('name');
            $table->string('city')->index();
            $table->string('country')->index();
            $table->string('timezone')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('airlines', function (Blueprint $table): void {
            $table->id();
            $table->string('iata_code', 2)->unique();
            $table->string('icao_code', 3)->nullable()->index();
            $table->string('name');
            $table->string('country')->nullable()->index();
            $table->string('logo_url')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('airlines');
        Schema::dropIfExists('airports');
    }
};
