<?php

namespace Tests\Unit\Actions;

use App\Actions\UploadDocumentAction;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadDocumentActionTest extends TestCase
{
    use RefreshDatabase;

    private UploadDocumentAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(UploadDocumentAction::class);
        User::factory()->create();
        $this->actingAs(User::find(1));
    }

    public function test_stores_file_and_creates_document_record(): void
    {
        Storage::fake('local');
        $client = Client::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

        $document = $this->action->execute(Client::class, $client->id, $file);

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'documentable_type' => Client::class,
            'documentable_id' => $client->id,
            'name' => 'document.pdf',
            'mime_type' => 'application/pdf',
        ]);
        Storage::disk('local')->assertExists($document->path);
    }

    public function test_logs_activity(): void
    {
        Storage::fake('local');
        $client = Client::factory()->create();
        $file = UploadedFile::fake()->create('doc.pdf', 1024);

        $this->action->execute(Client::class, $client->id, $file);

        $this->assertDatabaseHas('activities', [
            'event' => 'document.uploaded',
        ]);
    }
}
