<?php

namespace App\Providers;

use App\Models\AdminUser;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\ChatConversation;
use App\Models\Payment;
use App\Policies\AdminUserPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\BookingPolicy;
use App\Policies\ChatConversationPolicy;
use App\Policies\PaymentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        AdminUser::class => AdminUserPolicy::class,
        Booking::class => BookingPolicy::class,
        Payment::class => PaymentPolicy::class,
        ChatConversation::class => ChatConversationPolicy::class,
        AuditLog::class => AuditLogPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('view-admin-dashboard', fn ($admin): bool => $admin?->is_active === true);
    }
}
