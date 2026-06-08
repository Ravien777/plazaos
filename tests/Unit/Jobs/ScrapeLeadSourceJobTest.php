<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Enums\LeadStatus;
use App\Enums\SourceType;
use App\Jobs\ScrapeLeadSourceJob;
use App\LeadSources\ScraperFactory;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ScrapeLeadSourceJobTest extends TestCase
{
    use RefreshDatabase;

    private ScraperFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();
        $this->factory = $this->app->make(ScraperFactory::class);
    }

    public function test_job_is_dispatched_with_source(): void
    {
        Queue::fake();

        $source = LeadSource::factory()->create([
            'type' => SourceType::Upwork,
        ]);

        ScrapeLeadSourceJob::dispatch($source);

        Queue::assertPushed(ScrapeLeadSourceJob::class, function ($job) use ($source) {
            return $job->source->id === $source->id;
        });
    }

    public function test_job_updates_last_run_at(): void
    {
        $source = LeadSource::factory()->create([
            'type' => SourceType::Other,
            'last_run_at' => null,
        ]);

        (new ScrapeLeadSourceJob($source))->handle($this->factory);

        $this->assertNotNull($source->fresh()->last_run_at);
    }

    public function test_job_skips_non_scrapeable_type(): void
    {
        $source = LeadSource::factory()->create([
            'type' => SourceType::ColdEmail,
        ]);

        (new ScrapeLeadSourceJob($source))->handle($this->factory);

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_job_creates_leads_from_json_endpoint(): void
    {
        Http::fake([
            'https://example.com/leads.json' => Http::response([
                [
                    'name' => 'Alice Smith',
                    'email' => 'alice@example.com',
                    'company_name' => 'Acme Inc',
                ],
                [
                    'name' => 'Bob Jones',
                    'email' => 'bob@example.com',
                    'company_name' => 'BobCo',
                ],
            ], 200, ['Content-Type' => 'application/json']),
        ]);

        $source = LeadSource::factory()->create([
            'type' => SourceType::Website,
            'config' => ['url' => 'https://example.com/leads.json'],
        ]);

        (new ScrapeLeadSourceJob($source))->handle($this->factory);

        $this->assertDatabaseCount('leads', 2);
        $this->assertDatabaseHas('leads', [
            'email' => 'alice@example.com',
            'contact_name' => 'Alice Smith',
            'company_name' => 'Acme Inc',
            'status' => LeadStatus::New->value,
            'source' => $source->name,
        ]);
    }

    public function test_job_skips_duplicate_emails(): void
    {
        Lead::factory()->create([
            'email' => 'alice@example.com',
        ]);

        Http::fake([
            'https://example.com/leads.json' => Http::response([
                ['name' => 'Alice Smith', 'email' => 'alice@example.com'],
                ['name' => 'Bob Jones', 'email' => 'bob@example.com'],
            ], 200, ['Content-Type' => 'application/json']),
        ]);

        $source = LeadSource::factory()->create([
            'type' => SourceType::Website,
            'config' => ['url' => 'https://example.com/leads.json'],
        ]);

        (new ScrapeLeadSourceJob($source))->handle($this->factory);

        $this->assertDatabaseCount('leads', 2);
    }

    public function test_job_skips_duplicate_website(): void
    {
        Lead::factory()->create([
            'website' => 'https://example.com',
        ]);

        Http::fake([
            'https://example.com/leads.json' => Http::response([
                ['name' => 'Alice Smith', 'website' => 'https://example.com'],
            ], 200, ['Content-Type' => 'application/json']),
        ]);

        $source = LeadSource::factory()->create([
            'type' => SourceType::Website,
            'config' => ['url' => 'https://example.com/leads.json'],
        ]);

        (new ScrapeLeadSourceJob($source))->handle($this->factory);

        $this->assertDatabaseCount('leads', 1);
    }

    public function test_job_skips_duplicate_company_name(): void
    {
        Lead::factory()->create([
            'company_name' => 'Acme Inc',
            'website' => null,
            'email' => null,
        ]);

        Http::fake([
            'https://example.com/leads.json' => Http::response([
                ['name' => 'Alice Smith', 'company_name' => 'Acme Inc'],
            ], 200, ['Content-Type' => 'application/json']),
        ]);

        $source = LeadSource::factory()->create([
            'type' => SourceType::Website,
            'config' => ['url' => 'https://example.com/leads.json'],
        ]);

        (new ScrapeLeadSourceJob($source))->handle($this->factory);

        $this->assertDatabaseCount('leads', 1);
    }

    public function test_job_handles_http_failure(): void
    {
        Http::fake([
            'https://example.com/leads.json' => Http::response(null, 500),
        ]);

        $source = LeadSource::factory()->create([
            'type' => SourceType::Website,
            'config' => ['url' => 'https://example.com/leads.json'],
        ]);

        (new ScrapeLeadSourceJob($source))->handle($this->factory);

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_job_handles_missing_url_config(): void
    {
        $source = LeadSource::factory()->create([
            'type' => SourceType::Website,
            'config' => [],
        ]);

        (new ScrapeLeadSourceJob($source))->handle($this->factory);

        $this->assertDatabaseCount('leads', 0);
    }
}
