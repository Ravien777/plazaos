<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlanSlug;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Plan> */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->word(),
            'name' => $this->faker->word(),
            'description' => 'Test plan',
            'monthly_price_cents' => 0,
            'max_users' => 1,
            'features' => ['leads'],
            'is_active' => true,
            'sort_order' => 1,
        ];
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'slug' => PlanSlug::Free->value,
            'name' => 'Free',
            'description' => 'Free plan',
            'monthly_price_cents' => 0,
            'max_users' => 1,
            'features' => ['leads'],
        ]);
    }

    public function pro(): static
    {
        return $this->state(fn (array $attributes) => [
            'slug' => PlanSlug::Pro->value,
            'name' => 'Pro',
            'description' => 'Pro plan',
            'monthly_price_cents' => 4900,
            'max_users' => 5,
            'features' => ['leads', 'clients', 'projects', 'email'],
        ]);
    }

    public function team(): static
    {
        return $this->state(fn (array $attributes) => [
            'slug' => PlanSlug::Team->value,
            'name' => 'Team',
            'description' => 'Team plan',
            'monthly_price_cents' => 14900,
            'max_users' => 20,
            'features' => ['leads', 'clients', 'projects', 'email', 'api'],
        ]);
    }
}
