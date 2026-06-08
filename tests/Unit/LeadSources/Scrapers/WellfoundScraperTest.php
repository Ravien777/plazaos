<?php

declare(strict_types=1);

namespace Tests\Unit\LeadSources\Scrapers;

use App\LeadSources\Scrapers\WellfoundScraper;
use App\Models\LeadSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WellfoundScraperTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();
    }

    public function test_scrapes_companies_from_api(): void
    {
        Http::fake([
            'wellfound.com/*' => Http::response([
                'data' => [
                    ['name' => 'StartupInc', 'company_name' => 'StartupInc', 'url' => 'https://wellfound.com/company/1', 'industry' => 'Tech'],
                    ['name' => 'BizCorp', 'company_name' => 'BizCorp', 'url' => 'https://wellfound.com/company/2', 'industry' => 'Finance'],
                ],
            ]),
        ]);

        $source = LeadSource::factory()->create([
            'name' => 'Wellfound Leads',
            'config' => ['search_query' => 'startup'],
        ]);

        $created = (new WellfoundScraper)->scrape($source);

        $this->assertEquals(2, $created);
        $this->assertDatabaseHas('leads', ['company_name' => 'StartupInc', 'industry' => 'Tech']);
    }

    public function test_handles_non_array_response(): void
    {
        Http::fake([
            'wellfound.com/*' => Http::response(null, 200),
        ]);

        $source = LeadSource::factory()->create([
            'name' => 'Wellfound Leads',
            'config' => ['search_query' => 'startup'],
        ]);

        $created = (new WellfoundScraper)->scrape($source);

        $this->assertEquals(0, $created);
    }
}
