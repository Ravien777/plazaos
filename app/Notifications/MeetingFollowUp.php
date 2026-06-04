<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MeetingFollowUp extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Meeting $meeting
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Follow-up After Meeting',
            'message' => "Follow up on \"{$this->meeting->title}\" from {$this->meeting->start_time->format('M j, Y')}.",
            'link' => "/meetings/{$this->meeting->id}",
            'icon' => 'meeting',
        ];
    }
}
