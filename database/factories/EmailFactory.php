<?php

namespace Database\Factories;

use App\Models\Email;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailFactory extends Factory
{
    protected $model = Email::class;

    public function definition(): array
    {
        return [
            'from' => 'noreply@plazaos.test',
            'to' => fake()->safeEmail(),
            'subject' => fake()->sentence(),
            'body' => fake()->paragraph(),
            'status' => 'sent',
            'emailable_type' => Lead::class,
            'emailable_id' => Lead::factory(),
        ];
    }
}
