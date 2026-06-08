<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use App\Models\Meeting;
use App\Models\User;
use App\Notifications\MeetingReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendMeetingRemindersCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_sends_reminder_for_upcoming_meetings(): void
    {
        $meeting = Meeting::factory()->create([
            'start_time' => now()->addMinutes(30),
            'reminder_sent' => false,
        ]);

        Notification::fake();

        $this->artisan('meetings:send-reminders')
            ->assertSuccessful();

        Notification::assertSentTo($this->user, MeetingReminder::class);
    }

    public function test_skips_meetings_outside_window(): void
    {
        Meeting::factory()->create([
            'start_time' => now()->addHours(3),
            'reminder_sent' => false,
        ]);

        Notification::fake();

        $this->artisan('meetings:send-reminders')
            ->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_skips_meetings_already_reminded(): void
    {
        Meeting::factory()->create([
            'start_time' => now()->addMinutes(30),
            'reminder_sent' => true,
        ]);

        Notification::fake();

        $this->artisan('meetings:send-reminders')
            ->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_marks_meeting_as_reminded(): void
    {
        $meeting = Meeting::factory()->create([
            'start_time' => now()->addMinutes(30),
            'reminder_sent' => false,
        ]);

        Notification::fake();

        $this->artisan('meetings:send-reminders')
            ->assertSuccessful();

        $this->assertTrue($meeting->fresh()->reminder_sent);
    }

    public function test_handles_no_meetings_gracefully(): void
    {
        Notification::fake();

        $this->artisan('meetings:send-reminders')
            ->assertSuccessful();

        Notification::assertNothingSent();
    }
}
