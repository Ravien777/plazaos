<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\DispatchWebhookJob;
use App\Models\Webhook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WebhookService
{
    public function list(): mixed
    {
        return Webhook::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function create(array $data): Webhook
    {
        return Webhook::create([
            'user_id' => auth()->id(),
            'url' => $data['url'],
            'events' => $data['events'],
            'secret' => Str::random(32),
            'active' => true,
        ]);
    }

    public function update(Webhook $webhook, array $data): Webhook
    {
        $webhook->update($data);

        return $webhook;
    }

    public function delete(Webhook $webhook): void
    {
        $webhook->delete();
    }

    public function regenerateSecret(Webhook $webhook): string
    {
        $secret = Str::random(32);
        $webhook->update(['secret' => $secret]);

        return $secret;
    }

    public function dispatch(string $event, Model $subject): void
    {
        $webhooks = Webhook::where('active', true)
            ->whereJsonContains('events', $event)
            ->get();

        foreach ($webhooks as $webhook) {
            DispatchWebhookJob::dispatch($webhook, $event, $subject);
        }
    }
}
