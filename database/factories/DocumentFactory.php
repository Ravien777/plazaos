<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Client;
use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        $documentable = Client::factory()->create();

        return [
            'documentable_type' => $documentable->getMorphClass(),
            'documentable_id' => $documentable->id,
            'name' => fake()->word() . '.' . fake()->fileExtension(),
            'path' => 'documents/' . fake()->uuid() . '.' . fake()->fileExtension(),
            'mime_type' => fake()->mimeType(),
            'size' => fake()->numberBetween(1024, 5242880),
        ];
    }
}
