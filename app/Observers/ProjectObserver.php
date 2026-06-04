<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Services\AutomationService;

class ProjectObserver
{
    public function __construct(
        private readonly AutomationService $automationService
    ) {}

    public function updated(Project $project): void
    {
        if ($project->isDirty('status') && $project->status === ProjectStatus::Completed) {
            $this->automationService->onProjectCompleted($project);
        }
    }
}
