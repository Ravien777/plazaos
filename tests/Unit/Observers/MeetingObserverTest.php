<?php

declare(strict_types=1);

namespace Tests\Unit\Observers;

use App\Models\Lead;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingObserverTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_created_triggers_automation_activity(): void
    {
        $lead = Lead::factory()->create();
        $meeting = $lead->meetings()->save(Meeting::factory()->make(['user_id' => $this->user->id]));

        $this->assertDatabaseHas('activities', [
            'subject_type' => Meeting::class,
            'subject_id' => $meeting->id,
            'event' => 'automation.meeting_scheduled',
        ]);
    }
}
