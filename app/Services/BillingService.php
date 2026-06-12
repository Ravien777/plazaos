<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Team;
use Stripe\StripeClient;

class BillingService
{
    private ?StripeClient $stripe = null;

    private function client(): StripeClient
    {
        if ($this->stripe === null) {
            $secret = config('billing.stripe.secret');

            if (!$secret) {
                throw new \RuntimeException('Stripe secret key is not configured.');
            }

            $this->stripe = new StripeClient($secret);
        }

        return $this->stripe;
    }

    public function createCheckoutSession(Team $team, Plan $plan): string
    {
        $customerId = $this->ensureCustomer($team);

        $checkout = $this->client()->checkout->sessions->create([
            'customer' => $customerId,
            'mode' => 'subscription',
            'line_items' => [[
                'price' => $plan->stripe_price_id,
                'quantity' => 1,
            ]],
            'metadata' => [
                'team_id' => $team->id,
                'plan_id' => $plan->id,
            ],
            'subscription_data' => [
                'metadata' => [
                    'team_id' => $team->id,
                    'plan_id' => $plan->id,
                ],
            ],
            'success_url' => route('billing.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('billing.cancel'),
        ]);

        return $checkout->url;
    }

    public function createCustomerPortalSession(Team $team): string
    {
        $customerId = $this->ensureCustomer($team);

        $session = $this->client()->billingPortal->sessions->create([
            'customer' => $customerId,
            'return_url' => route('settings.billing'),
        ]);

        return $session->url;
    }

    public function syncSubscriptionFromStripe(string $stripeSubscriptionId): void
    {
        $stripeSub = $this->client()->subscriptions->retrieve($stripeSubscriptionId, [
            'expand' => ['customer', 'items.data.price'],
        ]);

        $teamId = $stripeSub->metadata['team_id'] ?? null;
        $planId = $stripeSub->metadata['plan_id'] ?? null;

        if (!$teamId || !$planId) {
            $subscription = Subscription::where('stripe_subscription_id', $stripeSubscriptionId)->first();
            if (!$subscription) {
                return;
            }
            $teamId = $subscription->team_id;
            $planId = $subscription->plan_id;
        }

        $plan = Plan::find($planId);
        if (!$plan) {
            return;
        }

        $status = $stripeSub->status;

        $data = [
            'plan_id' => $plan->id,
            'stripe_subscription_id' => $stripeSub->id,
            'stripe_customer_id' => $stripeSub->customer->id ?? null,
            'status' => $status,
            'current_period_ends_at' => $stripeSub->current_period_end
                ? now()->timestamp($stripeSub->current_period_end)
                : null,
            'trial_ends_at' => $stripeSub->trial_end
                ? now()->timestamp($stripeSub->trial_end)
                : null,
            'canceled_at' => $stripeSub->canceled_at
                ? now()->timestamp($stripeSub->canceled_at)
                : null,
            'ended_at' => $stripeSub->ended_at
                ? now()->timestamp($stripeSub->ended_at)
                : null,
        ];

        if ($status === 'canceled' || $status === 'incomplete_expired') {
            $data['status'] = 'canceled';
            $data['ended_at'] = now();
        }

        Subscription::updateOrCreate(
            ['team_id' => $teamId],
            $data,
        );
    }

    public function cancelSubscription(Team $team): void
    {
        $subscription = $team->subscription;

        if (!$subscription) {
            return;
        }

        if ($subscription->stripe_subscription_id) {
            $this->client()->subscriptions->cancel($subscription->stripe_subscription_id);
        }

        $subscription->update([
            'status' => 'canceled',
            'canceled_at' => now(),
        ]);
    }

    public function resumeSubscription(Team $team): void
    {
        $subscription = $team->subscription;

        if (!$subscription || !$subscription->stripe_subscription_id) {
            return;
        }

        $this->client()->subscriptions->update($subscription->stripe_subscription_id, [
            'cancel_at_period_end' => false,
        ]);

        $subscription->update([
            'canceled_at' => null,
        ]);
    }

    public function updateSubscriptionPlan(Team $team, string $stripePriceId): void
    {
        $subscription = $team->subscription;

        if (!$subscription || !$subscription->stripe_subscription_id) {
            return;
        }

        $plan = Plan::where('stripe_price_id', $stripePriceId)->first();

        if (!$plan) {
            return;
        }

        $this->client()->subscriptions->update($subscription->stripe_subscription_id, [
            'items' => [[
                'id' => $subscription->stripe_subscription_id,
                'price' => $stripePriceId,
            ]],
        ]);

        $subscription->update(['plan_id' => $plan->id]);
    }

    public function canAccessFeature(Team $team, string $feature): bool
    {
        $plan = $this->currentPlan($team);

        return $plan->hasFeature($feature);
    }

    public function currentPlan(Team $team): Plan
    {
        if ($team->subscription && $team->subscription->plan) {
            return $team->subscription->plan;
        }

        return Plan::where('slug', 'free')->firstOrFail();
    }

    public function isTrialActive(Team $team): bool
    {
        return $team->subscription !== null && $team->subscription->onTrial();
    }

    public function hasExceededSeats(Team $team): bool
    {
        $plan = $this->currentPlan($team);

        if ($plan->max_users === null) {
            return false;
        }

        return $team->members()->count() >= $plan->max_users;
    }

    public function createOrUpdateCustomer(Team $team): string
    {
        return $this->ensureCustomer($team);
    }

    public function retrieveProductPrices(): array
    {
        $prices = $this->client()->prices->all([
            'active' => true,
            'expand' => ['data.product'],
        ]);

        return $prices->data;
    }

    public function getInvoices(Team $team, int $limit = 10): array
    {
        $customerId = $team->subscription?->stripe_customer_id;

        if (!$customerId) {
            return [];
        }

        $invoices = $this->client()->invoices->all([
            'customer' => $customerId,
            'limit' => $limit,
        ]);

        return array_map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'amount_due' => $invoice->amount_due,
                'amount_paid' => $invoice->amount_paid,
                'status' => $invoice->status,
                'created' => $invoice->created,
                'pdf_url' => $invoice->invoice_pdf,
                'paid_at' => $invoice->status_transactions?->first()?->created ?? null,
            ];
        }, $invoices->data);
    }

    public function handleTrialEnd(Team $team): void
    {
        $freePlan = Plan::where('slug', 'free')->firstOrFail();

        $team->subscription?->update([
            'plan_id' => $freePlan->id,
            'status' => 'canceled',
            'ended_at' => now(),
        ]);
    }

    private function ensureCustomer(Team $team): string
    {
        $existingCustomerId = $team->subscription?->stripe_customer_id;

        if ($existingCustomerId) {
            try {
                $this->client()->customers->retrieve($existingCustomerId);
                return $existingCustomerId;
            } catch (\Exception $e) {
                // Customer was deleted in Stripe; create a new one
            }
        }

        $owner = $team->owner;

        $customer = $this->client()->customers->create([
            'name' => $team->name,
            'email' => $owner?->email,
            'metadata' => [
                'team_id' => $team->id,
            ],
        ]);

        if ($team->subscription) {
            $team->subscription->update(['stripe_customer_id' => $customer->id]);
        }

        return $customer->id;
    }
}
