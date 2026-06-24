<?php

namespace App\Actions\Admin;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Services\Booking\BookingStateMachine;

class MarkBookingRefundPendingAction
{
    public function __construct(private readonly BookingStateMachine $stateMachine) {}

    public function handle(Booking $booking, ?int $actorUserId = null, ?string $reason = null): Booking
    {
        return $this->stateMachine->transition(
            booking: $booking,
            nextStatus: BookingStatus::RefundPending,
            actorUserId: $actorUserId,
            reason: $reason ?: 'Marked refund pending by admin.',
            metadata: ['source' => 'filament_admin']
        );
    }
}
