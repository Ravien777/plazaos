<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Plan;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBillingAccess
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $team = $request->user()?->team;

        if (!$team) {
            return redirect()->route('team.create')
                ->with('info', 'Create a team to get started.');
        }

        $subscription = $team->subscription;

        $plan = $subscription?->plan ?? Plan::where('slug', 'free')->first();

        if (!$plan || !$plan->hasFeature($feature)) {
            return redirect()->route('settings.billing')
                ->with('error', 'Upgrade your plan to access this feature.');
        }

        return $next($request);
    }
}
