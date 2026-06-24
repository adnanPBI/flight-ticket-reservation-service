<?php

namespace App\Data;

final readonly class ProviderOrderResultData
{
    /** @param array<string, mixed> $rawPayload */
    public function __construct(
        public string $provider,
        public string $providerOrderId,
        public ?string $pnr,
        public ?string $ticketNumber,
        public string $status,
        public array $rawPayload = [],
    ) {}

    public function isTicketed(): bool
    {
        return in_array(strtolower($this->status), ['ticketed', 'issued', 'fulfilled'], true);
    }

    public function isConfirmed(): bool
    {
        return in_array(strtolower($this->status), ['confirmed', 'created', 'completed', 'ticketed', 'issued', 'fulfilled'], true);
    }

    public function needsTicketingSync(): bool
    {
        return in_array(strtolower($this->status), ['pending', 'ticketing_pending', 'awaiting_ticketing', 'processing'], true);
    }
}
