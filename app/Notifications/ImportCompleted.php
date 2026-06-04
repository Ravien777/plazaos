<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\LeadImport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ImportCompleted extends Notification
{
    use Queueable;

    public function __construct(
        public readonly LeadImport $import
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
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
