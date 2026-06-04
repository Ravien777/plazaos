<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Meeting;
use App\Services\AutomationService;

class MeetingObserver
{
    public function __construct(
        private readonly AutomationService $automationService
    ) {}

    public function created(Meeting $meeting): void
    {
        $this->automationService->onMeetingScheduled($meeting);
    }
}
