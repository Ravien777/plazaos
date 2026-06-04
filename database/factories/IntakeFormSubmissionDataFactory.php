<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\IntakeFormField;
use App\Models\IntakeFormSubmission;
use App\Models\IntakeFormSubmissionData;
use Illuminate\Database\Eloquent\Factories\Factory;

class IntakeFormSubmissionDataFactory extends Factory
{
    protected $model = IntakeFormSubmissionData::class;

    public function definition(): array
    {
        return [
            'intake_form_submission_id' => IntakeFormSubmission::factory(),
            'intake_form_field_id' => IntakeFormField::factory(),
            'value' => fake()->sentence(),
            'file_path' => null,
        ];
    }
}
