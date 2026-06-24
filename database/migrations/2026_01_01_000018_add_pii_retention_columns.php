<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->timestamp('pii_redacted_at')->nullable()->after('customer_phone');
        });

        // Allow date_of_birth to be nulled once a passenger's PII is redacted.
        Schema::table('booking_passengers', function (Blueprint $table): void {
            $table->date('date_of_birth')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('booking_passengers', function (Blueprint $table): void {
            $table->date('date_of_birth')->nullable(false)->change();
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn('pii_redacted_at');
        });
    }
};
