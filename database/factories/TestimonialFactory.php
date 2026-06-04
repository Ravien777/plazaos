<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Client;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'project_id' => null,
            'rating' => fake()->numberBetween(1, 5),
            'content' => fake()->optional(0.7)->sentence(),
            'review_token' => (string) Str::uuid(),
            'is_approved' => fake()->boolean(60),
            'submitted_at' => fake()->optional(0.8)->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attrs) => [
            'is_approved' => true,
            'rating' => 5,
            'submitted_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attrs) => [
            'is_approved' => false,
            'submitted_at' => null,
        ]);
    }
}
