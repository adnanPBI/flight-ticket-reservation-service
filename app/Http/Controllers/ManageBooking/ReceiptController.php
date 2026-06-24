<?php

namespace App\Http\Controllers\ManageBooking;

use App\Actions\Booking\AuthorizeManageBookingAccessAction;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Contracts\View\View;

class ReceiptController extends Controller
{
    public function __invoke(Booking $booking, AuthorizeManageBookingAccessAction $access): View
    {
        abort_unless($access->allows(request(), $booking), 403, 'Verify the booking reference and customer email before viewing this receipt.');

        $booking->load(['payments' => fn ($query) => $query->latest('id'), 'priceBreakdowns', 'segments', 'passengers']);

        return view('receipts.booking', ['booking' => $booking]);
    }
}
