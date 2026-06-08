<?php

namespace Tests\Unit\Notifications;

use App\Models\Client;
use App\Models\Lead;
use App\Models\LeadImport;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\Ticket;
use App\Notifications\DocumentUploaded;
use App\Notifications\FormSubmissionReceived;
use App\Notifications\ImportCompleted;
use App\Notifications\LeadCreated;
use App\Notifications\LeadImported;
use App\Notifications\LeadInactiveReminder;
use App\Notifications\MeetingScheduled;
use App\Notifications\ProjectStatusChanged;
use App\Notifications\TicketCreated;
use App\Notifications\TicketReplied;
use App\Models\IntakeForm;
use App\Models\IntakeFormSubmission;
use App\Models\User;
use Illuminate\Notifications\Slack\SlackMessage;
use Tests\TestCase;

class SlackNotificationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.slack.notifications.bot_user_oauth_token' => 'xoxb-test-token']);
    }

    protected function tearDown(): void
    {
        config(['services.slack.notifications.bot_user_oauth_token' => null]);
        parent::tearDown();
    }

    private function notifiable(): object
    {
        return User::factory()->make([
            'notification_preferences' => ['slack_enabled' => true],
        ]);
    }

    public function test_via_includes_slack_when_configured(): void
    {
        $lead = Lead::factory()->make();
        $notification = new LeadCreated($lead);

        $channels = $notification->via($this->notifiable());

        $this->assertContains('slack', $channels);
        $this->assertContains('database', $channels);
    }

    public function test_via_excludes_slack_when_not_configured(): void
    {
        config(['services.slack.notifications.bot_user_oauth_token' => '']);

        $lead = Lead::factory()->make();
        $notification = new LeadCreated($lead);

        $channels = $notification->via($this->notifiable());

        $this->assertNotContains('slack', $channels);
        $this->assertContains('database', $channels);
    }

    public function test_lead_created_to_slack_message(): void
    {
        $lead = Lead::factory()->make(['company_name' => 'Acme Corp']);
        $notification = new LeadCreated($lead);

        $message = $notification->toSlack($this->notifiable());

        $this->assertInstanceOf(SlackMessage::class, $message);
    }

    public function test_lead_imported_to_slack_message(): void
    {
        $lead = Lead::factory()->make(['company_name' => 'Acme Corp']);
        $notification = new LeadImported($lead);

        $message = $notification->toSlack($this->notifiable());

        $this->assertInstanceOf(SlackMessage::class, $message);
    }

    public function test_lead_inactive_reminder_to_slack_message(): void
    {
        $lead = Lead::factory()->make(['company_name' => 'Acme Corp']);
        $notification = new LeadInactiveReminder($lead);

        $message = $notification->toSlack($this->notifiable());

        $this->assertInstanceOf(SlackMessage::class, $message);
    }

    public function test_meeting_scheduled_to_slack_message(): void
    {
        $meeting = $this->createMock(Meeting::class);
        $meeting->method('__get')->willReturnMap([
            ['title', 'Discovery Call'],
            ['start_time', now()],
            ['provider', null],
        ]);
        $meeting->method('__call')->willReturn(null);

        $notification = new MeetingScheduled($meeting);

        $message = $notification->toSlack($this->notifiable());

        $this->assertInstanceOf(SlackMessage::class, $message);
    }

    public function test_ticket_created_to_slack_message(): void
    {
        $ticket = $this->createMock(Ticket::class);
        $ticket->method('__get')->willReturnMap([
            ['subject', 'Bug report'],
            ['status', 'open'],
            ['priority', 'high'],
        ]);
        $ticket->method('__call')->willReturn(null);

        $notification = new TicketCreated($ticket);

        $message = $notification->toSlack($this->notifiable());

        $this->assertInstanceOf(SlackMessage::class, $message);
    }

    public function test_ticket_replied_to_slack_message(): void
    {
        $ticket = $this->createMock(Ticket::class);
        $ticket->method('__get')->willReturnMap([
            ['subject', 'Bug report'],
            ['status', 'open'],
        ]);
        $ticket->method('__call')->willReturn(null);

        $notification = new TicketReplied($ticket);

        $message = $notification->toSlack($this->notifiable());

        $this->assertInstanceOf(SlackMessage::class, $message);
    }

    public function test_document_uploaded_to_slack_message(): void
    {
        $client = $this->createMock(Client::class);
        $client->method('__get')->willReturnMap([
            ['company_name', 'Acme Corp'],
            ['id', '1'],
        ]);

        $notification = new DocumentUploaded('proposal.pdf', $client);

        $message = $notification->toSlack($this->notifiable());

        $this->assertInstanceOf(SlackMessage::class, $message);
    }

    public function test_project_status_changed_to_slack_message(): void
    {
        $project = $this->createMock(Project::class);
        $project->method('__get')->willReturnMap([
            ['name', 'Website Redesign'],
            ['progress_percentage', 50],
            ['id', '1'],
        ]);

        $notification = new ProjectStatusChanged($project, 'Discovery', 'Design');

        $message = $notification->toSlack($this->notifiable());

        $this->assertInstanceOf(SlackMessage::class, $message);
    }

    public function test_form_submission_received_to_slack_message(): void
    {
        $form = $this->createMock(IntakeForm::class);
        $form->method('__get')->willReturnMap([
            ['title', 'Website Intake'],
            ['id', '1'],
        ]);

        $client = $this->createMock(Client::class);
        $client->method('__get')->willReturnMap([
            ['company_name', 'Acme Corp'],
        ]);

        $submission = $this->createMock(IntakeFormSubmission::class);
        $submission->method('__get')->willReturn('1');
        $submission->method('__call')->willReturn(null);

        $notification = new FormSubmissionReceived($form, $client, $submission);

        $message = $notification->toSlack($this->notifiable());

        $this->assertInstanceOf(SlackMessage::class, $message);
    }

    public function test_import_completed_to_slack_message(): void
    {
        $import = $this->createMock(LeadImport::class);
        $import->method('__get')->willReturnMap([
            ['filename', 'leads.csv'],
            ['processed', 100],
            ['failed', 2],
            ['id', '1'],
        ]);

        $notification = new ImportCompleted($import);

        $message = $notification->toSlack($this->notifiable());

        $this->assertInstanceOf(SlackMessage::class, $message);
    }

    public function test_user_model_route_notification_for_slack(): void
    {
        config(['services.slack.notifications.channel' => '#plazaos']);

        $user = new User;
        $channel = $user->routeNotificationForSlack($this->createMock(
            \Illuminate\Notifications\Notification::class
        ));

        $this->assertEquals('#plazaos', $channel);
    }
}
