<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->string('provider_order_status')->nullable()->after('provider_order_id')->index();
            $table->json('provider_order_payload')->nullable()->after('provider_order_status');
            $table->timestamp('ticketing_last_checked_at')->nullable()->after('ticketed_at');
            $table->unsignedInteger('ticketing_retry_count')->default(0)->after('ticketing_last_checked_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn([
                'provider_order_status',
                'provider_order_payload',
                'ticketing_last_checked_at',
                'ticketing_retry_count',
            ]);
        });
    }
};
