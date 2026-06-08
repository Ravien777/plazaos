<?php

declare(strict_types=1);

namespace App\LeadSources\Scrapers;

use App\LeadSources\Concerns\DeduplicatesLeads;
use App\LeadSources\Contracts\ScraperInterface;
use App\Models\LeadSource;
use Illuminate\Support\Facades\Http;

class FreelancerScraper implements ScraperInterface
{
    use DeduplicatesLeads;

    public function scrape(LeadSource $source): int
    {
        $config = $source->config ?? [];
        $query = $config['search_query'] ?? $source->name;

        $url = 'https://www.freelancer.com/rss/jobs?q=' . urlencode($query);

        $response = Http::timeout(30)->withOptions([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; PlazaOS/1.0)',
            ],
        ])->get($url);

        if (! $response->successful()) {
            activity()->log($source, 'lead_source.scrape_error', "Freelancer RSS returned HTTP {$response->status()}.");

            return 0;
        }

        $xml = simplexml_load_string($response->body());

        if ($xml === false || ! isset($xml->channel->item)) {
            return 0;
        }

        $created = 0;

        foreach ($xml->channel->item as $item) {
            $title = (string) $item->title;
            $company = $this->extractCompanyFromTitle($title);

            $lead = $this->createLead([
                'contact_name' => $title,
                'company_name' => $company,
                'website' => (string) $item->link,
            ], $source->name);

            if ($lead) {
                $created++;
            }
        }

        return $created;
    }

    private function extractCompanyFromTitle(string $title): ?string
    {
        if (preg_match('/^([^:]+):/', $title, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }
}
