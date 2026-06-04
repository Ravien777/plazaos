<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\SlackMessage;

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
        $channels = ['database'];

        if (config('services.slack.notifications.bot_user_oauth_token')) {
            $channels[] = 'slack';
        }

        return $channels;
    }

    public function toSlack(object $notifiable): SlackMessage
    {
        $name = $this->project->name;
        $progress = "{$this->project->progress_percentage}%";

        return (new SlackMessage)
            ->text(":chart_with_upwards_trend: *Project Status Changed*")
            ->sectionBlock(function ($section) use ($name, $progress) {
                $section->field("*Project*\n{$name}");
                $section->field("*Status*\n{$this->oldStatus} → {$this->newStatus}");
                $section->field("*Progress*\n{$progress}");
            })
            ->actionsBlock(fn ($actions) => $actions
                ->button('View Project')
                ->url(url("/projects/{$this->project->id}"))
            );
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
