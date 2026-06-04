<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MeetingScheduled extends Notification
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
            'title' => 'Meeting Scheduled',
            'message' => "Meeting \"{$this->meeting->title}\" was scheduled.",
            'link' => $this->meeting->meetable ? "/{$this->meeting->meetable->getMorphClass()}/{$this->meeting->meetable->getKey()}" : '/meetings/upcoming',
            'icon' => 'meeting',
        ];
    }
}
