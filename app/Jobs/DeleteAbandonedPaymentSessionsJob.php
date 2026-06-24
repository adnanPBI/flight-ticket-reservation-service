<?php

namespace App\Jobs;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\Payment\PaymentStateMachine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteAbandonedPaymentSessionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 60;

    public function handle(PaymentStateMachine $paymentStateMachine): void
    {
        $cutoff = now()->subMinutes((int) config('cleanup.abandoned_payment_minutes', 120));

        $payments = Payment::query()
            ->whereIn('status', [
                PaymentStatus::Created->value,
                PaymentStatus::RequiresPaymentMethod->value,
                PaymentStatus::RequiresAction->value,
                PaymentStatus::Processing->value,
            ])
            ->where('created_at', '<', $cutoff)
            ->whereHas('booking', fn ($query) => $query->whereIn('status', [
                BookingStatus::PaymentPending->value,
                BookingStatus::PassengerDetailsAdded->value,
            ]))
            ->limit(100)
            ->get();

        foreach ($payments as $payment) {
            if ($payment->status->canTransitionTo(PaymentStatus::Cancelled)) {
                $paymentStateMachine->transition(
                    payment: $payment,
                    nextStatus: PaymentStatus::Cancelled,
                    reason: 'Abandoned payment session expired by scheduled cleanup.',
                    metadata: ['cutoff' => $cutoff->toDateTimeString()]
                );
            }
        }

        Log::info('Abandoned payment sessions cleanup completed.', ['cancelled_payments' => $payments->count()]);
    }
}
