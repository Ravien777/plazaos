<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Lead;
use App\Services\AutomationService;
use Illuminate\Console\Command;

class CheckInactiveLeads extends Command
{
    protected $signature = 'automations:check-inactive-leads';

    protected $description = 'Check for leads inactive for 7+ days and trigger automation.';

    public function handle(AutomationService $automationService): int
    {
        $leads = Lead::where('status', 'new')
            ->whereNull('converted_at')
            ->where(function ($query) {
                $query->where('last_contacted_at', '<', now()->subDays(7))
                    ->orWhereNull('last_contacted_at');
            })
            ->get();

        if ($leads->isEmpty()) {
            $this->info('No inactive leads found.');
            return self::SUCCESS;
        }

        foreach ($leads as $lead) {
            $automationService->onLeadInactive($lead);
            $this->line("Triggered inactive automation for lead: {$lead->company_name}");
        }

        $this->info("Processed {$leads->count()} inactive lead(s).");

        return self::SUCCESS;
    }
}
