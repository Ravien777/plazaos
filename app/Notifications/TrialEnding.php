<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TrialEnding extends Notification
{
    use Queueable;

    public function __construct(
        public readonly int $daysRemaining
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Trial Ending Soon',
            'message' => "Your trial ends in {$this->daysRemaining} days. Upgrade to keep using all features.",
            'link' => '/settings/billing',
            'icon' => 'billing',
        ];
    }
}
