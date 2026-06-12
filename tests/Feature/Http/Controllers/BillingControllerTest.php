<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class BillingControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Team $team;
    private Plan $proPlan;
    private Plan $teamPlan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instance(
            BillingService::class,
            Mockery::mock(BillingService::class, function ($mock) {
                $mock->makePartial();
                $mock->shouldReceive('createCheckoutSession')->zeroOrMoreTimes()->andReturn('https://checkout.stripe.com/test');
                $mock->shouldReceive('createCustomerPortalSession')->zeroOrMoreTimes()->andReturn('https://billing.stripe.com/test');
            })
        );

        $this->proPlan = Plan::factory()->pro()->create(['stripe_price_id' => 'price_pro_test']);
        $this->teamPlan = Plan::factory()->team()->create(['stripe_price_id' => 'price_team_test']);

        $this->user = User::factory()->create();
        $this->team = Team::factory()->create([
            'owner_id' => $this->user->id,
        ]);
        $this->user->update([
            'team_id' => $this->team->id,
            'role' => 'owner',
        ]);
    }

    public function test_index_shows_billing_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('settings.billing'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Settings/Billing')
            ->has('currentPlan')
            ->has('plans', 2)
        );
    }

    public function test_index_redirects_if_no_team(): void
    {
        $user = User::factory()->create(['team_id' => null]);
        $this->actingAs($user);

        $response = $this->get(route('settings.billing'));

        $response->assertRedirect(route('team.create'));
    }

    public function test_checkout_redirects_to_stripe(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('billing.checkout', $this->proPlan));

        $response->assertRedirect();
    }

    public function test_checkout_fails_without_price_id(): void
    {
        $this->actingAs($this->user);
        $plan = Plan::factory()->create(['stripe_price_id' => null]);

        $response = $this->post(route('billing.checkout', $plan));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_success_redirects_to_billing(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('billing.success'));

        $response->assertRedirect(route('settings.billing'));
        $response->assertSessionHas('success');
    }

    public function test_cancel_redirects_to_billing(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('billing.cancel'));

        $response->assertRedirect(route('settings.billing'));
        $response->assertSessionHas('info');
    }

    public function test_cancel_subscription_downgrades_plan(): void
    {
        $this->actingAs($this->user);

        $freePlan = Plan::factory()->create(['slug' => 'free', 'monthly_price_cents' => 0]);

        $subscription = Subscription::factory()->create([
            'team_id' => $this->team->id,
            'plan_id' => $this->proPlan->id,
            'stripe_subscription_id' => null,
        ]);

        $response = $this->post(route('billing.cancel-subscription'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => 'canceled',
        ]);
    }

    public function test_pricing_page_shows_plans(): void
    {
        $response = $this->get(route('pricing'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Pricing')
            ->has('plans')
        );
    }
}
