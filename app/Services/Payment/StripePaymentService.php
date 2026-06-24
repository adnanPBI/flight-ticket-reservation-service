<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Str;
use RuntimeException;
use Stripe\StripeClient;

class StripePaymentService
{
    public function createPaymentIntent(Booking $booking, Payment $payment): array
    {
        if (config('stripe.mock_mode')) {
            return $this->createMockPaymentIntent($booking, $payment);
        }

        $secret = config('stripe.secret');

        if (! is_string($secret) || $secret === '' || str_starts_with($secret, 'sk_test_mock')) {
            throw new RuntimeException('Stripe secret key is missing. Enable STRIPE_MOCK_MODE=true or configure real Stripe test keys.');
        }

        $client = new StripeClient($secret);

        $intent = $client->paymentIntents->create([
            'amount' => $booking->total_amount_minor,
            'currency' => strtolower($booking->currency),
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'booking_id' => (string) $booking->id,
                'booking_reference' => $booking->booking_reference,
                'payment_id' => (string) $payment->id,
                'phase' => '6_payment_intent_only',
            ],
        ], [
            'idempotency_key' => "booking:{$booking->booking_reference}:payment:{$payment->id}",
        ]);

        return [
            'provider_payment_id' => $intent->id,
            'client_secret' => $intent->client_secret,
            'status' => $this->mapStripeStatus((string) $intent->status),
            'mock_mode' => false,
            'publishable_key' => config('stripe.key'),
        ];
    }

    public function retrievePaymentIntent(string $providerPaymentId): array
    {
        if (config('stripe.mock_mode')) {
            return [
                'provider_payment_id' => $providerPaymentId,
                'client_secret' => $providerPaymentId.'_secret_mock_safe_checkout',
                'status' => PaymentStatus::RequiresPaymentMethod,
                'mock_mode' => true,
                'publishable_key' => 'pk_test_mock',
            ];
        }

        $client = new StripeClient(config('stripe.secret'));
        $intent = $client->paymentIntents->retrieve($providerPaymentId);

        return [
            'provider_payment_id' => $intent->id,
            'client_secret' => $intent->client_secret,
            'status' => $this->mapStripeStatus((string) $intent->status),
            'mock_mode' => false,
            'publishable_key' => config('stripe.key'),
        ];
    }

    public function mapStripeStatus(string $stripeStatus): PaymentStatus
    {
        return match ($stripeStatus) {
            'requires_payment_method' => PaymentStatus::RequiresPaymentMethod,
            'requires_action' => PaymentStatus::RequiresAction,
            'processing' => PaymentStatus::Processing,
            'succeeded' => PaymentStatus::Succeeded,
            'canceled' => PaymentStatus::Cancelled,
            default => PaymentStatus::Processing,
        };
    }

    private function createMockPaymentIntent(Booking $booking, Payment $payment): array
    {
        $providerPaymentId = $payment->provider_payment_id ?: 'pi_mock_'.Str::lower((string) Str::ulid());
        $clientSecret = $providerPaymentId.'_secret_mock_safe_checkout';

        return [
            'provider_payment_id' => $providerPaymentId,
            'client_secret' => $clientSecret,
            'status' => PaymentStatus::RequiresPaymentMethod,
            'mock_mode' => true,
            'publishable_key' => 'pk_test_mock',
            'safe_note' => 'Mock mode is enabled. No Stripe API call was made and no card can be charged.',
        ];
    }
}
