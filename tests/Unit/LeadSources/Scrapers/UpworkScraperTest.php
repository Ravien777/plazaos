<?php

declare(strict_types=1);

namespace Tests\Unit\LeadSources\Scrapers;

use App\LeadSources\Scrapers\UpworkScraper;
use App\Models\LeadSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UpworkScraperTest extends TestCase
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
<title>ClientName: Need React Developer</title>
<link>https://upwork.com/job/1</link>
<description>Posted by ClientClient looking for React developer</description>
</item>
<item>
<title>SecondClient: Python Expert Wanted</title>
<link>https://upwork.com/job/2</link>
<description>Looking for Python expert</description>
</item>
</channel>
</rss>
XML;

        Http::fake([
            'upwork.com/*' => Http::response($rss, 200, ['Content-Type' => 'text/xml']),
        ]);

        $source = LeadSource::factory()->create([
            'name' => 'Upwork Leads',
            'config' => ['search_query' => 'react'],
        ]);

        $created = (new UpworkScraper)->scrape($source);

        $this->assertEquals(2, $created);
        $this->assertDatabaseHas('leads', ['company_name' => 'ClientName']);
    }

    public function test_uses_source_name_as_default_query(): void
    {
        $rss = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
<channel>
<item>
<title>ClientCo: Job Title</title>
<link>https://upwork.com/job/1</link>
<description>Some description</description>
</item>
</channel>
</rss>
XML;

        Http::fake([
            'upwork.com/*' => Http::response($rss, 200, ['Content-Type' => 'text/xml']),
        ]);

        $source = LeadSource::factory()->create(['name' => 'Web Dev']);

        $created = (new UpworkScraper)->scrape($source);

        $this->assertEquals(1, $created);
    }

    public function test_handles_http_failure(): void
    {
        Http::fake([
            'upwork.com/*' => Http::response(null, 500),
        ]);

        $source = LeadSource::factory()->create(['name' => 'Upwork Leads']);

        $created = (new UpworkScraper)->scrape($source);

        $this->assertEquals(0, $created);
    }
}
