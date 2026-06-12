<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Services\BillingService;
use Illuminate\Support\Facades\Log;

class HandleStripeWebhookAction
{
    public function __construct(
        private readonly BillingService $billingService
    ) {}

    public function execute(string $type, array $payload): void
    {
        match ($type) {
            'customer.subscription.created',
            'customer.subscription.updated' => $this->syncSubscription($payload),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($payload),
            'invoice.paid' => $this->handleInvoicePaid($payload),
            'invoice.payment_failed' => $this->handlePaymentFailed($payload),
            'customer.subscription.trial_will_end' => $this->handleTrialWillEnd($payload),
            'checkout.session.completed' => $this->handleCheckoutCompleted($payload),
            default => Log::info('Unhandled Stripe webhook type', ['type' => $type]),
        };
    }

    private function syncSubscription(array $payload): void
    {
        $subscriptionId = $payload['data']['object']['id'] ?? null;

        if ($subscriptionId) {
            $this->billingService->syncSubscriptionFromStripe($subscriptionId);
        }
    }

    private function handleSubscriptionDeleted(array $payload): void
    {
        $subscriptionId = $payload['data']['object']['id'] ?? null;

        if (!$subscriptionId) {
            return;
        }

        $subscription = Subscription::where('stripe_subscription_id', $subscriptionId)->first();

        if ($subscription) {
            $freePlan = \App\Models\Plan::where('slug', 'free')->first();
            $subscription->update([
                'plan_id' => $freePlan?->id ?? $subscription->plan_id,
                'status' => SubscriptionStatus::Canceled->value,
                'canceled_at' => now(),
                'ended_at' => now(),
            ]);
        }
    }

    private function handleInvoicePaid(array $payload): void
    {
        $subscriptionId = $payload['data']['object']['subscription'] ?? null;

        if ($subscriptionId) {
            $this->billingService->syncSubscriptionFromStripe($subscriptionId);
        }
    }

    private function handlePaymentFailed(array $payload): void
    {
        $subscriptionId = $payload['data']['object']['subscription'] ?? null;

        if (!$subscriptionId) {
            return;
        }

        $subscription = Subscription::where('stripe_subscription_id', $subscriptionId)->first();

        if ($subscription) {
            $subscription->update([
                'status' => SubscriptionStatus::PastDue->value,
            ]);
        }
    }

    private function handleTrialWillEnd(array $payload): void
    {
        $subscriptionId = $payload['data']['object']['id'] ?? null;

        if ($subscriptionId) {
            $subscription = Subscription::where('stripe_subscription_id', $subscriptionId)->first();

            if ($subscription && $subscription->team) {
                $owner = $subscription->team->owner;

                if ($owner) {
                    $owner->notify(new \App\Notifications\TrialEnding(
                        $subscription->trialDaysRemaining()
                    ));
                }
            }
        }
    }

    private function handleCheckoutCompleted(array $payload): void
    {
        $session = $payload['data']['object'] ?? [];
        $subscriptionId = $session['subscription'] ?? null;

        if ($subscriptionId) {
            $this->billingService->syncSubscriptionFromStripe($subscriptionId);
        }
    }
}
