<?php

namespace Tests\Unit\Actions;

use App\Actions\CreateMeetingAction;
use App\Enums\MeetingStatus;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateMeetingActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateMeetingAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(CreateMeetingAction::class);
        User::factory()->create(['id' => 1]);
        $this->actingAs(User::find(1));
    }

    public function test_creates_meeting_with_correct_meetable(): void
    {
        $lead = Lead::factory()->create();

        $meeting = $this->action->execute($lead, [
            'title' => 'Strategy Call',
            'start_time' => now()->addDay(),
        ]);

        $this->assertDatabaseHas('meetings', [
            'id' => $meeting->id,
            'meetable_type' => Lead::class,
            'meetable_id' => $lead->id,
            'title' => 'Strategy Call',
        ]);
    }

    public function test_creates_meeting_with_scheduled_status(): void
    {
        $lead = Lead::factory()->create();

        $meeting = $this->action->execute($lead, [
            'title' => 'Meeting',
            'start_time' => now()->addDay(),
        ]);

        $this->assertEquals(MeetingStatus::Scheduled, $meeting->status);
    }

    public function test_creates_meeting_with_current_user(): void
    {
        $lead = Lead::factory()->create();

        $meeting = $this->action->execute($lead, [
            'title' => 'Meeting',
            'start_time' => now()->addDay(),
        ]);

        $this->assertEquals(1, $meeting->user_id);
    }

    public function test_logs_activity(): void
    {
        $lead = Lead::factory()->create();

        $this->action->execute($lead, [
            'title' => 'Meeting',
            'start_time' => now()->addDay(),
        ]);

        $this->assertDatabaseHas('activities', [
            'event' => 'meeting.created',
        ]);
    }
}
