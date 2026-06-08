<?php

namespace Tests\Unit\Services;

use App\Enums\ProjectStatus;
use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProjectService $projectService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectService = app(ProjectService::class);
        User::factory()->create();
    }

    public function test_list_returns_paginated_results(): void
    {
        Project::factory()->count(3)->create();

        $result = $this->projectService->list();

        $this->assertCount(3, $result->items());
    }

    public function test_list_filters_by_status(): void
    {
        Project::factory()->create(['status' => ProjectStatus::Development]);
        Project::factory()->create(['status' => ProjectStatus::Completed]);

        $result = $this->projectService->list(['status' => 'development']);

        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_by_client_id(): void
    {
        $client = Client::factory()->create();
        Project::factory()->create(['client_id' => $client->id]);
        Project::factory()->count(2)->create();

        $result = $this->projectService->list(['client_id' => $client->id]);

        $this->assertCount(1, $result->items());
    }

    public function test_list_loads_client_relationship(): void
    {
        Project::factory()->create();

        $result = $this->projectService->list();

        $this->assertTrue($result->items()[0]->relationLoaded('client'));
    }

    public function test_create_creates_project_and_logs_activity(): void
    {
        $data = Project::factory()->make()->toArray();
        $project = $this->projectService->create($data);

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
        $this->assertDatabaseHas('activities', [
            'subject_type' => Project::class,
            'subject_id' => $project->id,
            'event' => 'project.created',
        ]);
    }

    public function test_update_updates_project_and_logs_activity(): void
    {
        $project = Project::factory()->create();

        $updated = $this->projectService->update($project, ['name' => 'New Project Name']);

        $this->assertEquals('New Project Name', $updated->name);
        $this->assertDatabaseHas('activities', [
            'subject_type' => Project::class,
            'subject_id' => $project->id,
            'event' => 'project.updated',
        ]);
    }

    public function test_delete_deletes_project_and_logs_activity(): void
    {
        $project = Project::factory()->create();

        $this->projectService->delete($project);

        $this->assertSoftDeleted($project);
        $this->assertDatabaseHas('activities', [
            'subject_type' => Project::class,
            'subject_id' => $project->id,
            'event' => 'project.deleted',
        ]);
    }
}
