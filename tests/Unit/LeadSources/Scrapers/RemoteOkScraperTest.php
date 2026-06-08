<?php

declare(strict_types=1);

namespace Tests\Unit\LeadSources\Scrapers;

use App\LeadSources\Scrapers\RemoteOkScraper;
use App\Models\LeadSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RemoteOkScraperTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();
    }

    public function test_scrapes_jobs_from_api(): void
    {
        Http::fake([
            'remoteok.com/api' => Http::response([
                ['position' => 'Software Engineer', 'company' => 'TechCorp', 'url' => 'https://remoteok.com/job/1'],
                ['position' => 'Designer', 'company' => 'DesignCo', 'url' => 'https://remoteok.com/job/2'],
                ['position' => 'Developer', 'company' => 'DevInc', 'url' => 'https://remoteok.com/job/3'],
            ]),
        ]);

        $source = LeadSource::factory()->create(['name' => 'RemoteOK Jobs']);

        $created = (new RemoteOkScraper)->scrape($source);

        $this->assertEquals(3, $created);
        $this->assertDatabaseCount('leads', 3);
        $this->assertDatabaseHas('leads', ['contact_name' => 'Software Engineer', 'company_name' => 'TechCorp']);
    }

    public function test_handles_http_failure(): void
    {
        Http::fake([
            'remoteok.com/api' => Http::response(null, 500),
        ]);

        $source = LeadSource::factory()->create(['name' => 'RemoteOK Jobs']);

        $created = (new RemoteOkScraper)->scrape($source);

        $this->assertEquals(0, $created);
    }

    public function test_skips_items_without_company(): void
    {
        Http::fake([
            'remoteok.com/api' => Http::response([
                ['invalid' => true],
                ['position' => 'Valid Job', 'company' => 'RealCo', 'url' => 'https://remoteok.com/job/1'],
            ]),
        ]);

        $source = LeadSource::factory()->create(['name' => 'RemoteOK Jobs']);

        $created = (new RemoteOkScraper)->scrape($source);

        $this->assertEquals(1, $created);
    }
}
