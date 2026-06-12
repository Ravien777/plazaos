<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Trialing = 'trialing';
    case Active = 'active';
    case PastDue = 'past_due';
    case Canceled = 'canceled';
    case Incomplete = 'incomplete';
    case IncompleteExpired = 'incomplete_expired';

    public function label(): string
    {
        return match ($this) {
            self::Trialing => 'Trial',
            self::Active => 'Active',
            self::PastDue => 'Past Due',
            self::Canceled => 'Canceled',
            self::Incomplete => 'Incomplete',
            self::IncompleteExpired => 'Expired',
        };
    }

    public function isPaid(): bool
    {
        return in_array($this, [self::Active, self::Trialing]);
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Active, self::Trialing, self::PastDue]);
    }
}
