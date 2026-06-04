<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\SlackMessage;

class LeadInactiveReminder extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Lead $lead
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
        $company = $this->lead->company_name ?? '—';
        $contact = $this->lead->contact_name ?? '—';
        $email = $this->lead->email ?? '—';
        $lastContacted = $this->lead->last_contacted_at?->diffForHumans() ?? 'Never';

        return (new SlackMessage)
            ->text(":warning: *Inactive Lead Reminder*")
            ->sectionBlock(function ($section) use ($company, $contact, $email, $lastContacted) {
                $section->field("*Company*\n{$company}");
                $section->field("*Contact*\n{$contact}");
                $section->field("*Email*\n{$email}");
                $section->field("*Last Contacted*\n{$lastContacted}");
            })
            ->actionsBlock(fn ($actions) => $actions
                ->button('View Lead')
                ->url(url("/leads/{$this->lead->id}"))
            );
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Inactive Lead Reminder',
            'message' => "Lead {$this->lead->company_name} hasn't been contacted in 7 days.",
            'link' => "/leads/{$this->lead->id}",
            'icon' => 'reminder',
        ];
    }
}
