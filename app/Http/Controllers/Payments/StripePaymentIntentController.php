<?php

namespace App\Http\Controllers\Payments;

use App\Actions\Booking\AuthorizeManageBookingAccessAction;
use App\Actions\Payment\CreateStripePaymentIntentAction;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class StripePaymentIntentController extends Controller
{
    public function __invoke(Request $request, Booking $booking, CreateStripePaymentIntentAction $action, AuthorizeManageBookingAccessAction $access): JsonResponse
    {
        abort_unless($access->allows($request, $booking), 403, 'You are not authorized to start payment for this booking.');

        try {
            return response()->json($action->execute($booking));
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}
