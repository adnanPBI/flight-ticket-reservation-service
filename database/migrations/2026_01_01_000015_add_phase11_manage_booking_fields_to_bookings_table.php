<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->timestamp('customer_last_viewed_at')->nullable()->after('failed_at');
            $table->timestamp('last_manage_lookup_at')->nullable()->after('customer_last_viewed_at');
            $table->unsignedInteger('manage_lookup_count')->default(0)->after('last_manage_lookup_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn(['customer_last_viewed_at', 'last_manage_lookup_at', 'manage_lookup_count']);
        });
    }
};
