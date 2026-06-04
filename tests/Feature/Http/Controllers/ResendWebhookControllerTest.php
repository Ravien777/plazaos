<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Email;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResendWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->actingAs($user);

        config(['mail.from.address' => 'noreply@plazaos.test']);
    }

    public function test_handles_email_opened_event(): void
    {
        $lead = Lead::factory()->create(['email' => 'opened@example.com']);
        $email = Email::factory()->create([
            'emailable_type' => $lead->getMorphClass(),
            'emailable_id' => $lead->id,
            'to' => $lead->email,
            'subject' => 'Test',
            'external_id' => 'resend_email_123',
        ]);

        $this->assertNull($email->opened_at);

        $response = $this->call('POST', '/webhooks/resend', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'type' => 'email.opened',
            'data' => ['email_id' => 'resend_email_123'],
        ]));

        $response->assertOk();
        $response->assertJson(['status' => 'ok']);

        $this->assertNotNull($email->fresh()->opened_at);
    }

    public function test_ignores_non_opened_events(): void
    {
        $response = $this->call('POST', '/webhooks/resend', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'type' => 'email.delivered',
            'data' => [],
        ]));

        $response->assertOk();
        $response->assertJson(['status' => 'ignored']);
    }

    public function test_returns_404_for_unknown_email(): void
    {
        $response = $this->call('POST', '/webhooks/resend', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'type' => 'email.opened',
            'data' => ['email_id' => 'nonexistent-id'],
        ]));

        $response->assertNotFound();
        $response->assertJson(['error' => 'Email not found.']);
    }

    public function test_returns_400_for_missing_email_id(): void
    {
        $response = $this->call('POST', '/webhooks/resend', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'type' => 'email.opened',
            'data' => [],
        ]));

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Missing email_id.']);
    }
}
