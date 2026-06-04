<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\SlackMessage;

class MeetingScheduled extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Meeting $meeting
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->slackEnabled() && config('services.slack.notifications.bot_user_oauth_token')) {
            $channels[] = 'slack';
        }

        return $channels;
    }

    public function toSlack(object $notifiable): SlackMessage
    {
        $link = $this->meeting->meetable
            ? url("/{$this->meeting->meetable->getMorphClass()}/{$this->meeting->meetable->getKey()}")
            : url('/meetings/upcoming');
        $title = $this->meeting->title;
        $date = $this->meeting->start_time->format('M j, Y g:i A');
        $provider = $this->meeting->provider ?? '—';

        return (new SlackMessage)
            ->text(":calendar: *Meeting Scheduled*")
            ->sectionBlock(function ($section) use ($title, $date, $provider) {
                $section->field("*Title*\n{$title}");
                $section->field("*Date*\n{$date}");
                $section->field("*Provider*\n{$provider}");
            })
            ->actionsBlock(fn ($actions) => $actions
                ->button('View Meeting')
                ->url($link)
            );
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
