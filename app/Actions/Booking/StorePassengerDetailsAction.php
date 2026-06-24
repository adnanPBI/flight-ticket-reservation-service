<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Services\Booking\BookingStateMachine;
use DomainException;
use Illuminate\Support\Facades\DB;

class StorePassengerDetailsAction
{
    public function __construct(private readonly BookingStateMachine $stateMachine) {}

    /** @param array<string, mixed> $payload */
    public function execute(Booking $booking, array $payload, ?int $userId = null): Booking
    {
        if ($booking->offer_expires_at && $booking->offer_expires_at->isPast()) {
            throw new DomainException('This selected offer has expired. Please search again.');
        }

        if (! in_array($booking->status, [BookingStatus::OfferSelected, BookingStatus::PassengerDetailsAdded], true)) {
            throw new DomainException('Passenger details can only be added after a fare has been selected.');
        }

        return DB::transaction(function () use ($booking, $payload, $userId): Booking {
            $booking->forceFill([
                'customer_email' => $payload['customer_email'],
                'customer_phone' => $payload['customer_phone'],
            ])->save();

            $booking->passengers()->delete();

            foreach ($payload['passengers'] as $passenger) {
                $booking->passengers()->create([
                    'passenger_type' => $passenger['passenger_type'],
                    'title' => $passenger['title'] ?? null,
                    'first_name' => trim((string) $passenger['first_name']),
                    'last_name' => trim((string) $passenger['last_name']),
                    'date_of_birth' => $passenger['date_of_birth'],
                    'gender' => $passenger['gender'] ?? null,
                    'nationality' => $passenger['nationality'] ?? null,
                    'passport_number' => $passenger['passport_number'] ?? null,
                    'passport_expiry_date' => $passenger['passport_expiry_date'] ?? null,
                ]);
            }

            if ($booking->status === BookingStatus::OfferSelected) {
                $booking = $this->stateMachine->transition(
                    booking: $booking,
                    nextStatus: BookingStatus::PassengerDetailsAdded,
                    actorUserId: $userId,
                    reason: 'Customer added passenger details.',
                    metadata: [
                        'passenger_count' => count($payload['passengers']),
                        'customer_email' => $payload['customer_email'],
                    ],
                );
            }

            return $booking->refresh()->load(['passengers', 'segments', 'priceBreakdowns', 'offer', 'search']);
        });
    }
}
