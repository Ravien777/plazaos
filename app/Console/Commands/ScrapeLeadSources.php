<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ScrapeLeadSourceJob;
use App\Models\LeadSource;
use Illuminate\Console\Command;

class ScrapeLeadSources extends Command
{
    protected $signature = 'leads:scrape';

    protected $description = 'Scrape active lead sources that are due based on their frequency';

    public function handle(): void
    {
        $sources = LeadSource::where('is_active', true)->get();

        $dispatched = 0;

        foreach ($sources as $source) {
            if (!$this->isDue($source)) {
                continue;
            }

            ScrapeLeadSourceJob::dispatch($source);
            $dispatched++;
        }

        $this->info("Dispatched {$dispatched} scrape job(s).");
    }

    private function isDue(LeadSource $source): bool
    {
        return match ($source->frequency) {
            'hourly' => $source->last_run_at === null || $source->last_run_at->lt(now()->subHour()),
            'daily' => $source->last_run_at === null || $source->last_run_at->lt(now()->subDay()),
            'weekly' => $source->last_run_at === null || $source->last_run_at->lt(now()->subWeek()),
            default => false,
        };
    }
}
