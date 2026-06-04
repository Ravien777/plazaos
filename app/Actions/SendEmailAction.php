<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Email;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Database\Eloquent\Model;

class SendEmailAction
{
    public function __construct(
        private readonly EmailService $emailService
    ) {}

    public function execute(Model $emailable, string $subject, string $body, string $template = 'custom', ?User $user = null): Email
    {
        return $this->emailService->sendCustom($emailable, $subject, $body, $template, $user);
    }
}
