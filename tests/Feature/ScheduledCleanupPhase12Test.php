<?php

namespace Tests\Feature;

use Tests\TestCase;

class ScheduledCleanupPhase12Test extends TestCase
{
    public function test_expired_search_cleanup_clears_unbooked_raw_payloads(): void
    {
        $this->markTestSkipped('Scaffold test: create expired searches/offers and assert raw_payload is cleared only for unbooked offers.');
    }

    public function test_abandoned_payment_cleanup_cancels_only_unpaid_old_payments(): void
    {
        $this->markTestSkipped('Scaffold test: create old pending payments and successful payments, then assert only old unpaid payments are cancelled.');
    }
}
