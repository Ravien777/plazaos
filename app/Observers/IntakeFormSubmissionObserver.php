<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\IntakeFormSubmission;
use App\Services\AutomationService;

class IntakeFormSubmissionObserver
{
    public function __construct(
        private readonly AutomationService $automationService
    ) {}

    public function created(IntakeFormSubmission $submission): void
    {
        $this->automationService->onIntakeSubmitted($submission);
    }
}
