<?php

declare(strict_types=1);

namespace App\LeadSources\Scrapers;

use App\LeadSources\Concerns\DeduplicatesLeads;
use App\LeadSources\Contracts\ScraperInterface;
use App\Models\LeadSource;
use Illuminate\Support\Facades\Http;

class UpworkScraper implements ScraperInterface
{
    use DeduplicatesLeads;

    public function scrape(LeadSource $source): int
    {
        $config = $source->config ?? [];
        $query = $config['search_query'] ?? $source->name;

        $url = 'https://www.upwork.com/ab/feed/jobs/rss?q=' . urlencode($query);

        $response = Http::timeout(30)->withOptions([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; PlazaOS/1.0)',
            ],
        ])->get($url);

        if (! $response->successful()) {
            activity()->log($source, 'lead_source.scrape_error', "Upwork RSS returned HTTP {$response->status()}.");

            return 0;
        }

        $xml = simplexml_load_string($response->body());

        if ($xml === false || ! isset($xml->channel->item)) {
            return 0;
        }

        $created = 0;

        foreach ($xml->channel->item as $item) {
            $title = (string) $item->title;
            $description = strip_tags((string) $item->description);
            $company = $this->extractCompany($title, $description);

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

    private function extractCompany(string $title, string $description): ?string
    {
        if (preg_match('/^([^:]+):/', $title, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/Posted by ([^ ]+)/', $description, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }
}
