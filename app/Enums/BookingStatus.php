<?php

namespace App\Enums;

enum BookingStatus: string
{
    case SearchCreated = 'search_created';
    case OfferSelected = 'offer_selected';
    case PassengerDetailsAdded = 'passenger_details_added';
    case PaymentPending = 'payment_pending';
    case PaymentSucceeded = 'payment_succeeded';
    case BookingConfirming = 'booking_confirming';
    case BookingConfirmed = 'booking_confirmed';
    case TicketingPending = 'ticketing_pending';
    case Ticketed = 'ticketed';
    case BookingFailed = 'booking_failed';
    case RefundPending = 'refund_pending';
    case Refunded = 'refunded';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title()->toString();
    }

    /**
     * Allowed state transitions for Phase 1.
     * Keep this strict. Add cancellation/change paths later only after provider flows are confirmed.
     */
    public function canTransitionTo(self $next): bool
    {
        return in_array($next, self::allowedTransitions()[$this->value] ?? [], true);
    }

    /** @return array<string, list<self>> */
    public static function allowedTransitions(): array
    {
        return [
            self::SearchCreated->value => [self::OfferSelected, self::Cancelled],
            self::OfferSelected->value => [self::PassengerDetailsAdded, self::Cancelled],
            self::PassengerDetailsAdded->value => [self::PaymentPending, self::Cancelled],
            self::PaymentPending->value => [self::PaymentSucceeded, self::Cancelled, self::BookingFailed],
            self::PaymentSucceeded->value => [self::BookingConfirming, self::RefundPending],
            self::BookingConfirming->value => [self::BookingConfirmed, self::TicketingPending, self::BookingFailed],
            self::BookingConfirmed->value => [self::Ticketed, self::TicketingPending, self::RefundPending, self::Cancelled],
            self::TicketingPending->value => [self::Ticketed, self::BookingFailed, self::RefundPending],
            self::Ticketed->value => [self::RefundPending, self::Cancelled],
            self::BookingFailed->value => [self::RefundPending, self::Cancelled],
            self::RefundPending->value => [self::Refunded],
            self::Refunded->value => [],
            self::Cancelled->value => [],
        ];
    }
}
