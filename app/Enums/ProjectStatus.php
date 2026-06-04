<?php

declare(strict_types=1);

namespace App\Enums;

enum ProjectStatus: string
{
    case Discovery = 'discovery';
    case Design = 'design';
    case Development = 'development';
    case Testing = 'testing';
    case Launch = 'launch';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Discovery => 'Discovery',
            self::Design => 'Design',
            self::Development => 'Development',
            self::Testing => 'Testing',
            self::Launch => 'Launch',
            self::Completed => 'Completed',
        };
    }
}
