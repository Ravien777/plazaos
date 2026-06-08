<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\DispatchWebhookJob;
use App\Models\Lead;
use App\Models\User;
use App\Models\Webhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DispatchWebhookJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_dispatches_to_url_with_signature(): void
    {
        $webhook = Webhook::factory()->create([
            'user_id' => $this->user->id,
            'url' => 'https://example.com/webhook',
            'secret' => 'test-secret',
        ]);

        $lead = Lead::factory()->create();

        Http::fake([
            'https://example.com/webhook' => Http::response(['ok' => true], 200),
        ]);

        (new DispatchWebhookJob($webhook, 'lead.created', $lead))->handle();

        Http::assertSent(function ($request) use ($webhook) {
            $body = $request->body();
            $expectedSig = hash_hmac('sha256', $body, 'test-secret');

            return $request->url() === 'https://example.com/webhook'
                && $request->hasHeader('X-Webhook-Signature')
                && $request->header('X-Webhook-Signature')[0] === $expectedSig
                && $request->header('X-Webhook-Event')[0] === 'lead.created';
        });

        $this->assertNotNull($webhook->fresh()->last_sent_at);
    }

    public function test_records_error_on_failure(): void
    {
        $webhook = Webhook::factory()->create([
            'user_id' => $this->user->id,
            'url' => 'https://example.com/webhook',
        ]);

        $lead = Lead::factory()->create();

        Http::fake([
            'https://example.com/webhook' => Http::response('Server Error', 500),
        ]);

        (new DispatchWebhookJob($webhook, 'lead.created', $lead))->handle();

        $this->assertNotNull($webhook->fresh()->last_error_at);
        $this->assertNotNull($webhook->fresh()->last_error_message);
    }

    public function test_records_error_on_connection_failure(): void
    {
        $webhook = Webhook::factory()->create([
            'user_id' => $this->user->id,
            'url' => 'https://example.com/webhook',
        ]);

        $lead = Lead::factory()->create();

        Http::fake([
            'https://example.com/webhook' => function () {
                throw new \Exception('Connection refused');
            },
        ]);

        (new DispatchWebhookJob($webhook, 'lead.created', $lead))->handle();

        $this->assertNotNull($webhook->fresh()->last_error_at);
        $this->assertStringContainsString('Connection refused', $webhook->fresh()->last_error_message);
    }
}
