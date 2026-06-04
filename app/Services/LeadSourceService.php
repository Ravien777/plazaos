<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LeadSource;
use Illuminate\Pagination\LengthAwarePaginator;

class LeadSourceService
{
    public function list(): LengthAwarePaginator
    {
        return LeadSource::orderBy('created_at', 'desc')->paginate(25);
    }

    public function create(array $data): LeadSource
    {
        $source = LeadSource::create($data);

        activity()->log($source, 'lead_source.created', "Lead source {$source->name} was created.");

        return $source;
    }

    public function update(LeadSource $source, array $data): LeadSource
    {
        $source->update($data);

        activity()->log($source, 'lead_source.updated', "Lead source {$source->name} was updated.");

        return $source;
    }

    public function delete(LeadSource $source): void
    {
        activity()->log($source, 'lead_source.deleted', "Lead source {$source->name} was deleted.");

        $source->delete();
    }

    public function run(LeadSource $source): void
    {
        $source->update(['last_run_at' => now()]);

        \App\Jobs\ScrapeLeadSourceJob::dispatch($source);
    }
}
