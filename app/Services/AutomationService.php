<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Client;
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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class AutomationService
{
    public function onLeadImported(Lead $lead): void
    {
        activity()->log($lead, 'automation.lead_imported', "Automation: Lead {$lead->company_name} was imported.");

        Notification::send(User::first(), new LeadImported($lead));
    }

    public function onLeadInactive(Lead $lead): void
    {
        activity()->log($lead, 'automation.lead_inactive', "Automation: Lead {$lead->company_name} has been inactive for 7 days.");

        Notification::send(User::first(), new LeadInactiveReminder($lead));
    }

    public function onMeetingScheduled(Meeting $meeting): void
    {
        activity()->log($meeting, 'automation.meeting_scheduled', "Automation: Meeting {$meeting->title} was scheduled — follow-up reminder created.");

        Notification::send(User::first(), new MeetingFollowUp($meeting));
    }

    public function onLeadConverted(Lead $lead, Client $client): void
    {
        activity()->log($lead, 'automation.lead_converted', "Automation: Lead {$lead->company_name} was converted to client — onboarding triggered.");
        activity()->log($client, 'automation.lead_converted', "Automation: Client {$client->company_name} created from lead — onboarding checklist generated.");

        Notification::send(User::first(), new OnboardingChecklist($lead, $client));
    }

    public function onProjectCompleted(Project $project): void
    {
        activity()->log($project, 'automation.project_completed', "Automation: Project {$project->name} completed — testimonial request triggered.");

        Notification::send(User::first(), new TestimonialRequest($project));
    }

    public function onIntakeSubmitted(IntakeFormSubmission $submission): void
    {
        $submission->loadMissing('form', 'client');

        activity()->log($submission, 'automation.intake_submitted', "Automation: Intake form \"{$submission->form->title}\" submitted by {$submission->client->company_name}.");

        Notification::send(User::first(), new FormSubmissionReceived(
            $submission->form,
            $submission->client,
            $submission,
        ));
    }
}
