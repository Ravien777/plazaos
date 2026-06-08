<?php

declare(strict_types=1);

namespace App\LeadSources\Scrapers;

use App\LeadSources\Concerns\DeduplicatesLeads;
use App\LeadSources\Contracts\ScraperInterface;
use App\Models\LeadSource;
use Illuminate\Support\Facades\Http;

class LinkedInScraper implements ScraperInterface
{
    use DeduplicatesLeads;

    public function scrape(LeadSource $source): int
    {
        $config = $source->config ?? [];
        $query = $config['search_query'] ?? $source->name;
        $location = $config['location'] ?? '';

        $keywords = urlencode($query);
        $loc = $location ? '&location=' . urlencode($location) : '';

        $url = "https://www.linkedin.com/search/results/people/?keywords={$keywords}{$loc}";

        $response = Http::timeout(30)->withOptions([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
            ],
        ])->get($url);

        if (! $response->successful()) {
            activity()->log($source, 'lead_source.scrape_error', "LinkedIn returned HTTP {$response->status()}. LinkedIn may require authentication.");

            return 0;
        }

        $body = $response->body();
        $items = $this->extractSearchResults($body);

        if (empty($items)) {
            activity()->log($source, 'lead_source.scrape_info', "No results extracted from LinkedIn. The page structure may have changed.");

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

    private function extractSearchResults(string $html): array
    {
        $items = [];

        if (preg_match_all('/data-basic-profile-card="([^"]+)"/', $html, $matches)) {
            foreach ($matches[1] as $json) {
                $data = json_decode(html_entity_decode($json), true);

                if (is_array($data)) {
                    $items[] = [
                        'contact_name' => $data['name'] ?? null,
                        'company_name' => $data['company'] ?? null,
                        'city' => $data['location'] ?? null,
                    ];
                }
            }

            if (! empty($items)) {
                return $items;
            }
        }

        if (preg_match_all('/<a[^>]*href="\/in\/[^"]*"[^>]*>([^<]+)<\/a>/i', $html, $nameMatches)) {
            foreach ($nameMatches[1] as $i => $name) {
                $item = ['contact_name' => trim($name)];

                if (preg_match('/<span[^>]*class="[^"]*company[^"]*"[^>]*>([^<]+)<\/span>/i', $html, $cMatches)) {
                    $item['company_name'] = trim($cMatches[1]);
                }

                $items[] = $item;
            }
        }

        return $items;
    }
}
