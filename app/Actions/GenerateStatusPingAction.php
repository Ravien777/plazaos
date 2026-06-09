<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Project;
use App\Enums\TaskStatus;

class GenerateStatusPingAction
{
    public function execute(Project $project, ?string $personalNote = null): array
    {
        $since = now()->subDays(7);

        $completed = $project->tasks()
            ->where('status', TaskStatus::Done)
            ->where('updated_at', '>=', $since)
            ->pluck('title');

        $inProgress = $project->tasks()
            ->whereNot('status', TaskStatus::Done)
            ->pluck('title');

        $recentActivity = $project->activities()
            ->where('created_at', '>=', $since)
            ->latest()
            ->take(10)
            ->pluck('description');

        return [
            'project_name' => $project->name,
            'client_name' => $project->client?->contact_name ?? 'Client',
            'client_email' => $project->client?->email ?? '',
            'completed' => $completed,
            'in_progress' => $inProgress,
            'recent_activity' => $recentActivity,
            'personal_note' => $personalNote,
            'generated_at' => now()->toDateTimeString(),
        ];
    }
}
