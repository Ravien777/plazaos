<?php

declare(strict_types=1);

namespace App\LeadSources\Scrapers;

use App\LeadSources\Contracts\ScraperInterface;
use App\Models\LeadSource;

class GenericScraper implements ScraperInterface
{
    public function scrape(LeadSource $source): int
    {
        activity()->log(
            $source,
            'lead_source.scrape_info',
            "Lead source '{$source->name}' (type: {$source->type->value}) does not have an automated scraper. Leads should be created manually or imported via CSV."
        );

        return 0;
    }
}
