<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

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
            'status' => 'active',
            'notes' => fake()->sentence(),
            'last_contacted_at' => fake()->optional(0.5)->dateTime(),
        ];
    }
}
