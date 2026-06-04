<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Client;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeadConverted extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Lead $lead,
        public readonly Client $client
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Lead Converted',
            'message' => "Lead {$this->lead->company_name} was converted to client.",
            'link' => "/clients/{$this->client->id}",
            'icon' => 'conversion',
        ];
    }
}
