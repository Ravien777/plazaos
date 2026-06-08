<?php

declare(strict_types=1);

namespace App\LeadSources\Scrapers;

use App\LeadSources\Concerns\DeduplicatesLeads;
use App\LeadSources\Contracts\ScraperInterface;
use App\Models\LeadSource;
use Illuminate\Support\Facades\Http;

class WellfoundScraper implements ScraperInterface
{
    use DeduplicatesLeads;

    public function scrape(LeadSource $source): int
    {
        $config = $source->config ?? [];
        $query = $config['search_query'] ?? $source->name;

        $url = 'https://wellfound.com/api/search?query=' . urlencode($query) . '&type=companies';

        $response = Http::timeout(30)->withOptions([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; PlazaOS/1.0)',
                'Accept' => 'application/json',
            ],
        ])->get($url);

        if (! $response->successful()) {
            activity()->log($source, 'lead_source.scrape_error', "Wellfound search returned HTTP {$response->status()}.");

            return 0;
        }

        $body = $response->json();

        if (! is_array($body)) {
            return 0;
        }

        $items = $body['data'] ?? $body['results'] ?? $body['companies'] ?? $body;

        if (! is_array($items)) {
            return 0;
        }

        $created = 0;

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $lead = $this->createLead([
                'contact_name' => $item['name'] ?? $item['company_name'] ?? null,
                'company_name' => $item['company_name'] ?? $item['name'] ?? null,
                'website' => $item['url'] ?? $item['website_url'] ?? null,
                'industry' => $item['industry'] ?? null,
                'city' => $item['location'] ?? null,
            ], $source->name);

            if ($lead) {
                $created++;
            }
        }

        return $created;
    }
}
