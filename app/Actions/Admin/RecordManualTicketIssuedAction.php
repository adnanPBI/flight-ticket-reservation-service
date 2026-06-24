<?php

namespace App\Actions\Admin;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Services\Booking\BookingStateMachine;
use Illuminate\Support\Facades\DB;

class RecordManualTicketIssuedAction
{
    public function __construct(private readonly BookingStateMachine $stateMachine) {}

    public function handle(Booking $booking, string $ticketNumber, ?string $pnr = null, ?int $actorUserId = null): Booking
    {
        return DB::transaction(function () use ($booking, $ticketNumber, $pnr, $actorUserId) {
            $booking->forceFill([
                'ticket_number' => $ticketNumber,
                'pnr' => $pnr ?: $booking->pnr,
                'ticketed_at' => now(),
            ])->save();

            if ($booking->status !== BookingStatus::Ticketed) {
                $booking = $this->stateMachine->transition(
                    booking: $booking,
                    nextStatus: BookingStatus::Ticketed,
                    actorUserId: $actorUserId,
                    reason: 'Manual ticket number recorded by admin.',
                    metadata: ['ticket_number' => $ticketNumber, 'pnr' => $pnr]
                );
            }

            return $booking->refresh();
        });
    }
}
