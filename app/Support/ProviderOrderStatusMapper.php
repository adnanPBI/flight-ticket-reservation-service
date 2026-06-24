<?php

namespace App\Support;

use App\Enums\BookingStatus;

class ProviderOrderStatusMapper
{
    public function targetBookingStatus(string $providerStatus): BookingStatus
    {
        return match (strtolower($providerStatus)) {
            'ticketed', 'issued', 'fulfilled' => BookingStatus::Ticketed,
            'confirmed', 'created', 'completed' => BookingStatus::BookingConfirmed,
            'pending', 'ticketing_pending', 'awaiting_ticketing', 'processing' => BookingStatus::TicketingPending,
            default => BookingStatus::TicketingPending,
        };
    }
}
