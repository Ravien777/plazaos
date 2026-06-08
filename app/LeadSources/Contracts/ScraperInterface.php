<?php

declare(strict_types=1);

namespace App\LeadSources\Contracts;

use App\Models\LeadSource;

interface ScraperInterface
{
    public function scrape(LeadSource $source): int;
}
