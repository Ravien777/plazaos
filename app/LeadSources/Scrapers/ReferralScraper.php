<?php

declare(strict_types=1);

namespace App\LeadSources\Scrapers;

use App\LeadSources\Contracts\ScraperInterface;
use App\Models\LeadSource;

class ReferralScraper implements ScraperInterface
{
    public function scrape(LeadSource $source): int
    {
        activity()->log(
            $source,
            'lead_source.scrape_info',
            "Referral source '{$source->name}' cannot be scraped. Referral leads should be created manually or imported via CSV."
        );

        return 0;
    }
}
