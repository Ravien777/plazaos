<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Subscription> */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'plan_id' => Plan::factory(),
            'stripe_subscription_id' => 'sub_' . fake()->uuid(),
            'stripe_customer_id' => 'cus_' . fake()->uuid(),
            'status' => 'active',
            'trial_ends_at' => null,
            'current_period_ends_at' => now()->addMonth(),
            'seats' => 1,
        ];
    }

    public function trialing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'trialing',
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'canceled',
            'canceled_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
        ]);
    }
}
