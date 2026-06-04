<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Portal;

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentControllerTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private Client $otherClient;
    private ClientUser $user;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create(['id' => 1]);

        $this->client = Client::factory()->create();
        $this->otherClient = Client::factory()->create();
        $this->user = ClientUser::factory()->create(['client_id' => $this->client->id]);
        $this->actingAs($this->user, 'client');
    }

    public function test_index_shows_only_own_documents(): void
    {
        Document::factory()->create([
            'documentable_type' => 'client',
            'documentable_id' => $this->client->id,
            'name' => 'Our Doc',
        ]);
        Document::factory()->create([
            'documentable_type' => 'client',
            'documentable_id' => $this->otherClient->id,
            'name' => 'Their Doc',
        ]);

        $response = $this->get(route('portal.documents.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Portal/Documents/Index')
            ->has('documents', 1)
        );
    }

    public function test_cannot_download_other_clients_document(): void
    {
        $doc = Document::factory()->create([
            'documentable_type' => 'client',
            'documentable_id' => $this->otherClient->id,
        ]);

        $response = $this->get(route('portal.documents.download', $doc));

        $response->assertForbidden();
    }
}
