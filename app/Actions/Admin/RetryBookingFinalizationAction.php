<?php

namespace App\Actions\Admin;

use App\Enums\BookingStatus;
use App\Jobs\ConfirmBookingJob;
use App\Jobs\RetryTicketingJob;
use App\Models\Booking;
use App\Support\AuditLogger;
use DomainException;

class RetryBookingFinalizationAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Booking $booking, ?int $actorUserId = null): void
    {
        $allowed = [
            BookingStatus::PaymentSucceeded,
            BookingStatus::BookingFailed,
            BookingStatus::TicketingPending,
        ];

        if (! in_array($booking->status, $allowed, true)) {
            throw new DomainException('This booking status is not retryable from admin.');
        }

        $this->auditLogger->record(
            action: 'admin.booking.retry_requested',
            auditableType: Booking::class,
            auditableId: $booking->id,
            actorUserId: $actorUserId,
            metadata: ['status' => $booking->status->value]
        );

        if ($booking->status === BookingStatus::TicketingPending) {
            RetryTicketingJob::dispatch($booking->id);
            return;
        }

        ConfirmBookingJob::dispatch($booking->id);
    }
}
