<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Meeting;

interface MeetingProviderInterface
{
    public function createEvent(Meeting $meeting): ?string;

    public function updateEvent(Meeting $meeting): void;

    public function deleteEvent(Meeting $meeting): void;

    public function isEnabled(): bool;
}
