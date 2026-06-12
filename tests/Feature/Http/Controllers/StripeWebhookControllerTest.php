<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_rejects_invalid_signature(): void
    {
        config(['billing.stripe.webhook_secret' => 'whsec_test']);

        $response = $this->postJson('/webhooks/stripe', [], [
            'Stripe-Signature' => 'invalid',
        ]);

        $response->assertStatus(401);
    }

    public function test_webhook_returns_500_when_secret_not_configured(): void
    {
        config(['billing.stripe.webhook_secret' => null]);

        $response = $this->postJson('/webhooks/stripe', [
            'type' => 'customer.subscription.created',
            'data' => ['object' => ['id' => 'sub_test']],
        ], [
            'Stripe-Signature' => 't=123,v1=test',
        ]);

        $response->assertStatus(500);
    }

    public function test_cancel_subscription_creates_subscription(): void
    {
        $this->actingAs(User::factory()->create());

        $freePlan = Plan::factory()->create(['slug' => 'free', 'monthly_price_cents' => 0]);
        $proPlan = Plan::factory()->pro()->create();
        $team = Team::factory()->create(['owner_id' => auth()->id()]);
        auth()->user()->update(['team_id' => $team->id, 'role' => 'owner']);

        $subscription = Subscription::factory()->create([
            'team_id' => $team->id,
            'plan_id' => $proPlan->id,
            'status' => 'active',
            'stripe_subscription_id' => null,
        ]);

        $this->post(route('billing.cancel-subscription'));

        $subscription->refresh();

        $this->assertEquals('canceled', $subscription->status);
    }
}
