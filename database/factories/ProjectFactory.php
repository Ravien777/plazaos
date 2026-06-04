<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'status' => ProjectStatus::Discovery,
            'budget' => fake()->randomFloat(2, 1000, 100000),
            'progress_percentage' => fake()->numberBetween(0, 100),
            'start_date' => fake()->date(),
            'due_date' => fake()->optional(0.5)->date(),
        ];
    }
}
