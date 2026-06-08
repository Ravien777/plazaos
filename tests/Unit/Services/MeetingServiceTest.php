<?php

namespace Tests\Unit\Services;

use App\Enums\MeetingStatus;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\User;
use App\Notifications\MeetingScheduled;
use App\Services\MeetingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MeetingServiceTest extends TestCase
{
    use RefreshDatabase;

    private MeetingService $meetingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->meetingService = app(MeetingService::class);
        User::factory()->create();
    }

    public function test_list_returns_paginated_results(): void
    {
        Lead::factory()->create()->meetings()->saveMany(
            Meeting::factory()->count(3)->make(['user_id' => 1])
        );

        $result = $this->meetingService->list();

        $this->assertCount(3, $result->items());
    }

    public function test_list_filters_by_upcoming(): void
    {
        Lead::factory()->create()->meetings()->saveMany([
            Meeting::factory()->make(['start_time' => now()->addDay(), 'user_id' => 1]),
            Meeting::factory()->make(['start_time' => now()->subDay(), 'user_id' => 1]),
        ]);

        $result = $this->meetingService->list(['upcoming' => true]);

        $this->assertCount(1, $result->items());
    }

    public function test_create_creates_meeting_and_logs_activity_and_sends_notification(): void
    {
        Notification::fake();
        $lead = Lead::factory()->create();

        $meeting = $this->meetingService->create([
            'meetable_type' => Lead::class,
            'meetable_id' => $lead->id,
            'title' => 'Project Kickoff',
            'start_time' => now()->addDay(),
            'status' => MeetingStatus::Scheduled->value,
            'user_id' => 1,
        ]);

        $this->assertDatabaseHas('meetings', ['id' => $meeting->id]);
        $this->assertDatabaseHas('activities', [
            'subject_type' => Meeting::class,
            'subject_id' => $meeting->id,
            'event' => 'meeting.created',
        ]);
        Notification::assertSentTo(User::find(1), MeetingScheduled::class);
    }

    public function test_get_for_returns_meetings_for_polymorphic_model(): void
    {
        $lead = Lead::factory()->create();
        $client = Client::factory()->create();

        $lead->meetings()->save(Meeting::factory()->make(['user_id' => 1]));
        $client->meetings()->save(Meeting::factory()->make(['user_id' => 1]));

        $this->assertCount(1, $this->meetingService->getFor($lead));
        $this->assertCount(1, $this->meetingService->getFor($client));
    }

    public function test_upcoming_returns_only_future_scheduled_meetings(): void
    {
        Lead::factory()->create()->meetings()->saveMany([
            Meeting::factory()->make(['start_time' => now()->addDay(), 'user_id' => 1]),
            Meeting::factory()->make(['start_time' => now()->subDay(), 'user_id' => 1]),
            Meeting::factory()->make(['start_time' => now()->addDay(), 'status' => MeetingStatus::Completed, 'user_id' => 1]),
        ]);

        $upcoming = $this->meetingService->upcoming();

        $this->assertCount(1, $upcoming);
    }

    public function test_upcoming_count_returns_correct_count(): void
    {
        Lead::factory()->create()->meetings()->saveMany([
            Meeting::factory()->make(['start_time' => now()->addDay(), 'user_id' => 1]),
            Meeting::factory()->make(['start_time' => now()->subDay(), 'user_id' => 1]),
        ]);

        $this->assertEquals(1, $this->meetingService->upcomingCount());
    }

    public function test_update_updates_meeting_and_logs_activity(): void
    {
        $lead = Lead::factory()->create();
        $meeting = $lead->meetings()->save(Meeting::factory()->make(['user_id' => 1]));

        $this->meetingService->update($meeting, ['title' => 'Updated Title']);

        $this->assertEquals('Updated Title', $meeting->fresh()->title);
        $this->assertDatabaseHas('activities', [
            'subject_type' => Meeting::class,
            'subject_id' => $meeting->id,
            'event' => 'meeting.updated',
        ]);
    }

    public function test_delete_deletes_meeting_and_logs_activity(): void
    {
        $lead = Lead::factory()->create();
        $meeting = $lead->meetings()->save(Meeting::factory()->make(['user_id' => 1]));

        $this->meetingService->delete($meeting);

        $this->assertSoftDeleted($meeting);
        $this->assertDatabaseHas('activities', [
            'subject_type' => Meeting::class,
            'subject_id' => $meeting->id,
            'event' => 'meeting.deleted',
        ]);
    }
}
