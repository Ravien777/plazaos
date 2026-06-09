<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_index_returns_200(): void
    {
        Project::factory()->count(3)->create();

        $response = $this->get(route('projects.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Projects/Index'));
    }

    public function test_store_creates_project_and_redirects(): void
    {
        $client = Client::factory()->create();

        $response = $this->post(route('projects.store'), [
            'name' => 'Website Redesign',
            'description' => 'Redesign the company website',
            'client_id' => $client->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', ['name' => 'Website Redesign']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->post(route('projects.store'), []);

        $response->assertSessionHasErrors(['name', 'client_id']);
    }

    public function test_show_returns_200_with_relations(): void
    {
        $project = Project::factory()->create();

        $response = $this->get(route('projects.show', $project));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Projects/Show')
            ->has('project')
        );
    }

    public function test_edit_returns_200(): void
    {
        $project = Project::factory()->create();

        $response = $this->get(route('projects.edit', $project));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Projects/Edit'));
    }

    public function test_update_updates_project_and_redirects(): void
    {
        $project = Project::factory()->create();

        $response = $this->put(route('projects.update', $project), [
            'name' => 'Updated Project',
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $this->assertEquals('Updated Project', $project->fresh()->name);
    }

    public function test_destroy_deletes_project_and_redirects(): void
    {
        $project = Project::factory()->create();

        $response = $this->delete(route('projects.destroy', $project));

        $response->assertRedirect(route('projects.index'));
        $this->assertSoftDeleted($project);
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->post('/logout');

        $this->get(route('projects.index'))->assertRedirect(route('login'));
    }
}
