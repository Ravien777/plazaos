<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Lead;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_upcoming_returns_200(): void
    {
        Meeting::factory()->count(3)->create();

        $response = $this->get(route('meetings.upcoming'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Meetings/Index'));
    }

    public function test_create_returns_200(): void
    {
        $response = $this->get(route('meetings.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Meetings/Create'));
    }

    public function test_store_creates_meeting_and_redirects(): void
    {
        $response = $this->post(route('meetings.store'), [
            'title' => 'Follow-up',
            'start_time' => now()->addDay()->toDateTimeString(),
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('meetings', ['title' => 'Follow-up']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->post(route('meetings.store'), []);

        $response->assertSessionHasErrors(['title', 'start_time']);
    }

    public function test_show_returns_200(): void
    {
        $meeting = Meeting::factory()->create();

        $response = $this->get(route('meetings.show', $meeting));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Meetings/Show')
            ->has('meeting')
        );
    }

    public function test_update_updates_meeting_and_redirects(): void
    {
        $meeting = Meeting::factory()->create();

        $response = $this->put(route('meetings.update', $meeting), [
            'title' => 'Rescheduled',
        ]);

        $response->assertRedirect();
        $this->assertEquals('Rescheduled', $meeting->fresh()->title);
    }

    public function test_destroy_deletes_meeting_and_redirects(): void
    {
        $meeting = Meeting::factory()->create();

        $response = $this->delete(route('meetings.destroy', $meeting));

        $response->assertRedirect();
        $this->assertSoftDeleted($meeting);
    }

    public function test_store_for_meetable_creates_and_redirects(): void
    {
        $lead = Lead::factory()->create();

        $response = $this->post(route('meetings.store_for_meetable', ['meetableType' => 'lead', 'meetable' => $lead->id]), [
            'title' => 'Lead Meeting',
            'start_time' => now()->addDay()->toDateTimeString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('meetings', ['title' => 'Lead Meeting']);
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->post('/logout');

        $this->get(route('meetings.upcoming'))->assertRedirect(route('login'));
    }
}
