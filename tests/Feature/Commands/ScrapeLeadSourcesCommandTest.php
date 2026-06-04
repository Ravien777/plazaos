<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use App\Enums\SourceType;
use App\Jobs\ScrapeLeadSourceJob;
use App\Models\LeadSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ScrapeLeadSourcesCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create(['id' => 1]);
    }

    public function test_dispatches_jobs_for_due_sources(): void
    {
        Bus::fake();

        LeadSource::factory()->create([
            'frequency' => 'hourly',
            'last_run_at' => null,
            'is_active' => true,
        ]);

        $this->artisan('leads:scrape')
            ->assertSuccessful();

        Bus::assertDispatched(ScrapeLeadSourceJob::class);
    }

    public function test_skips_non_due_sources(): void
    {
        Bus::fake();

        LeadSource::factory()->create([
            'frequency' => 'hourly',
            'last_run_at' => now(),
            'is_active' => true,
        ]);

        $this->artisan('leads:scrape')
            ->assertSuccessful();

        Bus::assertNotDispatched(ScrapeLeadSourceJob::class);
    }

    public function test_skips_manual_sources(): void
    {
        Bus::fake();

        LeadSource::factory()->create([
            'frequency' => 'manual',
            'last_run_at' => null,
            'is_active' => true,
        ]);

        $this->artisan('leads:scrape')
            ->assertSuccessful();

        Bus::assertNotDispatched(ScrapeLeadSourceJob::class);
    }

    public function test_skips_inactive_sources(): void
    {
        Bus::fake();

        LeadSource::factory()->create([
            'frequency' => 'hourly',
            'last_run_at' => null,
            'is_active' => false,
        ]);

        $this->artisan('leads:scrape')
            ->assertSuccessful();

        Bus::assertNotDispatched(ScrapeLeadSourceJob::class);
    }

    public function test_dispatches_jobs_for_daily_sources_due(): void
    {
        Bus::fake();

        LeadSource::factory()->create([
            'frequency' => 'daily',
            'last_run_at' => now()->subHours(25),
            'is_active' => true,
        ]);

        $this->artisan('leads:scrape')
            ->assertSuccessful();

        Bus::assertDispatched(ScrapeLeadSourceJob::class);
    }

    public function test_skips_daily_sources_not_due(): void
    {
        Bus::fake();

        LeadSource::factory()->create([
            'frequency' => 'daily',
            'last_run_at' => now()->subHours(23),
            'is_active' => true,
        ]);

        $this->artisan('leads:scrape')
            ->assertSuccessful();

        Bus::assertNotDispatched(ScrapeLeadSourceJob::class);
    }

    public function test_dispatches_for_weekly_due(): void
    {
        Bus::fake();

        LeadSource::factory()->create([
            'frequency' => 'weekly',
            'last_run_at' => now()->subDays(8),
            'is_active' => true,
        ]);

        $this->artisan('leads:scrape')
            ->assertSuccessful();

        Bus::assertDispatched(ScrapeLeadSourceJob::class);
    }

    public function test_dispatches_jobs_for_multiple_due_sources(): void
    {
        Bus::fake();

        LeadSource::factory()->create([
            'frequency' => 'hourly',
            'last_run_at' => null,
            'is_active' => true,
        ]);

        LeadSource::factory()->create([
            'frequency' => 'daily',
            'last_run_at' => now()->subHours(25),
            'is_active' => true,
        ]);

        LeadSource::factory()->create([
            'frequency' => 'manual',
            'last_run_at' => null,
            'is_active' => true,
        ]);

        $this->artisan('leads:scrape')
            ->assertSuccessful();

        Bus::assertDispatchedTimes(ScrapeLeadSourceJob::class, 2);
    }
}
