<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Email;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Resend\Laravel\Facades\Resend;

class EmailService
{
    public function getFor(Model $emailable): Collection
    {
        return $emailable->emails()->latest()->get();
    }

    public function sendCustom(Model $emailable, string $subject, string $body, string $template = 'custom', ?User $user = null): Email
    {
        $user = $user ?? Auth::user();

        $from = config('mail.from.address');
        $to = $emailable->email ?? $emailable->contact_email ?? '';

        try {
            $response = Resend::emails()->send([
                'from' => $from,
                'to' => $to,
                'subject' => $subject,
                'html' => nl2br(e($body)),
                'open_tracking' => true,
            ]);

            $email = $emailable->emails()->create([
                'from' => $from,
                'to' => $to,
                'subject' => $subject,
                'body' => $body,
                'template' => $template,
                'status' => 'sent',
                'external_id' => $response->id ?? null,
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            $email = $emailable->emails()->create([
                'from' => $from,
                'to' => $to,
                'subject' => $subject,
                'body' => $body,
                'template' => $template,
                'status' => 'failed',
            ]);
        }

        activity()->log($emailable, 'email.sent', "Email sent to {$to}: {$subject}");

        return $email;
    }

    public function sendFromTemplate(Model $emailable, string $templateKey, array $variables = []): Email
    {
        $templates = config("email-templates.{$templateKey}");

        if (!$templates) {
            throw new \InvalidArgumentException("Template '{$templateKey}' not found.");
        }

        $subject = $this->substitute($templates['subject'], $variables);
        $body = $this->substitute($templates['body'], $variables);

        return $this->sendCustom($emailable, $subject, $body, $templateKey);
    }

    private function substitute(string $text, array $variables): string
    {
        $search = [];
        $replace = [];

        foreach ($variables as $key => $value) {
            $search[] = '{{' . $key . '}}';
            $replace[] = $value;
        }

        return str_replace($search, $replace, $text);
    }
}
