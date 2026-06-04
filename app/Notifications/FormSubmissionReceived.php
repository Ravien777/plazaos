<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Client;
use App\Models\IntakeForm;
use App\Models\IntakeFormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

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
        return ['database'];
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
