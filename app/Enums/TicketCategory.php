<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketCategory: string
{
    case BugReport = 'bug_report';
    case FeatureRequest = 'feature_request';
    case Support = 'support';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::BugReport => 'Bug Report',
            self::FeatureRequest => 'Feature Request',
            self::Support => 'Support',
            self::Other => 'Other',
        };
    }
}
