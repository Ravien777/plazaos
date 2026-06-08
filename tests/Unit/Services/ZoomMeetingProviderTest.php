<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\User;
use App\Services\ZoomMeetingProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ZoomMeetingProviderTest extends TestCase
{
    use RefreshDatabase;

    private ZoomMeetingProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();

        config(['services.zoom' => [
            'enabled' => true,
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'account_id' => 'test-account-id',
        ]]);

        $this->provider = app(ZoomMeetingProvider::class);
    }

    public function test_is_enabled_returns_true_when_configured(): void
    {
        $this->assertTrue($this->provider->isEnabled());
    }

    public function test_is_enabled_returns_false_when_disabled(): void
    {
        config(['services.zoom.enabled' => false]);
        $this->assertFalse($this->provider->isEnabled());
    }

    public function test_create_event_calls_zoom_api_and_updates_meeting(): void
    {
        Http::fake([
            'zoom.us/oauth/token' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 3600,
            ]),
            'api.zoom.us/v2/users/me/meetings' => Http::response([
                'id' => 987654321,
                'join_url' => 'https://zoom.us/j/987654321',
                'start_url' => 'https://zoom.us/s/987654321',
            ]),
        ]);

        $meeting = Meeting::factory()->create([
            'provider' => 'zoom',
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
        ]);

        $result = $this->provider->createEvent($meeting);

        $meeting->refresh();
        $this->assertEquals('987654321', $result);
        $this->assertEquals('987654321', $meeting->google_event_id);
        $this->assertEquals('https://zoom.us/j/987654321', $meeting->meet_link);
    }

    public function test_create_event_returns_null_when_disabled(): void
    {
        config(['services.zoom.enabled' => false]);

        $meeting = Meeting::factory()->create();
        $result = $this->provider->createEvent($meeting);

        $this->assertNull($result);
    }

    public function test_create_event_handles_api_error(): void
    {
        Http::fake([
            'zoom.us/oauth/token' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 3600,
            ]),
            'api.zoom.us/v2/users/me/meetings' => Http::response(null, 500),
        ]);

        $meeting = Meeting::factory()->create([
            'provider' => 'zoom',
            'start_time' => now()->addDay(),
        ]);

        $result = $this->provider->createEvent($meeting);

        $this->assertNull($result);
        $this->assertNull($meeting->fresh()->google_event_id);
    }

    public function test_update_event_calls_zoom_api(): void
    {
        Http::fake([
            'zoom.us/oauth/token' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 3600,
            ]),
            'api.zoom.us/v2/meetings/12345' => Http::response([
                'id' => 12345,
                'join_url' => 'https://zoom.us/j/12345',
            ]),
        ]);

        $meeting = Meeting::factory()->create([
            'provider' => 'zoom',
            'google_event_id' => '12345',
            'start_time' => now()->addDay(),
        ]);

        $this->provider->updateEvent($meeting);

        $this->assertEquals('https://zoom.us/j/12345', $meeting->fresh()->meet_link);
    }

    public function test_update_event_skips_without_event_id(): void
    {
        $meeting = Meeting::factory()->create([
            'provider' => 'zoom',
            'google_event_id' => null,
        ]);

        Http::assertNothingSent();
        $this->provider->updateEvent($meeting);
    }

    public function test_delete_event_calls_zoom_api(): void
    {
        Http::fake([
            'zoom.us/oauth/token' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 3600,
            ]),
            'api.zoom.us/v2/meetings/12345' => Http::response(null, 204),
        ]);

        $meeting = Meeting::factory()->create([
            'provider' => 'zoom',
            'google_event_id' => '12345',
        ]);

        $this->provider->deleteEvent($meeting);
        Http::assertSent(fn ($request) => $request->method() === 'DELETE');
    }

    public function test_delete_event_skips_without_event_id(): void
    {
        $meeting = Meeting::factory()->create([
            'provider' => 'zoom',
            'google_event_id' => null,
        ]);

        $this->provider->deleteEvent($meeting);
        Http::assertNothingSent();
    }
}
