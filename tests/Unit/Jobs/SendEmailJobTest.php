<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\SendEmailJob;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SendEmailJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();
    }

    public function test_handle_sends_email_for_lead(): void
    {
        $lead = Lead::factory()->create();

        $emailService = Mockery::mock(EmailService::class);
        $emailService->shouldReceive('sendCustom')->once()->with(
            Mockery::on(fn ($m) => $m->is($lead)),
            'Test Subject',
            'Test Body',
            'custom',
            null,
        );

        $job = new SendEmailJob('lead', (string) $lead->id, 'Test Subject', 'Test Body');
        $job->handle($emailService);
    }

    public function test_handle_sends_email_for_client(): void
    {
        $client = Client::factory()->create();

        $emailService = Mockery::mock(EmailService::class);
        $emailService->shouldReceive('sendCustom')->once()->with(
            Mockery::on(fn ($m) => $m->is($client)),
            'Test Subject',
            'Test Body',
            'custom',
            null,
        );

        $job = new SendEmailJob('client', (string) $client->id, 'Test Subject', 'Test Body');
        $job->handle($emailService);
    }

    public function test_handle_sends_email_for_project(): void
    {
        $project = Project::factory()->create();

        $emailService = Mockery::mock(EmailService::class);
        $emailService->shouldReceive('sendCustom')->once()->with(
            Mockery::on(fn ($m) => $m->is($project)),
            'Test Subject',
            'Test Body',
            'custom',
            null,
        );

        $job = new SendEmailJob('project', (string) $project->id, 'Test Subject', 'Test Body');
        $job->handle($emailService);
    }

    public function test_handle_uses_user_if_provided(): void
    {
        $user = User::factory()->create();
        $lead = Lead::factory()->create();

        $emailService = Mockery::mock(EmailService::class);
        $emailService->shouldReceive('sendCustom')->once()->with(
            Mockery::on(fn ($m) => $m->is($lead)),
            'Test Subject',
            'Test Body',
            'custom',
            Mockery::on(fn ($u) => $u->is($user)),
        );

        $job = new SendEmailJob('lead', (string) $lead->id, 'Test Subject', 'Test Body', $user->id);
        $job->handle($emailService);
    }

    public function test_handle_gracefully_handles_unknown_type(): void
    {
        $emailService = Mockery::mock(EmailService::class);
        $emailService->shouldNotReceive('sendCustom');

        $job = new SendEmailJob('invalid', '1', 'Test Subject', 'Test Body');
        $job->handle($emailService);
    }

    public function test_handle_gracefully_handles_missing_model(): void
    {
        $emailService = Mockery::mock(EmailService::class);
        $emailService->shouldNotReceive('sendCustom');

        $job = new SendEmailJob('lead', '999', 'Test Subject', 'Test Body');
        $job->handle($emailService);
    }
}
