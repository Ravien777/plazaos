<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Client;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OnboardingChecklist extends Notification
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
            'title' => 'New Client — Onboarding',
            'message' => "{$this->client->company_name} was converted. Create onboarding tasks.",
            'link' => "/clients/{$this->client->id}",
            'icon' => 'conversion',
        ];
    }
}
