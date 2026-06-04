<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TestimonialRequest extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Project $project
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Request Testimonial',
            'message' => "Project {$this->project->name} completed. Request a testimonial from {$this->project->client->company_name}.",
            'link' => "/projects/{$this->project->id}",
            'icon' => 'testimonial',
        ];
    }
}
