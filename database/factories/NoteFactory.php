<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lead;
use App\Models\Note;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoteFactory extends Factory
{
    protected $model = Note::class;

    public function definition(): array
    {
        $noteable = Lead::factory()->create();

        return [
            'noteable_type' => $noteable->getMorphClass(),
            'noteable_id' => $noteable->id,
            'content' => fake()->paragraph(),
            'created_by' => 1,
        ];
    }
}
