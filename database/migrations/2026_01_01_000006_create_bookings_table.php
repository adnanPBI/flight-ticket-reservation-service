<?php

use App\Enums\BookingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('flight_search_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('flight_offer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('booking_reference')->unique();
            $table->string('status')->default(BookingStatus::SearchCreated->value)->index();
            $table->string('provider')->index();
            $table->string('provider_offer_id')->nullable()->index();
            $table->string('provider_order_id')->nullable()->index();
            $table->string('pnr')->nullable()->index();
            $table->string('ticket_number')->nullable()->index();
            $table->string('currency', 3)->default('USD');
            $table->unsignedBigInteger('provider_base_amount_minor')->default(0);
            $table->unsignedBigInteger('tax_amount_minor')->default(0);
            $table->unsignedBigInteger('fee_amount_minor')->default(0);
            $table->unsignedBigInteger('markup_amount_minor')->default(0);
            $table->unsignedBigInteger('discount_amount_minor')->default(0);
            $table->unsignedBigInteger('total_amount_minor')->default(0);
            $table->timestamp('offer_expires_at')->nullable()->index();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('ticketed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->string('customer_email')->nullable()->index();
            $table->string('customer_phone')->nullable()->index();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['provider', 'provider_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
