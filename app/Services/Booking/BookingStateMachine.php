<?php

namespace App\Services\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingEvent;
use App\Support\AuditLogger;
use DomainException;
use Illuminate\Support\Facades\DB;

class BookingStateMachine
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function transition(
        Booking $booking,
        BookingStatus $nextStatus,
        ?int $actorUserId = null,
        ?string $reason = null,
        array $metadata = []
    ): Booking {
        $current = $booking->status;

        if (! $current->canTransitionTo($nextStatus)) {
            throw new DomainException("Invalid booking status transition from {$current->value} to {$nextStatus->value}.");
        }

        return DB::transaction(function () use ($booking, $current, $nextStatus, $actorUserId, $reason, $metadata) {
            $booking->forceFill([
                'status' => $nextStatus,
            ])->save();

            BookingEvent::query()->create([
                'booking_id' => $booking->id,
                'from_status' => $current->value,
                'to_status' => $nextStatus->value,
                'actor_user_id' => $actorUserId,
                'reason' => $reason,
                'metadata' => $metadata,
            ]);

            $this->auditLogger->record(
                action: 'booking.status_changed',
                auditableType: Booking::class,
                auditableId: $booking->id,
                actorUserId: $actorUserId,
                oldValues: ['status' => $current->value],
                newValues: ['status' => $nextStatus->value],
                metadata: ['reason' => $reason, ...$metadata]
            );

            return $booking->refresh();
        });
    }
}
