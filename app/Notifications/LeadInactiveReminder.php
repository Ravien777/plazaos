<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeadInactiveReminder extends Notification
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
            'title' => 'Inactive Lead Reminder',
            'message' => "Lead {$this->lead->company_name} hasn't been contacted in 7 days.",
            'link' => "/leads/{$this->lead->id}",
            'icon' => 'reminder',
        ];
    }
}
