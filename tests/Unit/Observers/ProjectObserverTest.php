<?php

declare(strict_types=1);

namespace Tests\Unit\Observers;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create(['id' => 1]);
    }

    public function test_updated_to_completed_triggers_automation_activity(): void
    {
        $project = Project::factory()->create();

        $project->update(['status' => ProjectStatus::Completed]);

        $this->assertDatabaseHas('activities', [
            'subject_type' => Project::class,
            'subject_id' => $project->id,
            'event' => 'automation.project_completed',
        ]);
    }

    public function test_updated_to_non_completed_does_not_trigger_automation(): void
    {
        $project = Project::factory()->create(['status' => ProjectStatus::Discovery]);

        $project->update(['status' => ProjectStatus::Design]);

        $this->assertDatabaseMissing('activities', [
            'subject_type' => Project::class,
            'subject_id' => $project->id,
            'event' => 'automation.project_completed',
        ]);
    }
}
