<?php

namespace App\Http\Controllers\Payments;

use App\Actions\Booking\AuthorizeManageBookingAccessAction;
use App\Actions\Payment\MarkPaymentSucceededAction;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MockStripePaymentSuccessController extends Controller
{
    public function __invoke(Request $request, Booking $booking, Payment $payment, MarkPaymentSucceededAction $action, AuthorizeManageBookingAccessAction $access): JsonResponse
    {
        abort_unless($access->allows($request, $booking), 403, 'You are not authorized to manage payments for this booking.');

        abort_unless(config('stripe.mock_mode'), 403, 'Mock payment success is disabled when STRIPE_MOCK_MODE=false.');
        abort_unless($payment->booking_id === $booking->id, 404);
        abort_unless($payment->provider === 'stripe', 409, 'Only Stripe mock payments can be simulated here.');

        $action->execute(
            payment: $payment,
            providerEventId: 'evt_mock_payment_'.$payment->id,
            metadata: ['source' => 'mock_success_endpoint']
        );

        return response()->json([
            'message' => 'Mock payment marked as succeeded. No real Stripe charge happened.',
            'booking' => [
                'reference' => $booking->refresh()->booking_reference,
                'status' => $booking->status->value,
            ],
            'payment' => [
                'id' => $payment->id,
                'status' => $payment->refresh()->status->value,
            ],
            'confirmation_url' => route('flights.confirmation', ['booking' => $booking->booking_reference]),
        ]);
    }
}
