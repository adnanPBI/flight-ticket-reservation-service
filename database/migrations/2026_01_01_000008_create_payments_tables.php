<?php

use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('stripe')->index();
            $table->string('provider_payment_id')->nullable()->unique();
            $table->string('provider_customer_id')->nullable()->index();
            $table->string('status')->default(PaymentStatus::Created->value)->index();
            $table->string('currency', 3)->default('USD');
            $table->unsignedBigInteger('amount_minor')->default(0);
            $table->unsignedBigInteger('refunded_amount_minor')->default(0);
            $table->string('client_secret_last4', 4)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
        });

        Schema::create('payment_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable()->index();
            $table->string('to_status')->index();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->string('provider_event_id')->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('payments');
    }
};
