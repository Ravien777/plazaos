<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Meeting;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleCalendarServiceTest extends TestCase
{
    use RefreshDatabase;

    private GoogleCalendarService $service;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();
        $this->service = app(GoogleCalendarService::class);
    }

    public function test_isEnabled_returns_config_value_when_no_db_setting(): void
    {
        config(['google-calendar.enabled' => false]);

        $this->assertFalse($this->service->isEnabled());
    }

    public function test_createEvent_returns_null_when_disabled(): void
    {
        config(['google-calendar.enabled' => false]);

        $meeting = Meeting::factory()->create();

        $this->assertNull($this->service->createEvent($meeting));
    }

    public function test_createEvent_returns_null_when_client_unavailable(): void
    {
        config(['google-calendar.enabled' => true]);

        $meeting = Meeting::factory()->create();

        $this->assertNull($this->service->createEvent($meeting));
    }

    public function test_updateEvent_returns_early_when_not_enabled(): void
    {
        config(['google-calendar.enabled' => false]);

        $meeting = Meeting::factory()->create();

        $this->assertNull($this->service->updateEvent($meeting));
    }

    public function test_updateEvent_returns_early_when_no_google_event_id(): void
    {
        config(['google-calendar.enabled' => true]);

        $meeting = Meeting::factory()->create(['google_event_id' => null]);

        $this->assertNull($this->service->updateEvent($meeting));
    }

    public function test_deleteEvent_returns_early_when_not_enabled(): void
    {
        config(['google-calendar.enabled' => false]);

        $meeting = Meeting::factory()->create();

        $this->assertNull($this->service->deleteEvent($meeting));
    }
}
