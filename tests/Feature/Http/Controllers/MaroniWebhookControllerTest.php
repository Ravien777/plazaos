<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Services\MaroniService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class MaroniWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();
    }

    public function test_handle_invoice_created_event(): void
    {
        config(['maroni.webhook_secret' => 'test-secret']);

        $client = Client::factory()->create(['maroni_client_id' => 'ext_123']);

        $payload = '{"event":"invoice.created","data":{"client_id":"ext_123","invoice_id":"inv_1"}}';
        $signature = hash_hmac('sha256', $payload, 'test-secret');

        $response = $this->postJson('/api/maroni/webhook', json_decode($payload, true), [
            'X-Webhook-Signature' => $signature,
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'ok']);
    }

    public function test_handle_client_updated_event(): void
    {
        config(['maroni.webhook_secret' => 'test-secret']);

        Client::factory()->create(['maroni_client_id' => 'ext_456']);

        $maroniService = Mockery::mock(MaroniService::class);
        $maroniService->shouldReceive('syncClient')->once();
        $this->app->instance(MaroniService::class, $maroniService);

        $payload = '{"event":"client.updated","data":{"client_id":"ext_456"}}';
        $signature = hash_hmac('sha256', $payload, 'test-secret');

        $response = $this->postJson('/api/maroni/webhook', json_decode($payload, true), [
            'X-Webhook-Signature' => $signature,
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'ok']);
    }

    public function test_handle_unknown_event(): void
    {
        config(['maroni.webhook_secret' => 'test-secret']);

        Client::factory()->create(['maroni_client_id' => 'ext_789']);

        $payload = '{"event":"some.random.event","data":{"client_id":"ext_789"}}';
        $signature = hash_hmac('sha256', $payload, 'test-secret');

        $response = $this->postJson('/api/maroni/webhook', json_decode($payload, true), [
            'X-Webhook-Signature' => $signature,
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'ok']);
    }

    public function test_handle_invalid_signature(): void
    {
        config(['maroni.webhook_secret' => 'test-secret']);

        $payload = '{"event":"invoice.created","data":{"client_id":"ext_123"}}';

        $response = $this->postJson('/api/maroni/webhook', json_decode($payload, true), [
            'X-Webhook-Signature' => 'bad-signature',
        ]);

        $response->assertUnauthorized();
        $response->assertJson(['error' => 'Invalid signature.']);
    }

    public function test_handle_missing_signature(): void
    {
        config(['maroni.webhook_secret' => 'test-secret']);

        $payload = '{"event":"invoice.created","data":{"client_id":"ext_123"}}';

        $response = $this->postJson('/api/maroni/webhook', json_decode($payload, true));

        $response->assertUnauthorized();
        $response->assertJson(['error' => 'Invalid signature.']);
    }

    public function test_handle_missing_client_id(): void
    {
        config(['maroni.webhook_secret' => 'test-secret']);

        $payload = '{"event":"invoice.created","data":[]}';
        $signature = hash_hmac('sha256', $payload, 'test-secret');

        $response = $this->postJson('/api/maroni/webhook', json_decode($payload, true), [
            'X-Webhook-Signature' => $signature,
        ]);

        $response->assertBadRequest();
        $response->assertJson(['error' => 'Missing client_id.']);
    }

    public function test_handle_client_not_found(): void
    {
        config(['maroni.webhook_secret' => 'test-secret']);

        $payload = '{"event":"invoice.created","data":{"client_id":"nonexistent"}}';
        $signature = hash_hmac('sha256', $payload, 'test-secret');

        $response = $this->postJson('/api/maroni/webhook', json_decode($payload, true), [
            'X-Webhook-Signature' => $signature,
        ]);

        $response->assertNotFound();
        $response->assertJson(['error' => 'Client not found.']);
    }

    public function test_handle_no_secret_configured(): void
    {
        config(['maroni.webhook_secret' => null]);

        $payload = '{"event":"invoice.created","data":{"client_id":"ext_123"}}';

        $response = $this->postJson('/api/maroni/webhook', json_decode($payload, true));

        $response->assertStatus(500);
        $response->assertJson(['error' => 'Webhook not configured.']);
    }
}
