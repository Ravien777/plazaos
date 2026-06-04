<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\IntakeFieldType;
use App\Models\IntakeFormField;
use Illuminate\Database\Eloquent\Factories\Factory;

class IntakeFormFieldFactory extends Factory
{
    protected $model = IntakeFormField::class;

    public function definition(): array
    {
        return [
            'label' => fake()->word(),
            'field_type' => IntakeFieldType::Text,
            'required' => false,
            'options' => null,
            'placeholder' => null,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
