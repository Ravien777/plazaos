<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadSource;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class ScrapeLeadSourceJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly LeadSource $source
    ) {}

    public function handle(): void
    {
        $this->source->update(['last_run_at' => now()]);

        activity()->log(
            $this->source,
            'lead_source.scrape_started',
            "Scraping {$this->source->name} started."
        );

        $created = match ($this->source->type->value) {
            'website' => $this->scrapeWebsite(),
            default => $this->notImplemented(),
        };

        activity()->log(
            $this->source,
            'lead_source.scrape_completed',
            "Scraping {$this->source->name} completed. Created {$created} lead(s)."
        );
    }

    private function scrapeWebsite(): int
    {
        $config = $this->source->config ?? [];
        $url = $config['url'] ?? null;

        if (!$url) {
            activity()->log($this->source, 'lead_source.scrape_error', "No URL configured for website source {$this->source->name}.");

            return 0;
        }

        $response = Http::timeout(30)->get($url);

        if (!$response->successful()) {
            activity()->log($this->source, 'lead_source.scrape_error', "HTTP {$response->status()} fetching {$url}.");

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

        if (!is_array($items)) {
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

        if (!is_array($items) || empty($items)) {
            return 0;
        }

        $created = 0;

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $email = $item['email'] ?? null;
            $contactName = $item['name'] ?? $item['contact_name'] ?? $item['full_name'] ?? null;
            $website = $item['website'] ?? null;
            $companyName = $item['company_name'] ?? $item['company'] ?? null;

            if ($email) {
                $exists = Lead::where('email', $email)->exists();

                if ($exists) {
                    continue;
                }
            }

            if ($website && $companyName) {
                $exists = Lead::where('website', $website)
                    ->where('company_name', $companyName)
                    ->exists();

                if ($exists) {
                    continue;
                }
            }

            if ($website && !$email) {
                $exists = Lead::where('website', $website)->exists();

                if ($exists) {
                    continue;
                }
            }

            if ($companyName && !$email && !$website) {
                $exists = Lead::where('company_name', $companyName)->exists();

                if ($exists) {
                    continue;
                }
            }

            if (!$email && !$contactName) {
                continue;
            }

            $data = [
                'contact_name' => $contactName,
                'source' => $this->source->name,
                'status' => LeadStatus::New,
            ];

            if ($companyName) {
                $data['company_name'] = $companyName;
            }

            if ($email) {
                $data['email'] = $email;
            }

            foreach (['phone', 'website', 'industry', 'city', 'country'] as $field) {
                if (isset($item[$field])) {
                    $data[$field] = $item[$field];
                }
            }

            Lead::create($data);

            $created++;
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

    private function notImplemented(): int
    {
        activity()->log(
            $this->source,
            'lead_source.scrape_skipped',
            "Scraping not implemented for source type {$this->source->type->value} on {$this->source->name}."
        );

        return 0;
    }
}
