<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MeetingStatus;
use App\Models\Client;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeetingFactory extends Factory
{
    protected $model = Meeting::class;

    public function definition(): array
    {
        return [
            'meetable_type' => (new Client)->getMorphClass(),
            'meetable_id' => Client::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'start_time' => fake()->dateTimeBetween('now', '+1 month'),
            'end_time' => fake()->dateTimeBetween('+1 hour', '+2 hours'),
            'location' => fake()->optional(0.5)->city(),
            'meet_link' => fake()->optional(0.3)->url(),
            'status' => MeetingStatus::Scheduled,
            'user_id' => User::factory(),
        ];
    }
}
