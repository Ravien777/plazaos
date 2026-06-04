<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MeetingReminder extends Notification
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
            'title' => 'Meeting Reminder',
            'message' => "Reminder: \"{$this->meeting->title}\" starts at {$this->meeting->start_time->format('g:i A')}.",
            'link' => $this->meeting->meetable ? "/{$this->meeting->meetable->getMorphClass()}/{$this->meeting->meetable->getKey()}" : '/meetings/upcoming',
            'icon' => 'meeting',
        ];
    }
}
