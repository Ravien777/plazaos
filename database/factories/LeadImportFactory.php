<?php

namespace Database\Factories;

use App\Models\LeadImport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadImportFactory extends Factory
{
    protected $model = LeadImport::class;

    public function definition(): array
    {
        return [
            'filename' => fake()->word() . '.csv',
            'filepath' => 'imports/' . fake()->uuid() . '.csv',
            'column_mapping' => [],
            'duplicate_strategy' => 'skip',
            'total_rows' => 0,
            'processed' => 0,
            'failed' => 0,
            'errors' => [],
            'status' => 'pending',
            'user_id' => User::factory(),
        ];
    }
}
