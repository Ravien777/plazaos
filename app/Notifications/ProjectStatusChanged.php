<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Project $project,
        public readonly string $oldStatus,
        public readonly string $newStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Project Status Changed',
            'message' => "Project \"{$this->project->name}\" changed from {$this->oldStatus} to {$this->newStatus}.",
            'link' => "/projects/{$this->project->id}",
            'icon' => 'project',
        ];
    }
}
