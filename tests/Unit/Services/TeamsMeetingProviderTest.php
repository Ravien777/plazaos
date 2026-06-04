<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Meeting;
use App\Models\User;
use App\Services\TeamsMeetingProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TeamsMeetingProviderTest extends TestCase
{
    use RefreshDatabase;

    private TeamsMeetingProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create(['id' => 1]);

        config(['services.microsoft_teams' => [
            'enabled' => true,
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'tenant_id' => 'test-tenant-id',
        ]]);

        $this->provider = app(TeamsMeetingProvider::class);
    }

    public function test_is_enabled_returns_true_when_configured(): void
    {
        $this->assertTrue($this->provider->isEnabled());
    }

    public function test_is_enabled_returns_false_when_disabled(): void
    {
        config(['services.microsoft_teams.enabled' => false]);
        $this->assertFalse($this->provider->isEnabled());
    }

    public function test_create_event_calls_graph_api_and_updates_meeting(): void
    {
        Http::fake([
            'login.microsoftonline.com/*/oauth2/v2.0/token' => Http::response([
                'access_token' => 'test-graph-token',
                'expires_in' => 3600,
            ]),
            'graph.microsoft.com/v1.0/users/onlineMeetings' => Http::response([
                'id' => 'MEETING_UUID_123',
                'joinUrl' => 'https://teams.microsoft.com/l/meetup-join/abc123',
                'subject' => 'Test Meeting',
            ]),
        ]);

        $meeting = Meeting::factory()->create([
            'provider' => 'microsoft_teams',
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
        ]);

        $result = $this->provider->createEvent($meeting);

        $meeting->refresh();
        $this->assertEquals('MEETING_UUID_123', $result);
        $this->assertEquals('MEETING_UUID_123', $meeting->google_event_id);
        $this->assertEquals('https://teams.microsoft.com/l/meetup-join/abc123', $meeting->meet_link);
    }

    public function test_create_event_returns_null_when_disabled(): void
    {
        config(['services.microsoft_teams.enabled' => false]);

        $meeting = Meeting::factory()->create();
        $result = $this->provider->createEvent($meeting);

        $this->assertNull($result);
    }

    public function test_create_event_handles_api_error(): void
    {
        Http::fake([
            'login.microsoftonline.com/*/oauth2/v2.0/token' => Http::response([
                'access_token' => 'test-graph-token',
                'expires_in' => 3600,
            ]),
            'graph.microsoft.com/v1.0/users/onlineMeetings' => Http::response(null, 500),
        ]);

        $meeting = Meeting::factory()->create([
            'provider' => 'microsoft_teams',
            'start_time' => now()->addDay(),
        ]);

        $result = $this->provider->createEvent($meeting);

        $this->assertNull($result);
        $this->assertNull($meeting->fresh()->google_event_id);
    }

    public function test_create_event_returns_null_when_no_id_in_response(): void
    {
        Http::fake([
            'login.microsoftonline.com/*/oauth2/v2.0/token' => Http::response([
                'access_token' => 'test-graph-token',
                'expires_in' => 3600,
            ]),
            'graph.microsoft.com/v1.0/users/onlineMeetings' => Http::response([
                'joinUrl' => 'https://teams.microsoft.com/l/meetup-join/abc123',
            ]),
        ]);

        $meeting = Meeting::factory()->create([
            'provider' => 'microsoft_teams',
            'start_time' => now()->addDay(),
        ]);

        $result = $this->provider->createEvent($meeting);

        $this->assertNull($result);
        $this->assertNull($meeting->fresh()->google_event_id);
    }

    public function test_update_event_calls_graph_api(): void
    {
        Http::fake([
            'login.microsoftonline.com/*/oauth2/v2.0/token' => Http::response([
                'access_token' => 'test-graph-token',
                'expires_in' => 3600,
            ]),
            'graph.microsoft.com/v1.0/users/onlineMeetings/MEETING_UUID_123' => Http::response([
                'id' => 'MEETING_UUID_123',
                'joinUrl' => 'https://teams.microsoft.com/l/meetup-join/updated',
            ]),
        ]);

        $meeting = Meeting::factory()->create([
            'provider' => 'microsoft_teams',
            'google_event_id' => 'MEETING_UUID_123',
            'start_time' => now()->addDay(),
        ]);

        $this->provider->updateEvent($meeting);

        $this->assertEquals('https://teams.microsoft.com/l/meetup-join/updated', $meeting->fresh()->meet_link);
    }

    public function test_update_event_skips_without_event_id(): void
    {
        $meeting = Meeting::factory()->create([
            'provider' => 'microsoft_teams',
            'google_event_id' => null,
        ]);

        Http::assertNothingSent();
        $this->provider->updateEvent($meeting);
    }

    public function test_delete_event_calls_graph_api(): void
    {
        Http::fake([
            'login.microsoftonline.com/*/oauth2/v2.0/token' => Http::response([
                'access_token' => 'test-graph-token',
                'expires_in' => 3600,
            ]),
            'graph.microsoft.com/v1.0/users/onlineMeetings/MEETING_UUID_123' => Http::response(null, 204),
        ]);

        $meeting = Meeting::factory()->create([
            'provider' => 'microsoft_teams',
            'google_event_id' => 'MEETING_UUID_123',
        ]);

        $this->provider->deleteEvent($meeting);
        Http::assertSent(fn ($request) => $request->method() === 'DELETE');
    }

    public function test_delete_event_skips_without_event_id(): void
    {
        $meeting = Meeting::factory()->create([
            'provider' => 'microsoft_teams',
            'google_event_id' => null,
        ]);

        $this->provider->deleteEvent($meeting);
        Http::assertNothingSent();
    }
}
