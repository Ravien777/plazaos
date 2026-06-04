<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeadImported extends Notification
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
            'title' => 'New Lead Imported',
            'message' => "Lead {$this->lead->company_name} was imported. Review their profile.",
            'link' => "/leads/{$this->lead->id}",
            'icon' => 'import',
        ];
    }
}
