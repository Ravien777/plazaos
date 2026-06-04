<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use App\Models\Lead;
use App\Models\User;
use App\Notifications\LeadInactiveReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CheckInactiveLeadsCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create(['id' => 1]);
    }

    public function test_triggers_automation_for_stale_leads(): void
    {
        $stale = Lead::factory()->create([
            'last_contacted_at' => now()->subDays(10),
        ]);

        $recent = Lead::factory()->create([
            'last_contacted_at' => now()->subDay(),
        ]);

        Notification::fake();

        $this->artisan('automations:check-inactive-leads')
            ->assertSuccessful();

        Notification::assertSentTo(User::find(1), LeadInactiveReminder::class);
    }

    public function test_triggers_for_leads_never_contacted(): void
    {
        Lead::factory()->create([
            'last_contacted_at' => null,
        ]);

        Notification::fake();

        $this->artisan('automations:check-inactive-leads')
            ->assertSuccessful();

        Notification::assertSentTo(User::find(1), LeadInactiveReminder::class);
    }

    public function test_skips_active_leads(): void
    {
        Lead::factory()->create([
            'last_contacted_at' => now()->subDay(),
        ]);

        Notification::fake();

        $this->artisan('automations:check-inactive-leads')
            ->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_skips_converted_leads(): void
    {
        Lead::factory()->create([
            'last_contacted_at' => now()->subDays(10),
            'converted_at' => now()->subDays(5),
        ]);

        Notification::fake();

        $this->artisan('automations:check-inactive-leads')
            ->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_handles_no_leads_gracefully(): void
    {
        Notification::fake();

        $this->artisan('automations:check-inactive-leads')
            ->assertSuccessful();

        Notification::assertNothingSent();
    }
}
