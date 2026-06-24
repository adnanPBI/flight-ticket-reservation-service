<?php

namespace App\Data;

final readonly class ProviderCancellationResultData
{
    /** @param array<string, mixed> $rawPayload */
    public function __construct(
        public string $provider,
        public string $providerOrderId,
        public string $status,
        public ?string $refundReference = null,
        public array $rawPayload = [],
    ) {}
}
