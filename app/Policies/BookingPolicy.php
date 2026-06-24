<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User|AdminUser $actor, Booking $booking): bool
    {
        if ($actor instanceof AdminUser) {
            return $actor->is_active;
        }

        return $booking->user_id !== null && $booking->user_id === $actor->id;
    }

    public function update(User|AdminUser $actor, Booking $booking): bool
    {
        return $actor instanceof AdminUser
            && $actor->is_active
            && in_array($actor->role, ['super_admin', 'admin', 'operations'], true);
    }

    public function retryFinalization(User|AdminUser $actor, Booking $booking): bool
    {
        return $this->update($actor, $booking);
    }

    public function markRefundPending(User|AdminUser $actor, Booking $booking): bool
    {
        return $actor instanceof AdminUser
            && $actor->is_active
            && in_array($actor->role, ['super_admin', 'admin', 'finance'], true);
    }
}
