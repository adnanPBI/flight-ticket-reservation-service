<?php

namespace App\Http\Controllers\Pricing;

use App\Actions\Booking\AuthorizeManageBookingAccessAction;
use App\Actions\Pricing\ApplyPromoCodeToBookingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyPromoCodeRequest;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

class ApplyPromoCodeController extends Controller
{
    public function __invoke(ApplyPromoCodeRequest $request, Booking $booking, ApplyPromoCodeToBookingAction $action, AuthorizeManageBookingAccessAction $access): RedirectResponse
    {
        abort_unless($access->allows($request, $booking), 403, 'You are not authorized to modify this booking.');

        try {
            $action->execute($booking, $request->string('code')->toString(), auth()->id());
        } catch (RuntimeException $exception) {
            return back()->withErrors(['promo_code' => $exception->getMessage()]);
        }

        return back()->with('success', 'Promo code applied. Payment must be recreated from the updated total.');
    }
}
