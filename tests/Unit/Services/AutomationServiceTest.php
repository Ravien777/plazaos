<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Client;
use App\Models\IntakeForm;
use App\Models\IntakeFormSubmission;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\User;
use App\Notifications\FormSubmissionReceived;
use App\Notifications\LeadImported;
use App\Notifications\LeadInactiveReminder;
use App\Notifications\MeetingFollowUp;
use App\Notifications\OnboardingChecklist;
use App\Notifications\TestimonialRequest;
use App\Services\AutomationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AutomationServiceTest extends TestCase
{
    use RefreshDatabase;

    private AutomationService $automationService;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();
        $this->automationService = app(AutomationService::class);
    }

    public function test_on_lead_imported_sends_notification_and_logs_activity(): void
    {
        Notification::fake();
        $lead = Lead::factory()->create();

        $this->automationService->onLeadImported($lead);

        Notification::assertSentTo(User::find(1), LeadImported::class);
        $this->assertDatabaseHas('activities', [
            'subject_type' => Lead::class,
            'subject_id' => $lead->id,
            'event' => 'automation.lead_imported',
        ]);
    }

    public function test_on_lead_inactive_sends_notification_and_logs_activity(): void
    {
        Notification::fake();
        $lead = Lead::factory()->create();

        $this->automationService->onLeadInactive($lead);

        Notification::assertSentTo(User::find(1), LeadInactiveReminder::class);
        $this->assertDatabaseHas('activities', [
            'subject_type' => Lead::class,
            'subject_id' => $lead->id,
            'event' => 'automation.lead_inactive',
        ]);
    }

    public function test_on_meeting_scheduled_sends_notification_and_logs_activity(): void
    {
        Notification::fake();
        $lead = Lead::factory()->create();
        $meeting = $lead->meetings()->save(Meeting::factory()->make(['user_id' => 1]));

        $this->automationService->onMeetingScheduled($meeting);

        Notification::assertSentTo(User::find(1), MeetingFollowUp::class);
        $this->assertDatabaseHas('activities', [
            'subject_type' => Meeting::class,
            'subject_id' => $meeting->id,
            'event' => 'automation.meeting_scheduled',
        ]);
    }

    public function test_on_lead_converted_sends_notification_and_logs_activities(): void
    {
        Notification::fake();
        $lead = Lead::factory()->create();
        $client = Client::factory()->create();

        $this->automationService->onLeadConverted($lead, $client);

        Notification::assertSentTo(User::find(1), OnboardingChecklist::class);
        $this->assertDatabaseHas('activities', [
            'subject_type' => Lead::class,
            'subject_id' => $lead->id,
            'event' => 'automation.lead_converted',
        ]);
        $this->assertDatabaseHas('activities', [
            'subject_type' => Client::class,
            'subject_id' => $client->id,
            'event' => 'automation.lead_converted',
        ]);
    }

    public function test_on_project_completed_sends_notification_and_logs_activity(): void
    {
        Notification::fake();
        $project = Project::factory()->create();

        $this->automationService->onProjectCompleted($project);

        Notification::assertSentTo(User::find(1), TestimonialRequest::class);
        $this->assertDatabaseHas('activities', [
            'subject_type' => Project::class,
            'subject_id' => $project->id,
            'event' => 'automation.project_completed',
        ]);
    }

    public function test_on_intake_submitted_sends_notification_and_logs_activity(): void
    {
        Notification::fake();
        $submission = IntakeFormSubmission::factory()->create();

        $this->automationService->onIntakeSubmitted($submission);

        Notification::assertSentTo(User::find(1), FormSubmissionReceived::class);
        $this->assertDatabaseHas('activities', [
            'subject_type' => IntakeFormSubmission::class,
            'subject_id' => $submission->id,
            'event' => 'automation.intake_submitted',
        ]);
    }
}
