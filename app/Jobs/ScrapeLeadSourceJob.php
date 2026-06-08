<?php

declare(strict_types=1);

namespace App\Jobs;

use App\LeadSources\ScraperFactory;
use App\Models\LeadSource;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ScrapeLeadSourceJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly LeadSource $source
    ) {}

    public function handle(ScraperFactory $factory): void
    {
        $this->source->update(['last_run_at' => now()]);

        activity()->log(
            $this->source,
            'lead_source.scrape_started',
            "Scraping {$this->source->name} started."
        );

        $created = $factory->driver($this->source->type)->scrape($this->source);

        activity()->log(
            $this->source,
            'lead_source.scrape_completed',
            "Scraping {$this->source->name} completed. Created {$created} lead(s)."
        );
    }
}
