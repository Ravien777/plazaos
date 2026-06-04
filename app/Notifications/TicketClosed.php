<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketClosed extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Ticket $ticket
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Ticket Closed',
            'message' => "Ticket \"{$this->ticket->subject}\" was closed.",
            'link' => "/tickets/{$this->ticket->id}",
            'icon' => 'ticket',
        ];
    }
}
