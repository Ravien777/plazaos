<?php

declare(strict_types=1);

namespace App\LeadSources\Scrapers;

use App\LeadSources\Concerns\DeduplicatesLeads;
use App\LeadSources\Contracts\ScraperInterface;
use App\Models\LeadSource;
use Illuminate\Support\Facades\Http;

class RemoteOkScraper implements ScraperInterface
{
    use DeduplicatesLeads;

    public function scrape(LeadSource $source): int
    {
        $response = Http::timeout(30)->get('https://remoteok.com/api');

        if (! $response->successful()) {
            activity()->log($source, 'lead_source.scrape_error', "RemoteOK API returned HTTP {$response->status()}.");

            return 0;
        }

        $items = $response->json();

        if (! is_array($items)) {
            return 0;
        }

        $created = 0;

        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['company'])) {
                continue;
            }

            $lead = $this->createLead([
                'contact_name' => $item['position'] ?? null,
                'company_name' => $item['company'] ?? null,
                'website' => $item['url'] ?? null,
            ], $source->name);

            if ($lead) {
                $created++;
            }
        }

        return $created;
    }
}
