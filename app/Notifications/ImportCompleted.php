<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\LeadImport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\SlackMessage;

class ImportCompleted extends Notification
{
    use Queueable;

    public function __construct(
        public readonly LeadImport $import
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
        $filename = $this->import->filename;
        $processed = (string) $this->import->processed;
        $failed = (string) $this->import->failed;

        return (new SlackMessage)
            ->text(":file_cabinet: *CSV Import Completed*")
            ->sectionBlock(function ($section) use ($filename, $processed, $failed) {
                $section->field("*File*\n{$filename}");
                $section->field("*Processed*\n{$processed}");
                $section->field("*Failed*\n{$failed}");
            })
            ->actionsBlock(fn ($actions) => $actions
                ->button('View Import')
                ->url(url("/leads/import/{$this->import->id}"))
            );
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Import Completed',
            'message' => "Import \"{$this->import->filename}\" completed. {$this->import->processed} processed, {$this->import->failed} failed.",
            'link' => "/leads/import/{$this->import->id}",
            'icon' => 'import',
        ];
    }
}
