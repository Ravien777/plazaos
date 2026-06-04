<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\LeadSource;
use App\Models\User;
use App\Services\LeadSourceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadSourceServiceTest extends TestCase
{
    use RefreshDatabase;

    private LeadSourceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(LeadSourceService::class);
    }

    public function test_creates_lead_source(): void
    {
        $source = $this->service->create([
            'name' => 'Upwork',
            'type' => 'upwork',
            'frequency' => 'daily',
            'config' => ['skills' => ['laravel']],
        ]);

        $this->assertDatabaseHas('lead_sources', ['name' => 'Upwork']);
        $this->assertEquals('upwork', $source->type->value);
        $this->assertEquals('daily', $source->frequency);
    }

    public function test_lists_paginated(): void
    {
        LeadSource::factory()->count(3)->create();

        $result = $this->service->list();

        $this->assertCount(3, $result);
    }

    public function test_updates_lead_source(): void
    {
        $source = LeadSource::factory()->create();

        $this->service->update($source, ['name' => 'Updated', 'frequency' => 'weekly']);

        $this->assertEquals('Updated', $source->fresh()->name);
        $this->assertEquals('weekly', $source->fresh()->frequency);
    }

    public function test_deletes_lead_source(): void
    {
        $source = LeadSource::factory()->create();

        $this->service->delete($source);

        $this->assertDatabaseMissing('lead_sources', ['id' => $source->id]);
    }

    public function test_run_dispatches_job(): void
    {
        $source = LeadSource::factory()->create();

        $this->service->run($source);

        $this->assertDatabaseHas('lead_sources', ['id' => $source->id]);
    }
}
