<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Lead;
use App\Services\AutomationService;
use Illuminate\Support\Facades\Cache;

class LeadObserver
{
    public function __construct(
        private readonly AutomationService $automationService
    ) {}

    public function created(Lead $lead): void
    {
        $cacheKey = "automation_lead_imported_{$lead->id}";

        if (Cache::has($cacheKey)) {
            return;
        }

        Cache::put($cacheKey, true, 60);

        $this->automationService->onLeadImported($lead);
    }
}
