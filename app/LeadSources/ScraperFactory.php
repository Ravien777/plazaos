<?php

declare(strict_types=1);

namespace App\LeadSources;

use App\Enums\SourceType;
use App\LeadSources\Contracts\ScraperInterface;
use App\LeadSources\Scrapers\ColdEmailScraper;
use App\LeadSources\Scrapers\FiverrScraper;
use App\LeadSources\Scrapers\FreelancerScraper;
use App\LeadSources\Scrapers\GenericScraper;
use App\LeadSources\Scrapers\GuruScraper;
use App\LeadSources\Scrapers\LinkedInScraper;
use App\LeadSources\Scrapers\PeoplePerHourScraper;
use App\LeadSources\Scrapers\ReferralScraper;
use App\LeadSources\Scrapers\RemoteOkScraper;
use App\LeadSources\Scrapers\ToptalScraper;
use App\LeadSources\Scrapers\UpworkScraper;
use App\LeadSources\Scrapers\WebsiteScraper;
use App\LeadSources\Scrapers\WellfoundScraper;
use App\LeadSources\Scrapers\WeWorkRemotelyScraper;

class ScraperFactory
{
    public function driver(SourceType $type): ScraperInterface
    {
        return match ($type) {
            SourceType::Website => app(WebsiteScraper::class),
            SourceType::RemoteOK => app(RemoteOkScraper::class),
            SourceType::WeWorkRemotely => app(WeWorkRemotelyScraper::class),
            SourceType::Upwork => app(UpworkScraper::class),
            SourceType::Freelancer => app(FreelancerScraper::class),
            SourceType::Guru => app(GuruScraper::class),
            SourceType::WellFound => app(WellfoundScraper::class),
            SourceType::LinkedIn => app(LinkedInScraper::class),
            SourceType::Fiverr => app(FiverrScraper::class),
            SourceType::PeoplePerHour => app(PeoplePerHourScraper::class),
            SourceType::Toptal => app(ToptalScraper::class),
            SourceType::ColdEmail => app(ColdEmailScraper::class),
            SourceType::Referral => app(ReferralScraper::class),
            SourceType::Other => app(GenericScraper::class),
        };
    }
}
