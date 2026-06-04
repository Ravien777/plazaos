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

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create(['id' => 1]);
    }

    public function test_created_triggers_automation_activity(): void
    {
        $lead = Lead::factory()->create();
        $meeting = $lead->meetings()->save(Meeting::factory()->make(['user_id' => 1]));

        $this->assertDatabaseHas('activities', [
            'subject_type' => Meeting::class,
            'subject_id' => $meeting->id,
            'event' => 'automation.meeting_scheduled',
        ]);
    }
}
