<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Lead;
use App\Models\User;
use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebhookServiceTest extends TestCase
{
    use RefreshDatabase;

    private WebhookService $webhookService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->webhookService = app(WebhookService::class);
    }

    public function test_create_generates_secret(): void
    {
        $webhook = $this->webhookService->create([
            'url' => 'https://example.com/hook',
            'events' => ['lead.created'],
        ]);

        $this->assertNotNull($webhook->id);
        $this->assertNotNull($webhook->secret);
        $this->assertEquals(32, strlen($webhook->secret));
        $this->assertTrue($webhook->active);
    }

    public function test_dispatch_queues_job_for_matching_webhook(): void
    {
        Queue::fake();

        Webhook::factory()->create([
            'user_id' => $this->user->id,
            'events' => ['lead.created', 'ticket.created'],
            'active' => true,
        ]);

        $lead = Lead::factory()->create();
        $this->webhookService->dispatch('lead.created', $lead);

        Queue::assertPushed(\App\Jobs\DispatchWebhookJob::class);
    }

    public function test_dispatch_skips_inactive_webhooks(): void
    {
        Queue::fake();

        Webhook::factory()->create([
            'user_id' => $this->user->id,
            'events' => ['lead.created'],
            'active' => false,
        ]);

        $lead = Lead::factory()->create();
        $this->webhookService->dispatch('lead.created', $lead);

        Queue::assertNothingPushed();
    }

    public function test_dispatch_skips_unsubscribed_events(): void
    {
        Queue::fake();

        Webhook::factory()->create([
            'user_id' => $this->user->id,
            'events' => ['client.created'],
            'active' => true,
        ]);

        $lead = Lead::factory()->create();
        $this->webhookService->dispatch('lead.created', $lead);

        Queue::assertNothingPushed();
    }
}
