<?php

declare(strict_types=1);

namespace App\LeadSources\Scrapers;

use App\LeadSources\Contracts\ScraperInterface;
use App\Models\LeadSource;

class ColdEmailScraper implements ScraperInterface
{
    public function scrape(LeadSource $source): int
    {
        activity()->log(
            $source,
            'lead_source.scrape_info',
            "Cold Email source '{$source->name}' cannot be scraped. Leads from cold email campaigns should be created manually or imported via CSV."
        );

        return 0;
    }
}
