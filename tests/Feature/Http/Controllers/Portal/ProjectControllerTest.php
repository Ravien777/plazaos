<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Portal;

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
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

    public function test_index_shows_only_own_projects(): void
    {
        Project::factory()->create(['client_id' => $this->client->id, 'name' => 'Our Project']);
        Project::factory()->create(['client_id' => $this->otherClient->id, 'name' => 'Their Project']);

        $response = $this->get(route('portal.projects.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Portal/Projects/Index')
            ->has('projects.data', 1)
            ->where('projects.data.0.name', 'Our Project')
        );
    }

    public function test_show_returns_own_project(): void
    {
        $project = Project::factory()->create(['client_id' => $this->client->id]);

        $response = $this->get(route('portal.projects.show', $project));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Portal/Projects/Show'));
    }

    public function test_cannot_view_other_clients_project(): void
    {
        $project = Project::factory()->create(['client_id' => $this->otherClient->id]);

        $response = $this->get(route('portal.projects.show', $project));

        $response->assertForbidden();
    }
}
