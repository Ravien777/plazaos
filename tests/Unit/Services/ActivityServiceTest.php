<?php

namespace Tests\Unit\Services;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityServiceTest extends TestCase
{
    use RefreshDatabase;

    private ActivityService $activityService;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create(['id' => 1]);
        $this->activityService = app(ActivityService::class);
    }

    public function test_log_creates_activity(): void
    {
        $lead = Lead::factory()->create();

        $activity = $this->activityService->log($lead, 'test.event', 'Test description');

        $this->assertDatabaseHas('activities', [
            'id' => $activity->id,
            'subject_type' => Lead::class,
            'subject_id' => $lead->id,
            'event' => 'test.event',
            'description' => 'Test description',
        ]);
    }

    public function test_log_stores_metadata(): void
    {
        $lead = Lead::factory()->create();

        $activity = $this->activityService->log($lead, 'test.event', 'Desc', ['key' => 'value']);

        $this->assertEquals(['key' => 'value'], $activity->metadata);
    }

    public function test_get_for_returns_activities_for_polymorphic_model(): void
    {
        $lead = Lead::factory()->create();
        $client = Client::factory()->create();
        $project = Project::factory()->create();

        $this->activityService->log($lead, 'lead.event', 'Lead activity');
        $this->activityService->log($client, 'client.event', 'Client activity');
        $this->activityService->log($project, 'project.event', 'Project activity');

        $this->assertCount(2, $this->activityService->getFor($lead));
        $this->assertCount(1, $this->activityService->getFor($client));
        $this->assertCount(1, $this->activityService->getFor($project));
    }

    public function test_recent_returns_latest_activities(): void
    {
        Lead::factory()->create()->activities()->createMany([
            ['event' => 'e1', 'description' => 'a'],
            ['event' => 'e2', 'description' => 'b'],
            ['event' => 'e3', 'description' => 'c'],
        ]);

        $recent = $this->activityService->recent(2);

        $this->assertCount(2, $recent);
    }

    public function test_count_by_event_returns_correct_count(): void
    {
        $lead = Lead::factory()->create();
        $this->activityService->log($lead, 'lead.created', 'Created');
        $this->activityService->log($lead, 'lead.created', 'Created again');
        $this->activityService->log($lead, 'lead.updated', 'Updated');

        $this->assertEquals(2, $this->activityService->countByEvent('lead.created'));
        $this->assertEquals(1, $this->activityService->countByEvent('lead.updated'));
    }

    public function test_count_by_event_with_since_filter(): void
    {
        $lead = Lead::factory()->create();
        $this->activityService->log($lead, 'test.event', 'Old');
        $this->travel(2)->days();
        $this->activityService->log($lead, 'test.event', 'Recent');

        $this->assertEquals(1, $this->activityService->countByEvent('test.event', now()->subDay()));
    }
}
