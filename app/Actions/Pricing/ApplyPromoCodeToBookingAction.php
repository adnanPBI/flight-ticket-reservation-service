<?php

namespace App\Actions\Pricing;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\PromoCode;
use App\Services\Pricing\PriceCalculationService;
use App\Services\Pricing\PromoCodeService;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ApplyPromoCodeToBookingAction
{
    public function __construct(
        private readonly PromoCodeService $promoCodeService,
        private readonly PriceCalculationService $priceCalculationService,
    ) {}

    public function execute(Booking $booking, string $code, ?int $actorUserId = null): Booking
    {
        $promoCode = $this->promoCodeService->findUsable($code, $booking->currency);

        return DB::transaction(function () use ($booking, $promoCode, $actorUserId): Booking {
            $booking = Booking::query()
                ->lockForUpdate()
                ->with(['payments', 'priceBreakdowns', 'offer.search'])
                ->findOrFail($booking->id);

            if (! in_array($booking->status, [BookingStatus::PassengerDetailsAdded, BookingStatus::PaymentPending], true)) {
                throw new RuntimeException('Promo codes can only be applied before payment succeeds.');
            }

            if ($booking->payments()->where('status', PaymentStatus::Succeeded)->exists()) {
                throw new RuntimeException('Promo codes cannot be applied after payment succeeds.');
            }

            if ($booking->offer_expires_at && $booking->offer_expires_at->isPast()) {
                throw new RuntimeException('This fare offer has expired. Please search again before applying promo code.');
            }

            $promoCode = PromoCode::query()->lockForUpdate()->findOrFail($promoCode->id);

            if (! $promoCode->isCurrentlyActive()) {
                throw new RuntimeException('Promo code is no longer active.');
            }

            if ($booking->applied_promo_code_id !== $promoCode->id) {
                if ($promoCode->usage_limit && $promoCode->used_count >= $promoCode->usage_limit) {
                    throw new RuntimeException('Promo code usage limit reached.');
                }

                $promoCode->increment('used_count');
            }

            $quote = $this->priceCalculationService->quoteForBooking($booking, $promoCode);

            $before = [
                'total_amount_minor' => $booking->total_amount_minor,
                'discount_amount_minor' => $booking->discount_amount_minor,
                'applied_promo_code' => $booking->applied_promo_code,
            ];

            $booking->forceFill($quote->toBookingAttributes())->save();

            $booking->priceBreakdowns()->delete();
            foreach ($quote->breakdown as $item) {
                $booking->priceBreakdowns()->create([
                    'label' => $item['label'],
                    'type' => $item['type'],
                    'currency' => $booking->currency,
                    'amount_minor' => (int) $item['amount_minor'],
                    'metadata' => [
                        'source' => 'promo_reprice',
                        'promo_code_id' => $promoCode->id,
                    ],
                ]);
            }

            $booking->payments()
                ->whereIn('status', [
                    PaymentStatus::Created->value,
                    PaymentStatus::RequiresPaymentMethod->value,
                    PaymentStatus::RequiresAction->value,
                    PaymentStatus::Processing->value,
                ])
                ->update([
                    'status' => PaymentStatus::Cancelled->value,
                    'metadata' => DB::raw("JSON_SET(COALESCE(metadata, JSON_OBJECT()), '$.cancelled_reason', 'repriced_after_promo')"),
                    'updated_at' => now(),
                ]);

            $booking->events()->create([
                'from_status' => $booking->status->value,
                'to_status' => $booking->status->value,
                'actor_user_id' => $actorUserId,
                'reason' => 'Promo code applied and booking repriced.',
                'metadata' => [
                    'before' => $before,
                    'after' => [
                        'total_amount_minor' => $quote->totalAmountMinor,
                        'discount_amount_minor' => $quote->discountAmountMinor,
                        'applied_promo_code' => $promoCode->code,
                    ],
                ],
            ]);

            app(AuditLogger::class)->record(
                action: 'booking.promo_applied',
                auditableType: $booking::class,
                auditableId: $booking->id,
                actorUserId: $actorUserId,
                oldValues: $before,
                newValues: [
                    'total_amount_minor' => $quote->totalAmountMinor,
                    'discount_amount_minor' => $quote->discountAmountMinor,
                    'applied_promo_code' => $promoCode->code,
                ],
                metadata: ['quote' => $quote->toSnapshot()],
            );

            return $booking->refresh()->load(['priceBreakdowns', 'payments']);
        });
    }
}
