<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DocumentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_index_returns_documents(): void
    {
        $client = Client::factory()->create();
        Document::factory()->count(2)->create([
            'documentable_type' => $client->getMorphClass(),
            'documentable_id' => $client->id,
        ]);

        $response = $this->get(route('documents.index', ['client', $client->id]));

        $response->assertOk();
        $response->assertJsonCount(2, 'documents');
    }

    public function test_store_creates_document(): void
    {
        $client = Client::factory()->create();
        $file = UploadedFile::fake()->create('test.pdf', 100);

        $response = $this->post(route('documents.store'), [
            'documentable_type' => 'client',
            'documentable_id' => $client->id,
            'file' => $file,
        ]);

        $response->assertCreated();
        $response->assertJsonStructure(['document' => ['id', 'name']]);
    }

    public function test_destroy_deletes_document(): void
    {
        $document = Document::factory()->create();

        $response = $this->delete(route('documents.destroy', $document));

        $response->assertOk();
        $response->assertJson(['message' => 'Document deleted.']);
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->post('/logout');

        $client = Client::factory()->create();
        $this->get(route('documents.index', ['client', $client->id]))->assertRedirect(route('login'));
    }
}
