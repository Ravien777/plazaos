<?php

declare(strict_types=1);

namespace Tests\Unit\Observers;

use App\Models\IntakeFormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntakeFormSubmissionObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create(['id' => 1]);
    }

    public function test_created_triggers_automation_activity(): void
    {
        $submission = IntakeFormSubmission::factory()->create();

        $this->assertDatabaseHas('activities', [
            'subject_type' => IntakeFormSubmission::class,
            'subject_id' => $submission->id,
            'event' => 'automation.intake_submitted',
        ]);
    }
}
