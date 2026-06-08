<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectBulkControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_bulk_delete_archives_projects(): void
    {
        $projects = Project::factory()->count(3)->create();

        $response = $this->post(route('projects.bulk.delete'), [
            'ids' => $projects->pluck('id')->toArray(),
        ]);

        $response->assertSessionHas('success');
        $this->assertSoftDeleted($projects[0]);
        $this->assertSoftDeleted($projects[1]);
        $this->assertSoftDeleted($projects[2]);
    }

    public function test_bulk_delete_validates_empty_ids(): void
    {
        $response = $this->post(route('projects.bulk.delete'), [
            'ids' => [],
        ]);

        $response->assertSessionHasErrors(['ids']);
    }

    public function test_bulk_delete_validates_nonexistent_ids(): void
    {
        $response = $this->post(route('projects.bulk.delete'), [
            'ids' => ['non-existent-id'],
        ]);

        $response->assertSessionHasErrors(['ids.0']);
    }

    public function test_bulk_force_delete_removes(): void
    {
        $projects = Project::factory()->count(3)->create();

        $response = $this->post(route('projects.bulk.force-delete'), [
            'ids' => $projects->pluck('id')->toArray(),
        ]);

        $response->assertSessionHas('success');
        $this->assertModelMissing($projects[0]);
        $this->assertModelMissing($projects[1]);
        $this->assertModelMissing($projects[2]);
    }

    public function test_bulk_update_status_changes(): void
    {
        $projects = Project::factory()->count(3)->create(['status' => 'discovery']);

        $response = $this->post(route('projects.bulk.status'), [
            'ids' => $projects->pluck('id')->toArray(),
            'status' => 'development',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('development', $projects[0]->fresh()->status->value);
        $this->assertEquals('development', $projects[1]->fresh()->status->value);
        $this->assertEquals('development', $projects[2]->fresh()->status->value);
    }

    public function test_bulk_update_status_validates_invalid(): void
    {
        $projects = Project::factory()->count(2)->create();

        $response = $this->post(route('projects.bulk.status'), [
            'ids' => $projects->pluck('id')->toArray(),
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors(['status']);
    }
}
