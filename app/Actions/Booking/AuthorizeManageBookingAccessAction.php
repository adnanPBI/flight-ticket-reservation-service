<?php

namespace App\Actions\Booking;

use App\Models\Booking;
use Illuminate\Http\Request;

class AuthorizeManageBookingAccessAction
{
    public function allows(Request $request, Booking $booking): bool
    {
        $user = $request->user();

        if ($user && $booking->user_id && $booking->user_id === $user->id) {
            return true;
        }

        return (bool) $request->session()->get($this->sessionKey($booking));
    }

    public function grant(Request $request, Booking $booking): void
    {
        $request->session()->put($this->sessionKey($booking), true);
    }

    public function sessionKey(Booking $booking): string
    {
        return 'manage_booking_access.' . $booking->getKey();
    }
}
