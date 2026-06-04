<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Webhook;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DispatchWebhookJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Webhook $webhook,
        private readonly string $event,
        private readonly Model $subject,
    ) {}

    public function handle(): void
    {
        $payload = $this->buildPayload();

        $json = json_encode($payload);

        if ($json === false) {
            $this->webhook->update([
                'last_error_at' => now(),
                'last_error_message' => 'Failed to encode payload as JSON.',
            ]);

            return;
        }

        $signature = hash_hmac('sha256', $json, $this->webhook->secret);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Webhook-Signature' => $signature,
                'X-Webhook-Event' => $this->event,
            ])->withBody($json, 'application/json')
                ->timeout(10)
                ->post($this->webhook->url);

            if ($response->successful()) {
                $this->webhook->update([
                    'last_sent_at' => now(),
                    'last_error_at' => null,
                    'last_error_message' => null,
                ]);
            } else {
                $this->webhook->update([
                    'last_error_at' => now(),
                    'last_error_message' => "HTTP {$response->status()}: " . Str::limit($response->body(), 200),
                ]);
            }
        } catch (\Exception $e) {
            $this->webhook->update([
                'last_error_at' => now(),
                'last_error_message' => Str::limit($e->getMessage(), 200),
            ]);
        }
    }

    private function buildPayload(): array
    {
        $attributes = $this->subject->toArray();

        unset($attributes['password'], $attributes['remember_token'], $attributes['deleted_at']);

        return [
            'event' => $this->event === 'webhook.test' ? 'webhook.test' : $this->event,
            'data' => [
                'id' => $this->subject->getKey(),
                'type' => $this->subject->getMorphClass(),
                'attributes' => $attributes,
            ],
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
