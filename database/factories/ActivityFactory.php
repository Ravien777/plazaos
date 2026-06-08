<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition(): array
    {
        return [
            'event' => fake()->word(),
            'description' => fake()->sentence(),
            'metadata' => [],
            'subject_type' => Lead::class,
            'subject_id' => Lead::factory(),
        ];
    }
}
