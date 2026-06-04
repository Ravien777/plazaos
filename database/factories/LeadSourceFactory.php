<?php

namespace Database\Factories;

use App\Enums\SourceType;
use App\Models\LeadSource;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadSourceFactory extends Factory
{
    protected $model = LeadSource::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'type' => SourceType::Other,
            'config' => [],
            'is_active' => true,
            'frequency' => 'manual',
        ];
    }
}
