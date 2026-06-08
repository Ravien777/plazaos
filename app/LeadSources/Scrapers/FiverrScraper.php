<?php

declare(strict_types=1);

namespace App\LeadSources\Scrapers;

use App\LeadSources\Concerns\DeduplicatesLeads;
use App\LeadSources\Contracts\ScraperInterface;
use App\Models\LeadSource;
use Illuminate\Support\Facades\Http;

class FiverrScraper implements ScraperInterface
{
    use DeduplicatesLeads;

    public function scrape(LeadSource $source): int
    {
        $config = $source->config ?? [];
        $query = $config['search_query'] ?? $source->name;

        $url = 'https://www.fiverr.com/search/gigs?query=' . urlencode($query);

        $response = Http::timeout(30)->withOptions([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
            ],
        ])->get($url);

        if (! $response->successful()) {
            activity()->log($source, 'lead_source.scrape_error', "Fiverr returned HTTP {$response->status()}.");

            return 0;
        }

        $body = $response->body();
        $items = $this->extractGigs($body);

        if (empty($items)) {
            activity()->log($source, 'lead_source.scrape_info', "No gigs extracted from Fiverr. The page structure may have changed.");

            return 0;
        }

        $created = 0;

        foreach ($items as $item) {
            $lead = $this->createLead($item, $source->name);

            if ($lead) {
                $created++;
            }
        }

        return $created;
    }

    private function extractGigs(string $html): array
    {
        $items = [];

        if (preg_match_all('/<script[^>]*id="__NEXT_DATA__"[^>]*>(.*?)<\/script>/s', $html, $jsonMatches)) {
            $json = json_decode($jsonMatches[1][0] ?? '{}', true);

            if ($json && isset($json['props']['pageProps']['gigs'])) {
                foreach ($json['props']['pageProps']['gigs'] as $gig) {
                    $items[] = [
                        'contact_name' => $gig['title'] ?? null,
                        'company_name' => $gig['seller']['username'] ?? $gig['seller']['display_name'] ?? null,
                        'website' => 'https://www.fiverr.com' . ($gig['url'] ?? ''),
                    ];
                }
            }

            if (! empty($items)) {
                return $items;
            }
        }

        if (preg_match_all('/class="[^"]*gig-title[^"]*"[^>]*>([^<]+)<\/a>/i', $html, $titleMatches)) {
            foreach ($titleMatches[1] as $title) {
                $items[] = ['contact_name' => trim($title)];
            }
        }

        return $items;
    }
}
