<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\TeamInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInvitation extends Notification
{
    use Queueable;

    public function __construct(
        public TeamInvitation $invitation,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('invitation.show', $this->invitation->token);

        return (new MailMessage)
            ->subject('You\'ve been invited to join ' . $this->invitation->team->name)
            ->greeting('Hello!')
            ->line('You have been invited to join the team **' . $this->invitation->team->name . '** on PlazaOS.')
            ->line('This invitation expires in 48 hours.')
            ->action('Accept Invitation', $url)
            ->line('If you did not expect this invitation, you can ignore this email.');
    }
}
