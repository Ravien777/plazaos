<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ImportLeadsJob;
use App\Models\Lead;
use App\Models\LeadImport;
use App\Models\User;
use App\Notifications\ImportCompleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ImportLeadsJobTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Notification::fake();
    }

    public function test_processes_valid_csv_and_creates_leads(): void
    {
        $csv = "company_name,contact_name,email\nAcme Corp,John,acme@test.com\nOther Inc,Jane,other@test.com\n";
        file_put_contents(storage_path('app/imports/test.csv'), $csv);

        $import = LeadImport::factory()->create([
            'filepath' => 'imports/test.csv',
            'user_id' => $this->user->id,
            'column_mapping' => ['header_row' => 0, 'fields' => ['company_name' => 'company_name', 'contact_name' => 'contact_name', 'email' => 'email']],
            'duplicate_strategy' => 'skip',
        ]);

        (new ImportLeadsJob($import))->handle();
        $import->refresh();

        $this->assertEquals('completed', $import->status);
        $this->assertEquals(2, $import->processed);
        $this->assertDatabaseHas('leads', ['company_name' => 'Acme Corp']);
        $this->assertDatabaseHas('leads', ['company_name' => 'Other Inc']);
    }

    public function test_skips_duplicates_when_strategy_is_skip(): void
    {
        Lead::factory()->create(['email' => 'existing@test.com']);
        $csv = "company_name,contact_name,email\nDuplicate,Bob,existing@test.com\n";
        file_put_contents(storage_path('app/imports/skip.csv'), $csv);

        $import = LeadImport::factory()->create([
            'filepath' => 'imports/skip.csv',
            'user_id' => $this->user->id,
            'column_mapping' => ['header_row' => 0, 'fields' => ['company_name' => 'company_name', 'contact_name' => 'contact_name', 'email' => 'email']],
            'duplicate_strategy' => 'skip',
        ]);

        (new ImportLeadsJob($import))->handle();
        $import->refresh();

        $this->assertEquals(1, $import->processed);
        $this->assertEquals(0, $import->failed);
    }

    public function test_updates_duplicates_when_strategy_is_update(): void
    {
        $lead = Lead::factory()->create(['email' => 'existing@test.com', 'company_name' => 'Old Name']);
        $csv = "company_name,contact_name,email\nNew Name,Bob,existing@test.com\n";
        file_put_contents(storage_path('app/imports/update.csv'), $csv);

        $import = LeadImport::factory()->create([
            'filepath' => 'imports/update.csv',
            'user_id' => $this->user->id,
            'column_mapping' => ['header_row' => 0, 'fields' => ['company_name' => 'company_name', 'contact_name' => 'contact_name', 'email' => 'email']],
            'duplicate_strategy' => 'update',
        ]);

        (new ImportLeadsJob($import))->handle();
        $lead->refresh();

        $this->assertEquals('New Name', $lead->company_name);
    }

    public function test_sends_import_completed_notification(): void
    {
        $csv = "company_name,contact_name,email\nAcme Corp,John,acme@test.com\n";
        file_put_contents(storage_path('app/imports/notif.csv'), $csv);

        $import = LeadImport::factory()->create([
            'filepath' => 'imports/notif.csv',
            'user_id' => $this->user->id,
            'column_mapping' => ['header_row' => 0, 'fields' => ['company_name' => 'company_name', 'contact_name' => 'contact_name', 'email' => 'email']],
        ]);

        (new ImportLeadsJob($import))->handle();

        Notification::assertSentTo($this->user, ImportCompleted::class);
    }

    public function test_completes_with_minimal_data(): void
    {
        $csv = "bad\nrow\n";
        file_put_contents(storage_path('app/imports/minimal.csv'), $csv);

        $import = LeadImport::factory()->create([
            'filepath' => 'imports/minimal.csv',
            'user_id' => $this->user->id,
            'column_mapping' => ['header_row' => 0, 'fields' => ['bad' => 'company_name']],
            'duplicate_strategy' => 'skip',
        ]);

        (new ImportLeadsJob($import))->handle();
        $import->refresh();

        $this->assertEquals('completed', $import->status);
        $this->assertDatabaseHas('leads', ['company_name' => 'row']);
    }
}
