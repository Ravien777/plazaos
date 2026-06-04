<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Client;
use App\Models\IntakeForm;
use App\Models\IntakeFormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\SlackMessage;

class FormSubmissionReceived extends Notification
{
    use Queueable;

    public function __construct(
        private readonly IntakeForm $form,
        private readonly Client $client,
        private readonly IntakeFormSubmission $submission,
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
        $formTitle = $this->form->title;
        $clientName = $this->client->company_name;

        return (new SlackMessage)
            ->text(":clipboard: *Form Submission Received*")
            ->sectionBlock(function ($section) use ($formTitle, $clientName) {
                $section->field("*Form*\n{$formTitle}");
                $section->field("*Client*\n{$clientName}");
            })
            ->actionsBlock(fn ($actions) => $actions
                ->button('View Submission')
                ->url(url("/intake-forms/{$this->form->id}"))
            );
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Form Submission Received',
            'message' => "{$this->client->company_name} submitted \"{$this->form->title}\".",
            'link' => "/intake-forms/{$this->form->id}",
            'icon' => 'clipboard-document-check',
        ];
    }
}
