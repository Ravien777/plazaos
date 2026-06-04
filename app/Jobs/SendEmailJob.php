<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        private readonly string $emailableType,
        private readonly string $emailableId,
        private readonly string $subject,
        private readonly string $body,
        private readonly ?int $userId = null,
    ) {}

    public function handle(EmailService $emailService): void
    {
        $model = $this->resolveEmailable();

        if (!$model) {
            return;
        }

        $user = $this->userId ? User::find($this->userId) : null;

        $emailService->sendCustom($model, $this->subject, $this->body, 'custom', $user);
    }

    private function resolveEmailable(): ?Model
    {
        $class = match ($this->emailableType) {
            'lead' => \App\Models\Lead::class,
            'client' => \App\Models\Client::class,
            'project' => \App\Models\Project::class,
            default => null,
        };

        if (!$class) {
            return null;
        }

        return $class::find($this->emailableId);
    }
}
