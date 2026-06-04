<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\IntakeForm;
use Illuminate\Database\Eloquent\Factories\Factory;

class IntakeFormFactory extends Factory
{
    protected $model = IntakeForm::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'is_active' => true,
        ];
    }
}
