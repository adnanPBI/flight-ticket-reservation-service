<?php

namespace App\Http\Controllers\Flights;

use App\Actions\Booking\AuthorizeManageBookingAccessAction;
use App\Actions\Booking\CreateBookingFromOfferAction;
use App\Http\Controllers\Controller;
use App\Models\FlightOffer;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OfferSelectionController extends Controller
{
    public function store(Request $request, FlightOffer $flightOffer, CreateBookingFromOfferAction $action, AuthorizeManageBookingAccessAction $access): RedirectResponse
    {
        try {
            $booking = $action->execute($flightOffer, $request->user()?->id);
        } catch (DomainException $exception) {
            return back()->withErrors(['offer' => $exception->getMessage()]);
        }

        $access->grant($request, $booking);

        return redirect()
            ->route('flights.passengers', $booking->booking_reference)
            ->with('success', 'Fare selected and revalidated. Add passenger details before the offer expires.');
    }
}
