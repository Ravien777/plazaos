<?php

namespace Tests\Unit\Models;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_trialing_returns_true_when_status_is_trialing(): void
    {
        $subscription = Subscription::factory()->trialing()->create();

        $this->assertTrue($subscription->isTrialing());
        $this->assertTrue($subscription->onTrial());
    }

    public function test_is_active_returns_true_for_active_statuses(): void
    {
        $active = Subscription::factory()->create(['status' => 'active']);
        $trialing = Subscription::factory()->trialing()->create();
        $pastDue = Subscription::factory()->create(['status' => 'past_due']);

        $this->assertTrue($active->isActive());
        $this->assertTrue($trialing->isActive());
        $this->assertTrue($pastDue->isActive());
    }

    public function test_is_active_returns_false_for_canceled(): void
    {
        $subscription = Subscription::factory()->canceled()->create();

        $this->assertFalse($subscription->isActive());
        $this->assertTrue($subscription->isCanceled());
    }

    public function test_on_trial_returns_false_when_trial_expired(): void
    {
        $subscription = Subscription::factory()->trialing()->create([
            'trial_ends_at' => now()->subDay(),
        ]);

        $this->assertFalse($subscription->onTrial());
    }

    public function test_trial_days_remaining_returns_correct_count(): void
    {
        $subscription = Subscription::factory()->trialing()->create([
            'trial_ends_at' => now()->addDays(5),
        ]);

        $this->assertEquals(5, $subscription->trialDaysRemaining());
    }

    public function test_trial_days_remaining_returns_zero_for_expired(): void
    {
        $subscription = Subscription::factory()->trialing()->create([
            'trial_ends_at' => now()->subDay(),
        ]);

        $this->assertEquals(0, $subscription->trialDaysRemaining());
    }

    public function test_subscription_belongs_to_team_and_plan(): void
    {
        $team = Team::factory()->create();
        $plan = Plan::factory()->pro()->create();

        $subscription = Subscription::factory()->create([
            'team_id' => $team->id,
            'plan_id' => $plan->id,
        ]);

        $this->assertInstanceOf(Team::class, $subscription->team);
        $this->assertInstanceOf(Plan::class, $subscription->plan);
        $this->assertEquals($team->id, $subscription->team->id);
        $this->assertEquals($plan->id, $subscription->plan->id);
    }
}
