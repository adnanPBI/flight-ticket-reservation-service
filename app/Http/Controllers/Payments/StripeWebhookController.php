<?php

namespace App\Http\Controllers\Payments;

use App\Actions\Payment\MarkPaymentSucceededAction;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Services\Payment\PaymentStateMachine;
use App\Services\Payment\StripePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        StripePaymentService $stripePaymentService,
        PaymentStateMachine $paymentStateMachine,
        MarkPaymentSucceededAction $markPaymentSucceededAction,
    ): JsonResponse {
        if (config('stripe.mock_mode')) {
            return response()->json([
                'message' => 'Stripe webhook ignored because STRIPE_MOCK_MODE=true. Use the mock success endpoint for local testing.',
            ]);
        }

        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret = config('stripe.webhook_secret');

        if (! $signature || ! $secret) {
            return response()->json(['message' => 'Missing Stripe webhook signature/secret.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (UnexpectedValueException|SignatureVerificationException $exception) {
            report($exception);
            return response()->json(['message' => 'Invalid Stripe webhook payload/signature.'], Response::HTTP_BAD_REQUEST);
        }

        if (PaymentEvent::query()->where('provider_event_id', $event->id)->exists()) {
            return response()->json(['message' => 'Duplicate webhook ignored.']);
        }

        $object = $event->data->object ?? null;
        $providerPaymentId = $object->id ?? null;

        if (! $providerPaymentId) {
            return response()->json(['message' => 'Webhook has no payment intent id.']);
        }

        $payment = Payment::query()->where('provider', 'stripe')->where('provider_payment_id', $providerPaymentId)->first();

        if (! $payment) {
            return response()->json(['message' => 'Payment not found for webhook.']);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $markPaymentSucceededAction->execute($payment, $event->id, ['stripe_event_type' => $event->type]);

            return response()->json(['message' => 'Payment marked succeeded.']);
        }

        if (in_array($event->type, ['payment_intent.payment_failed', 'payment_intent.canceled', 'payment_intent.processing', 'payment_intent.requires_action'], true)) {
            $mappedStatus = $stripePaymentService->mapStripeStatus((string) ($object->status ?? 'processing'));

            if ($payment->status !== $mappedStatus && $payment->status->canTransitionTo($mappedStatus)) {
                $paymentStateMachine->transition(
                    payment: $payment,
                    nextStatus: $mappedStatus,
                    reason: 'Stripe webhook status update',
                    metadata: ['stripe_event_type' => $event->type],
                    providerEventId: $event->id,
                );

                if ($mappedStatus === PaymentStatus::Failed) {
                    $payment->forceFill(['failed_at' => now()])->save();
                }
            }
        }

        return response()->json(['message' => 'Webhook received.']);
    }
}
