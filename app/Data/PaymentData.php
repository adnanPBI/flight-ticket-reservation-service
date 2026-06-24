<?php

namespace App\Data;

final readonly class PaymentData
{
    public function __construct(
        public int $bookingId,
        public string $provider,
        public string $currency,
        public int $amountMinor,
        public ?string $providerPaymentId = null,
    ) {}
}
