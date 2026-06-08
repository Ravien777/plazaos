<?php

declare(strict_types=1);

namespace Tests\Unit\LeadSources\Scrapers;

use App\LeadSources\Scrapers\GuruScraper;
use App\Models\LeadSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GuruScraperTest extends TestCase
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
<title>CompanyName: Senior Developer Needed</title>
<link>https://guru.com/job/1</link>
</item>
<item>
<title>StartupInc: UX Designer</title>
<link>https://guru.com/job/2</link>
</item>
</channel>
</rss>
XML;

        Http::fake([
            'guru.com/*' => Http::response($rss, 200, ['Content-Type' => 'text/xml']),
        ]);

        $source = LeadSource::factory()->create([
            'name' => 'Guru Leads',
            'config' => ['search_query' => 'react'],
        ]);

        $created = (new GuruScraper)->scrape($source);

        $this->assertEquals(2, $created);
        $this->assertDatabaseHas('leads', ['company_name' => 'CompanyName']);
    }

    public function test_handles_http_failure(): void
    {
        Http::fake([
            'guru.com/*' => Http::response(null, 500),
        ]);

        $source = LeadSource::factory()->create(['name' => 'Guru Leads']);

        $created = (new GuruScraper)->scrape($source);

        $this->assertEquals(0, $created);
    }
}
