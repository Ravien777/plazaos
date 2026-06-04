<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\SlackMessage;

class LeadCreated extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Lead $lead
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
        $company = $this->lead->company_name ?? '—';
        $contact = $this->lead->contact_name ?? '—';
        $email = $this->lead->email ?? '—';

        return (new SlackMessage)
            ->text(":new: *New Lead Created*")
            ->sectionBlock(function ($section) use ($company, $contact, $email) {
                $section->field("*Company*\n{$company}");
                $section->field("*Contact*\n{$contact}");
                $section->field("*Email*\n{$email}");
            })
            ->actionsBlock(fn ($actions) => $actions
                ->button('View Lead')
                ->url(url("/leads/{$this->lead->id}"))
            );
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New Lead Created',
            'message' => "Lead {$this->lead->company_name} was created.",
            'link' => "/leads/{$this->lead->id}",
            'icon' => 'lead',
        ];
    }
}
