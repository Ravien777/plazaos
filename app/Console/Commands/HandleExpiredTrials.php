<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Console\Command;

class HandleExpiredTrials extends Command
{
    protected $signature = 'plazaos:check-trials';

    protected $description = 'Downgrade expired trials to the free plan.';

    public function handle(): int
    {
        $expiredTrials = Subscription::where('status', 'trialing')
            ->where('trial_ends_at', '<=', now())
            ->get();

        if ($expiredTrials->isEmpty()) {
            $this->info('No expired trials found.');

            return self::SUCCESS;
        }

        $freePlan = Plan::where('slug', 'free')->firstOrFail();

        foreach ($expiredTrials as $subscription) {
            $subscription->update([
                'plan_id' => $freePlan->id,
                'status' => 'canceled',
                'ended_at' => now(),
            ]);

            $this->info("Trial expired for team {$subscription->team_id}");
        }

        return self::SUCCESS;
    }
}
