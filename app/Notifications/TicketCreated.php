<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\SlackMessage;

class TicketCreated extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Ticket $ticket
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (config('services.slack.notifications.bot_user_oauth_token')) {
            $channels[] = 'slack';
        }

        return $channels;
    }

    public function toSlack(object $notifiable): SlackMessage
    {
        $status = ucfirst($this->ticket->status->value ?? $this->ticket->status);
        $priority = ucfirst($this->ticket->priority->value ?? $this->ticket->priority);
        $from = $this->ticket->ticketable?->company_name ?? '—';

        return (new SlackMessage)
            ->text(":ticket: *New Ticket Created*")
            ->sectionBlock(function ($section) use ($status, $priority, $from) {
                $section->field("*Subject*\n{$this->ticket->subject}");
                $section->field("*Status*\n{$status}");
                $section->field("*Priority*\n{$priority}");
                $section->field("*From*\n{$from}");
            })
            ->actionsBlock(fn ($actions) => $actions
                ->button('View Ticket')
                ->url(url("/tickets/{$this->ticket->id}"))
            );
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Ticket Created',
            'message' => "Ticket \"{$this->ticket->subject}\" was created.",
            'link' => "/tickets/{$this->ticket->id}",
            'icon' => 'ticket',
        ];
    }
}
