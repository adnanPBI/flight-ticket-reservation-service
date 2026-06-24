<?php

namespace Tests\Feature;

use App\Filament\Resources\BookingResource;
use App\Filament\Resources\PaymentResource;
use App\Filament\Resources\SupportConversationResource;
use App\Models\AdminUser;
use Tests\TestCase;

class FilamentAdminPhase8Test extends TestCase
{
    public function test_phase_8_resources_are_registered_classes(): void
    {
        $this->assertTrue(class_exists(BookingResource::class));
        $this->assertTrue(class_exists(PaymentResource::class));
        $this->assertTrue(class_exists(SupportConversationResource::class));
    }

    public function test_admin_user_can_access_panel_when_active(): void
    {
        $admin = new AdminUser(['role' => 'admin', 'is_active' => true]);

        $this->assertTrue($admin->canAccessPanel(new \Filament\Panel('admin')));
    }
}
