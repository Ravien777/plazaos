<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class DocumentUploaded extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $documentName,
        public readonly ?Model $parent,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $parentName = '—';
        $link = '/';

        if ($this->parent instanceof Lead) {
            $parentName = $this->parent->company_name;
            $link = "/leads/{$this->parent->id}";
        } elseif ($this->parent instanceof Client) {
            $parentName = $this->parent->company_name;
            $link = "/clients/{$this->parent->id}";
        } elseif ($this->parent instanceof Project) {
            $parentName = $this->parent->name;
            $link = "/projects/{$this->parent->id}";
        }

        return [
            'title' => 'Document Uploaded',
            'message' => "Document \"{$this->documentName}\" was uploaded to {$parentName}.",
            'link' => $link,
            'icon' => 'document',
        ];
    }
}
