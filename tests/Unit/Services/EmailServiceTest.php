<?php

namespace Tests\Unit\Services;

use App\Models\Lead;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Resend\Contracts\Client as ResendClient;
use Tests\TestCase;

class EmailServiceTest extends TestCase
{
    use RefreshDatabase;

    private EmailService $emailService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emailService = app(EmailService::class);
        $this->user = User::factory()->create();

        config(['mail.from.address' => 'noreply@plazaos.test']);

        $emailsMock = Mockery::mock();
        $emailsMock->shouldReceive('send')->andReturn((object) ['id' => 'resend-test-id']);

        $clientMock = Mockery::mock(ResendClient::class);
        $clientMock->shouldReceive('emails')->andReturn($emailsMock);

        $this->app->instance(ResendClient::class, $clientMock);
    }

    public function test_send_custom_creates_email_on_success(): void
    {
        $lead = Lead::factory()->create(['email' => 'test@example.com']);

        $email = $this->emailService->sendCustom($lead, 'Hello', 'Body content', 'custom', $this->user);

        $this->assertDatabaseHas('emails', [
            'id' => $email->id,
            'emailable_type' => Lead::class,
            'emailable_id' => $lead->id,
            'subject' => 'Hello',
            'to' => 'test@example.com',
        ]);
        $this->assertDatabaseHas('activities', [
            'event' => 'email.sent',
        ]);
    }

    public function test_send_from_template_substitutes_variables(): void
    {
        $lead = Lead::factory()->create(['email' => 'test@example.com', 'contact_name' => 'John']);
        $email = $this->emailService->sendFromTemplate(
            $lead,
            'introduction',
            ['company_name' => $lead->company_name, 'contact_name' => $lead->contact_name, 'sender_name' => 'Test']
        );

        $this->assertStringContainsString('John', $email->body);
        $this->assertStringContainsString($lead->company_name, $email->body);
    }

    public function test_send_from_template_throws_for_unknown_template(): void
    {
        $lead = Lead::factory()->create();

        $this->expectException(\InvalidArgumentException::class);

        $this->emailService->sendFromTemplate($lead, 'nonexistent', []);
    }

    public function test_get_for_returns_emails(): void
    {
        $lead = Lead::factory()->create();
        $this->emailService->sendCustom($lead, 'Subject 1', 'Body', 'custom', $this->user);
        $this->emailService->sendCustom($lead, 'Subject 2', 'Body', 'custom', $this->user);

        $emails = $this->emailService->getFor($lead);

        $this->assertCount(2, $emails);
    }

    public function test_send_custom_sends_open_tracking_flag(): void
    {
        $lead = Lead::factory()->create(['email' => 'tracking@example.com']);

        $email = $this->emailService->sendCustom($lead, 'Tracking Test', 'Check open tracking', 'custom', $this->user);

        $this->assertDatabaseHas('emails', [
            'id' => $email->id,
            'to' => 'tracking@example.com',
            'subject' => 'Tracking Test',
            'opened_at' => null,
        ]);
    }
}
