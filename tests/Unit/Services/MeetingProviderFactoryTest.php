<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\MeetingProviderInterface;
use App\Services\GoogleCalendarService;
use App\Services\MeetingProviderFactory;
use App\Services\TeamsMeetingProvider;
use App\Services\ZoomMeetingProvider;
use Tests\TestCase;

class MeetingProviderFactoryTest extends TestCase
{
    private MeetingProviderFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = app(MeetingProviderFactory::class);
    }

    public function test_make_returns_google_calendar_service(): void
    {
        $provider = $this->factory->make('google_meet');

        $this->assertInstanceOf(GoogleCalendarService::class, $provider);
        $this->assertInstanceOf(MeetingProviderInterface::class, $provider);
    }

    public function test_make_returns_zoom_provider(): void
    {
        $provider = $this->factory->make('zoom');

        $this->assertInstanceOf(ZoomMeetingProvider::class, $provider);
        $this->assertInstanceOf(MeetingProviderInterface::class, $provider);
    }

    public function test_make_returns_teams_provider(): void
    {
        $provider = $this->factory->make('microsoft_teams');

        $this->assertInstanceOf(TeamsMeetingProvider::class, $provider);
        $this->assertInstanceOf(MeetingProviderInterface::class, $provider);
    }

    public function test_make_throws_for_unknown_provider(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown meeting provider: unknown');

        $this->factory->make('unknown');
    }
}
