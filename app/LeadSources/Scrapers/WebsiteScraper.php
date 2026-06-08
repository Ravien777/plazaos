<?php

declare(strict_types=1);

namespace App\LeadSources\Scrapers;

use App\LeadSources\Concerns\DeduplicatesLeads;
use App\LeadSources\Contracts\ScraperInterface;
use App\Models\LeadSource;
use Illuminate\Support\Facades\Http;

class WebsiteScraper implements ScraperInterface
{
    use DeduplicatesLeads;

    public function scrape(LeadSource $source): int
    {
        $config = $source->config ?? [];
        $url = $config['url'] ?? null;

        if (! $url) {
            activity()->log($source, 'lead_source.scrape_error', "No URL configured for website source {$source->name}.");

            return 0;
        }

        $response = Http::timeout(30)->get($url);

        if (! $response->successful()) {
            activity()->log($source, 'lead_source.scrape_error', "HTTP {$response->status()} fetching {$url}.");

            return 0;
        }

        $body = $response->body();
        $contentType = $response->header('Content-Type', '');

        $items = [];

        if (str_contains($contentType, 'application/json')) {
            $items = $response->json();
        } elseif (str_contains($contentType, 'text/xml') || str_contains($contentType, 'application/xml') || str_starts_with(trim($body), '<')) {
            $items = $this->parseXmlLeads($body);
        } else {
            $items = $this->parseJsonLines($body);
        }

        if (! is_array($items)) {
            $items = [];
        }

        if (isset($items['data'])) {
            $items = $items['data'];
        } elseif (isset($items['results'])) {
            $items = $items['results'];
        } elseif (isset($items['leads'])) {
            $items = $items['leads'];
        } elseif (isset($items['items'])) {
            $items = $items['items'];
        }

        if (! is_array($items) || empty($items)) {
            return 0;
        }

        $created = 0;

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            if ($this->createLead($item, $source->name)) {
                $created++;
            }
        }

        return $created;
    }

    private function parseXmlLeads(string $body): array
    {
        $items = [];

        try {
            $xml = simplexml_load_string($body);

            if ($xml === false) {
                return [];
            }

            if ($xml->channel && $xml->channel->item) {
                foreach ($xml->channel->item as $item) {
                    $items[] = [
                        'name' => (string) ($item->title ?? ''),
                        'email' => (string) ($item->email ?? (string) $item->author ?? ''),
                        'company_name' => (string) $item->children('http://search.yahoo.com/mrss/')->company ?? null,
                    ];
                }
            } else {
                foreach ($xml->children() as $child) {
                    $item = json_decode(json_encode($child), true);
                    if (is_array($item)) {
                        $items[] = $item;
                    }
                }
            }
        } catch (\Exception) {
            return [];
        }

        return $items;
    }

    private function parseJsonLines(string $body): array
    {
        $lines = explode("\n", trim($body));
        $items = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            $decoded = json_decode($line, true);

            if (is_array($decoded)) {
                $items[] = $decoded;
            }
        }

        return $items;
    }
}
