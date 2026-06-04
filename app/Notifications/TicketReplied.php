<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\SlackMessage;

class TicketReplied extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Ticket $ticket
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
        $status = ucfirst($this->ticket->status->value ?? $this->ticket->status);

        return (new SlackMessage)
            ->text(":speech_balloon: *New Reply on Ticket*")
            ->sectionBlock(function ($section) use ($status) {
                $section->field("*Subject*\n{$this->ticket->subject}");
                $section->field("*Status*\n{$status}");
            })
            ->actionsBlock(fn ($actions) => $actions
                ->button('View Ticket')
                ->url(url("/tickets/{$this->ticket->id}"))
            );
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Ticket Reply',
            'message' => "New reply on \"{$this->ticket->subject}\".",
            'link' => "/tickets/{$this->ticket->id}",
            'icon' => 'ticket',
        ];
    }
}
