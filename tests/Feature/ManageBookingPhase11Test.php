<?php

namespace Tests\Feature;

use Tests\TestCase;

class ManageBookingPhase11Test extends TestCase
{
    public function test_manage_booking_lookup_page_can_render(): void
    {
        $this->markTestSkipped('Install Laravel dependencies and factories before enabling Phase 11 HTTP assertions.');
    }

    public function test_guest_booking_requires_reference_and_email_verification(): void
    {
        $this->markTestSkipped('Create a booking fixture, then assert unverified access is denied and verified lookup grants session access.');
    }
}
