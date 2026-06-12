<?php

namespace Tests\Unit\Models;

use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_free_returns_true_when_price_is_zero(): void
    {
        $plan = Plan::factory()->create(['monthly_price_cents' => 0]);

        $this->assertTrue($plan->isFree());
        $this->assertFalse($plan->isPaid());
    }

    public function test_is_paid_returns_true_when_price_is_positive(): void
    {
        $plan = Plan::factory()->pro()->create();

        $this->assertTrue($plan->isPaid());
        $this->assertFalse($plan->isFree());
    }

    public function test_has_feature_checks_features_array(): void
    {
        $plan = Plan::factory()->create(['features' => ['leads', 'clients', 'projects']]);

        $this->assertTrue($plan->hasFeature('leads'));
        $this->assertTrue($plan->hasFeature('projects'));
        $this->assertFalse($plan->hasFeature('api'));
    }

    public function test_price_in_dollars_formats_correctly(): void
    {
        $plan = Plan::factory()->team()->create(['monthly_price_cents' => 14900]);

        $this->assertEquals('149.00', $plan->priceInDollars());
    }

    public function test_pro_factory_creates_pro_plan(): void
    {
        $plan = Plan::factory()->pro()->create();

        $this->assertEquals('pro', $plan->slug);
        $this->assertEquals(4900, $plan->monthly_price_cents);
        $this->assertEquals(5, $plan->max_users);
    }

    public function test_team_factory_creates_team_plan(): void
    {
        $plan = Plan::factory()->team()->create();

        $this->assertEquals('team', $plan->slug);
        $this->assertEquals(14900, $plan->monthly_price_cents);
        $this->assertEquals(20, $plan->max_users);
    }
}
