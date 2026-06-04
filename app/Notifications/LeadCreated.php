<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeadCreated extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Lead $lead
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New Lead Created',
            'message' => "Lead {$this->lead->company_name} was created.",
            'link' => "/leads/{$this->lead->id}",
            'icon' => 'lead',
        ];
    }
}
