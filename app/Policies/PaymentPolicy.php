<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function view(User|AdminUser $actor, Payment $payment): bool
    {
        if ($actor instanceof AdminUser) {
            return $actor->is_active && in_array($actor->role, ['super_admin', 'admin', 'finance', 'operations'], true);
        }

        return $payment->booking?->user_id !== null && $payment->booking->user_id === $actor->id;
    }

    public function update(User|AdminUser $actor, Payment $payment): bool
    {
        return $actor instanceof AdminUser
            && $actor->is_active
            && in_array($actor->role, ['super_admin', 'admin', 'finance'], true);
    }
}
