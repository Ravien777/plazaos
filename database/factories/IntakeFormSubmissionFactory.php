<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\IntakeForm;
use App\Models\IntakeFormSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

class IntakeFormSubmissionFactory extends Factory
{
    protected $model = IntakeFormSubmission::class;

    public function definition(): array
    {
        return [
            'intake_form_id' => IntakeForm::factory(),
            'client_id' => Client::factory(),
            'client_user_id' => ClientUser::factory(),
            'submitted_at' => now(),
        ];
    }
}
