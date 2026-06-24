<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Null out any legacy duplicate identifiers so the unique constraints apply cleanly.
        foreach (['pnr', 'ticket_number', 'provider_order_id'] as $column) {
            $duplicates = DB::table('bookings')
                ->select($column)
                ->whereNotNull($column)
                ->groupBy($column)
                ->havingRaw('COUNT(*) > 1')
                ->pluck($column);

            if ($duplicates->isNotEmpty()) {
                DB::table('bookings')->whereIn($column, $duplicates)->update([$column => null]);
            }
        }

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropIndex('bookings_pnr_index');
            $table->dropIndex('bookings_ticket_number_index');
            $table->dropIndex('bookings_provider_order_id_index');

            $table->unique('pnr');
            $table->unique('ticket_number');
            $table->unique('provider_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropUnique(['pnr']);
            $table->dropUnique(['ticket_number']);
            $table->dropUnique(['provider_order_id']);

            $table->index('pnr');
            $table->index('ticket_number');
            $table->index('provider_order_id');
        });
    }
};
