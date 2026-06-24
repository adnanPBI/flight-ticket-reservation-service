<?php

namespace App\Http\Controllers\ManageBooking;

use App\Actions\Booking\AuthorizeManageBookingAccessAction;
use App\Actions\Booking\BuildManageBookingPayloadAction;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Inertia\Inertia;
use Inertia\Response;

class ShowController extends Controller
{
    public function __invoke(Booking $booking, AuthorizeManageBookingAccessAction $access, BuildManageBookingPayloadAction $payloadAction): Response
    {
        abort_unless($access->allows(request(), $booking), 403, 'Verify the booking reference and customer email before viewing this booking.');

        $booking->forceFill(['customer_last_viewed_at' => now()])->save();

        return Inertia::render('ManageBooking/Show', [
            'booking' => $payloadAction->execute($booking),
        ]);
    }
}
