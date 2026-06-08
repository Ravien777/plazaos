<?php

declare(strict_types=1);

namespace Tests\Unit\LeadSources\Scrapers;

use App\LeadSources\Scrapers\FreelancerScraper;
use App\Models\LeadSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FreelancerScraperTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();
    }

    public function test_scrapes_jobs_from_rss(): void
    {
        $rss = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
<channel>
<item>
<title>ClientName: Build a Website</title>
<link>https://freelancer.com/project/1</link>
</item>
<item>
<title>BizCo: Mobile App Needed</title>
<link>https://freelancer.com/project/2</link>
</item>
</channel>
</rss>
XML;

        Http::fake([
            'freelancer.com/*' => Http::response($rss, 200, ['Content-Type' => 'text/xml']),
        ]);

        $source = LeadSource::factory()->create([
            'name' => 'Freelancer Leads',
            'config' => ['search_query' => 'php'],
        ]);

        $created = (new FreelancerScraper)->scrape($source);

        $this->assertEquals(2, $created);
        $this->assertDatabaseHas('leads', ['company_name' => 'ClientName']);
    }

    public function test_handles_http_failure(): void
    {
        Http::fake([
            'freelancer.com/*' => Http::response(null, 500),
        ]);

        $source = LeadSource::factory()->create(['name' => 'Freelancer Leads']);

        $created = (new FreelancerScraper)->scrape($source);

        $this->assertEquals(0, $created);
    }
}
