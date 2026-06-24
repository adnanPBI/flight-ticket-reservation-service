<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Created = 'created';
    case RequiresPaymentMethod = 'requires_payment_method';
    case RequiresAction = 'requires_action';
    case Processing = 'processing';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially_refunded';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title()->toString();
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, self::allowedTransitions()[$this->value] ?? [], true);
    }

    /** @return array<string, list<self>> */
    public static function allowedTransitions(): array
    {
        return [
            self::Created->value => [self::RequiresPaymentMethod, self::RequiresAction, self::Processing, self::Succeeded, self::Failed, self::Cancelled],
            self::RequiresPaymentMethod->value => [self::RequiresAction, self::Processing, self::Succeeded, self::Failed, self::Cancelled],
            self::RequiresAction->value => [self::Processing, self::Succeeded, self::Failed, self::Cancelled],
            self::Processing->value => [self::Succeeded, self::Failed, self::Cancelled],
            self::Succeeded->value => [self::Refunded, self::PartiallyRefunded],
            self::PartiallyRefunded->value => [self::Refunded],
            self::Failed->value => [],
            self::Cancelled->value => [],
            self::Refunded->value => [],
        ];
    }
}
