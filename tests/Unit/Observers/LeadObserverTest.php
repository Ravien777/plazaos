<?php

declare(strict_types=1);

namespace Tests\Unit\Observers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();
    }

    public function test_created_triggers_automation_activity(): void
    {
        $lead = Lead::factory()->create();

        $this->assertDatabaseHas('activities', [
            'subject_type' => Lead::class,
            'subject_id' => $lead->id,
            'event' => 'automation.lead_imported',
        ]);
    }
}
