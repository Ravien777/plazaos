<?php

declare(strict_types=1);

namespace App\Enums;

enum PlanSlug: string
{
    case Free = 'free';
    case Pro = 'pro';
    case Team = 'team';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Free',
            self::Pro => 'Pro',
            self::Team => 'Team',
        };
    }
}
