<?php

namespace Database\Factories;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'contact_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'website' => fake()->url(),
            'industry' => fake()->word(),
            'city' => fake()->city(),
            'country' => fake()->country(),
            'source' => fake()->word(),
            'status' => LeadStatus::New,
            'notes' => fake()->sentence(),
            'last_contacted_at' => fake()->optional(0.5)->dateTime(),
        ];
    }

    public function won(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => LeadStatus::Won,
            'converted_at' => now(),
        ]);
    }
}
