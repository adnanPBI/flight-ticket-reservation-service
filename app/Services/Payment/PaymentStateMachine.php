<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Support\AuditLogger;
use DomainException;
use Illuminate\Support\Facades\DB;

class PaymentStateMachine
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function transition(
        Payment $payment,
        PaymentStatus $nextStatus,
        ?int $actorUserId = null,
        ?string $reason = null,
        array $metadata = [],
        ?string $providerEventId = null
    ): Payment {
        $current = $payment->status;

        if (! $current->canTransitionTo($nextStatus)) {
            throw new DomainException("Invalid payment status transition from {$current->value} to {$nextStatus->value}.");
        }

        return DB::transaction(function () use ($payment, $current, $nextStatus, $actorUserId, $reason, $metadata, $providerEventId) {
            $payment->forceFill([
                'status' => $nextStatus,
            ])->save();

            PaymentEvent::query()->create([
                'payment_id' => $payment->id,
                'from_status' => $current->value,
                'to_status' => $nextStatus->value,
                'actor_user_id' => $actorUserId,
                'reason' => $reason,
                'metadata' => $metadata,
                'provider_event_id' => $providerEventId,
            ]);

            $this->auditLogger->record(
                action: 'payment.status_changed',
                auditableType: Payment::class,
                auditableId: $payment->id,
                actorUserId: $actorUserId,
                oldValues: ['status' => $current->value],
                newValues: ['status' => $nextStatus->value],
                metadata: ['reason' => $reason, ...$metadata]
            );

            return $payment->refresh();
        });
    }
}
