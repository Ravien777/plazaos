<?php

declare(strict_types=1);

namespace Tests\Unit\LeadSources\Scrapers;

use App\LeadSources\Scrapers\ColdEmailScraper;
use App\LeadSources\Scrapers\GenericScraper;
use App\LeadSources\Scrapers\LinkedInScraper;
use App\LeadSources\Scrapers\ReferralScraper;
use App\Models\LeadSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NonScrapeableScrapersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();
    }

    public function test_cold_email_returns_zero(): void
    {
        $source = LeadSource::factory()->create(['name' => 'Cold Email Test']);

        $created = (new ColdEmailScraper)->scrape($source);

        $this->assertEquals(0, $created);
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_referral_returns_zero(): void
    {
        $source = LeadSource::factory()->create(['name' => 'Referral Test']);

        $created = (new ReferralScraper)->scrape($source);

        $this->assertEquals(0, $created);
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_generic_returns_zero(): void
    {
        $source = LeadSource::factory()->create(['name' => 'Other Test']);

        $created = (new GenericScraper)->scrape($source);

        $this->assertEquals(0, $created);
        $this->assertDatabaseCount('leads', 0);
    }
}
