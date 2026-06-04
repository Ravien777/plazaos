<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional(0.7)->paragraph(),
            'status' => TaskStatus::Todo,
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'order' => fake()->numberBetween(0, 50),
            'assignee_id' => User::factory(),
            'created_by' => User::factory(),
        ];
    }
}
