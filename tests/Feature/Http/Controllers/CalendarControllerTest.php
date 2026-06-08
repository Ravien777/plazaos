<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Meeting;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CalendarControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $team = Team::factory()->create();

        $this->user = User::factory()->create([
            'team_id' => $team->id,
            'role' => 'owner',
        ]);
    }

    public function test_index_returns_calendar_page(): void
    {
        Meeting::factory()->count(3)->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->get(route('calendar.index'))
            ->assertInertia(fn ($page) => $page->component('Calendar/Index'));
    }

    public function test_index_passes_team_members(): void
    {
        $this->actingAs($this->user)
            ->get(route('calendar.index'))
            ->assertInertia(fn ($page) => $page->has('teamMembers'));
    }

    public function test_index_passes_today_meetings(): void
    {
        Meeting::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->user->team_id,
            'start_time' => now()->addHour(),
            'status' => 'scheduled',
        ]);

        $this->actingAs($this->user)
            ->get(route('calendar.index'))
            ->assertInertia(fn ($page) => $page->has('todayMeetings', 1));
    }

    public function test_events_returns_json(): void
    {
        $start = now()->startOfMonth()->toIso8601String();
        $end = now()->endOfMonth()->toIso8601String();

        Meeting::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'team_id' => $this->user->team_id,
            'status' => 'scheduled',
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
        ]);

        $this->actingAs($this->user)
            ->getJson(route('calendar.events', ['start' => $start, 'end' => $end]))
            ->assertJsonCount(2);
    }

    public function test_events_validates_date_range(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('calendar.events', ['start' => 'invalid', 'end' => 'invalid']))
            ->assertJsonValidationErrors(['start', 'end']);
    }

    public function test_export_ics_returns_file(): void
    {
        $meeting = Meeting::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->user->team_id,
            'title' => 'Test Meeting',
        ]);

        $this->actingAs($this->user)
            ->get(route('meetings.ics', $meeting))
            ->assertHeader('Content-Type', 'text/calendar; charset=utf-8')
            ->assertHeader('Content-Disposition', 'attachment; filename="meeting-' . $meeting->id . '.ics"')
            ->assertSee('BEGIN:VCALENDAR')
            ->assertSee('SUMMARY:Test Meeting');
    }

    public function test_index_guest_redirects(): void
    {
        $this->get(route('calendar.index'))
            ->assertRedirect(route('login'));
    }

    public function test_events_guest_redirects(): void
    {
        $this->getJson(route('calendar.events', ['start' => '2026-01-01', 'end' => '2026-01-31']))
            ->assertUnauthorized();
    }
}
