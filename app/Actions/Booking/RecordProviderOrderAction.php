<?php

namespace App\Actions\Booking;

use App\Data\ProviderOrderResultData;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Services\Booking\BookingStateMachine;
use App\Support\AuditLogger;
use App\Support\ProviderOrderStatusMapper;
use Illuminate\Support\Facades\DB;

class RecordProviderOrderAction
{
    public function __construct(
        private readonly BookingStateMachine $bookingStateMachine,
        private readonly ProviderOrderStatusMapper $statusMapper,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(Booking $booking, ProviderOrderResultData $orderResult): Booking
    {
        return DB::transaction(function () use ($booking, $orderResult): Booking {
            $booking = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            $targetStatus = $this->statusMapper->targetBookingStatus($orderResult->status);

            $booking->forceFill([
                'provider_order_id' => $orderResult->providerOrderId,
                'provider_order_status' => $orderResult->status,
                'provider_order_payload' => $orderResult->rawPayload,
                'pnr' => $orderResult->pnr,
                'ticket_number' => $orderResult->ticketNumber,
                'confirmed_at' => $orderResult->isConfirmed() ? ($booking->confirmed_at ?: now()) : $booking->confirmed_at,
                'ticketed_at' => $orderResult->isTicketed() ? ($booking->ticketed_at ?: now()) : $booking->ticketed_at,
                'ticketing_last_checked_at' => now(),
                'failure_reason' => null,
                'failed_at' => null,
            ])->save();

            $booking = $booking->refresh();

            if ($targetStatus === BookingStatus::Ticketed) {
                if ($booking->status === BookingStatus::BookingConfirming) {
                    $booking = $this->bookingStateMachine->transition(
                        booking: $booking,
                        nextStatus: BookingStatus::BookingConfirmed,
                        reason: 'Provider order created. Ticketing appears complete.',
                        metadata: ['provider_order_id' => $orderResult->providerOrderId]
                    );
                }

                if (in_array($booking->status, [BookingStatus::BookingConfirmed, BookingStatus::TicketingPending], true)) {
                    $booking = $this->bookingStateMachine->transition(
                        booking: $booking,
                        nextStatus: BookingStatus::Ticketed,
                        reason: 'Provider order ticketed.',
                        metadata: ['provider_order_id' => $orderResult->providerOrderId, 'ticket_number' => $orderResult->ticketNumber]
                    );
                }
            } elseif ($targetStatus === BookingStatus::BookingConfirmed && $booking->status === BookingStatus::BookingConfirming) {
                $booking = $this->bookingStateMachine->transition(
                    booking: $booking,
                    nextStatus: BookingStatus::BookingConfirmed,
                    reason: 'Provider order confirmed.',
                    metadata: ['provider_order_id' => $orderResult->providerOrderId, 'provider_status' => $orderResult->status]
                );
            } elseif ($targetStatus === BookingStatus::TicketingPending && in_array($booking->status, [BookingStatus::BookingConfirming, BookingStatus::BookingConfirmed], true)) {
                $booking = $this->bookingStateMachine->transition(
                    booking: $booking,
                    nextStatus: BookingStatus::TicketingPending,
                    reason: 'Provider order requires ticketing follow-up.',
                    metadata: ['provider_order_id' => $orderResult->providerOrderId, 'provider_status' => $orderResult->status]
                );
            }

            $this->auditLogger->record(
                action: 'booking.provider_order_recorded',
                auditableType: Booking::class,
                auditableId: $booking->id,
                newValues: [
                    'provider_order_id' => $orderResult->providerOrderId,
                    'provider_order_status' => $orderResult->status,
                    'pnr' => $orderResult->pnr,
                    'ticket_number' => $orderResult->ticketNumber,
                ],
                metadata: ['provider' => $orderResult->provider]
            );

            return $booking->refresh();
        });
    }
}
