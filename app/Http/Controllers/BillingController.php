<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function __construct(
        private readonly BillingService $billingService
    ) {}

    public function index(): Response|RedirectResponse
    {
        $user = auth()->user();
        $team = $user->team;

        if ($team === null) {
            return redirect()->route('team.create');
        }

        $subscription = $team->subscription;

        return Inertia::render('Settings/Billing', [
            'currentPlan' => $subscription?->plan ?? Plan::where('slug', 'free')->first(),
            'subscription' => $subscription,
            'plans' => Plan::where('is_active', true)->orderBy('sort_order')->get(),
            'seatCount' => $team->members()->count(),
            'invoices' => $this->billingService->getInvoices($team, 10),
        ]);
    }

    public function checkout(Request $request, Plan $plan): RedirectResponse
    {
        $user = $request->user();
        $team = $user->team;

        if (!$team) {
            return redirect()->route('team.create');
        }

        if (!$plan->stripe_price_id) {
            return redirect()->back()->with('error', 'This plan is not available for checkout.');
        }

        $url = $this->billingService->createCheckoutSession($team, $plan);

        return Inertia::location($url);
    }

    public function portal(Request $request): RedirectResponse
    {
        $team = $request->user()->team;

        if (!$team) {
            return redirect()->route('team.create');
        }

        $url = $this->billingService->createCustomerPortalSession($team);

        return Inertia::location($url);
    }

    public function success(Request $request): RedirectResponse
    {
        return redirect()->route('settings.billing')
            ->with('success', 'Subscription activated successfully. Welcome aboard!');
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('settings.billing')
            ->with('info', 'Checkout was cancelled. No charges were made.');
    }

    public function cancelSubscription(Request $request): RedirectResponse
    {
        $team = $request->user()->team;

        if (!$team) {
            return redirect()->route('team.create');
        }

        $this->billingService->cancelSubscription($team);

        return redirect()->back()->with('success', 'Subscription cancelled.');
    }

    public function resumeSubscription(Request $request): RedirectResponse
    {
        $team = $request->user()->team;

        if (!$team) {
            return redirect()->route('team.create');
        }

        $this->billingService->resumeSubscription($team);

        return redirect()->back()->with('success', 'Subscription resumed.');
    }
}
