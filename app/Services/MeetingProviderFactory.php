<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\MeetingProviderInterface;

class MeetingProviderFactory
{
    public function __construct(
        private readonly GoogleCalendarService $google,
        private readonly ZoomMeetingProvider $zoom,
        private readonly TeamsMeetingProvider $teams,
    ) {}

    public function make(string $provider): MeetingProviderInterface
    {
        return match ($provider) {
            'google_meet' => $this->google,
            'zoom' => $this->zoom,
            'microsoft_teams' => $this->teams,
            default => throw new \InvalidArgumentException("Unknown meeting provider: {$provider}"),
        };
    }
}
