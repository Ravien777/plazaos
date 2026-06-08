<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Client;
use App\Models\Comment;
use App\Models\Lead;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserMentioned extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Comment $comment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'You were mentioned',
            'message' => "{$this->comment->user->name} mentioned you in a comment on {$this->getResourceName()}.",
            'link' => $this->getLink(),
            'icon' => 'mention',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You were mentioned on PlazaOS')
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->comment->user->name} mentioned you in a comment on {$this->getResourceName()}.")
            ->line("\"{$this->comment->body}\"")
            ->action('View Comment', url($this->getLink()));
    }

    private function getResourceName(): string
    {
        $commentable = $this->comment->commentable;

        if ($commentable instanceof Lead) {
            return "lead {$commentable->company_name}";
        }

        if ($commentable instanceof Client) {
            return "client {$commentable->company_name}";
        }

        if ($commentable instanceof Project) {
            return "project {$commentable->name}";
        }

        return 'a record';
    }

    private function getLink(): string
    {
        $commentable = $this->comment->commentable;

        if ($commentable instanceof Lead) {
            return "/leads/{$commentable->id}";
        }

        if ($commentable instanceof Client) {
            return "/clients/{$commentable->id}";
        }

        if ($commentable instanceof Project) {
            return "/projects/{$commentable->id}";
        }

        return '#';
    }
}
