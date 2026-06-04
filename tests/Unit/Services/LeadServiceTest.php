<?php

namespace Tests\Unit\Services;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\LeadCreated;
use App\Services\LeadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LeadServiceTest extends TestCase
{
    use RefreshDatabase;

    private LeadService $leadService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->leadService = app(LeadService::class);
        User::factory()->create(['id' => 1]);
    }

    public function test_list_returns_paginated_results(): void
    {
        Lead::factory()->count(3)->create();

        $result = $this->leadService->list();

        $this->assertCount(3, $result->items());
    }

    public function test_list_filters_by_search(): void
    {
        Lead::factory()->create(['company_name' => 'Acme Corp']);
        Lead::factory()->create(['company_name' => 'Other Inc']);

        $result = $this->leadService->list(['search' => 'Acme']);

        $this->assertCount(1, $result->items());
        $this->assertEquals('Acme Corp', $result->items()[0]->company_name);
    }

    public function test_list_filters_by_status(): void
    {
        Lead::factory()->create(['status' => LeadStatus::New]);
        Lead::factory()->create(['status' => LeadStatus::Qualified]);

        $result = $this->leadService->list(['status' => 'new']);

        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_by_source(): void
    {
        Lead::factory()->create(['source' => 'Website']);
        Lead::factory()->create(['source' => 'Referral']);

        $result = $this->leadService->list(['source' => 'Website']);

        $this->assertCount(1, $result->items());
    }

    public function test_create_creates_lead_and_logs_activity_and_sends_notification(): void
    {
        Notification::fake();

        $data = Lead::factory()->make()->toArray();
        $lead = $this->leadService->create($data);

        $this->assertDatabaseHas('leads', ['id' => $lead->id]);
        $this->assertDatabaseHas('activities', [
            'subject_type' => Lead::class,
            'subject_id' => $lead->id,
            'event' => 'lead.created',
        ]);
        Notification::assertSentTo(User::find(1), LeadCreated::class);
    }

    public function test_update_updates_lead_and_logs_activity(): void
    {
        $lead = Lead::factory()->create();

        $updated = $this->leadService->update($lead, ['company_name' => 'New Name']);

        $this->assertEquals('New Name', $updated->company_name);
        $this->assertDatabaseHas('activities', [
            'subject_type' => Lead::class,
            'subject_id' => $lead->id,
            'event' => 'lead.updated',
        ]);
    }

    public function test_delete_deletes_lead_and_logs_activity(): void
    {
        $lead = Lead::factory()->create();

        $this->leadService->delete($lead);

        $this->assertDatabaseMissing('leads', ['id' => $lead->id]);
        $this->assertDatabaseHas('activities', [
            'subject_type' => Lead::class,
            'subject_id' => $lead->id,
            'event' => 'lead.deleted',
        ]);
    }
}
