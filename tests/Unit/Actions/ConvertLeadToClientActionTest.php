<?php

namespace Tests\Unit\Actions;

use App\Actions\ConvertLeadToClientAction;
use App\Enums\LeadStatus;
use App\Models\Document;
use App\Models\Email;
use App\Models\Lead;
use App\Models\Note;
use App\Models\User;
use App\Notifications\LeadConverted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConvertLeadToClientActionTest extends TestCase
{
    use RefreshDatabase;

    private ConvertLeadToClientAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(ConvertLeadToClientAction::class);
        User::factory()->create();
    }

    public function test_converts_lead_to_client(): void
    {
        Notification::fake();
        $lead = Lead::factory()->create();

        $client = $this->action->execute($lead);

        $this->assertDatabaseHas('clients', ['id' => $client->id, 'lead_id' => $lead->id]);
        $this->assertEquals($lead->company_name, $client->company_name);
        $this->assertEquals($lead->email, $client->email);
    }

    public function test_sets_lead_status_to_archived_and_converted_at(): void
    {
        Notification::fake();
        $lead = Lead::factory()->create();

        $this->action->execute($lead);
        $lead->refresh();

        $this->assertEquals(LeadStatus::Archived, $lead->status);
        $this->assertNotNull($lead->converted_at);
    }

    public function test_logs_two_activities(): void
    {
        Notification::fake();
        $lead = Lead::factory()->create();

        $this->action->execute($lead);

        $this->assertDatabaseHas('activities', ['event' => 'lead.converted']);
        $this->assertDatabaseHas('activities', ['event' => 'client.created_from_lead']);
    }

    public function test_sends_lead_converted_notification(): void
    {
        Notification::fake();
        $lead = Lead::factory()->create();

        $client = $this->action->execute($lead);

        Notification::assertSentTo(User::find(1), LeadConverted::class, function ($notification) use ($lead, $client) {
            return $notification->lead->id === $lead->id && $notification->client->id === $client->id;
        });
    }

    public function test_copies_notes_to_client(): void
    {
        Notification::fake();
        $lead = Lead::factory()->create();
        $lead->notes()->create(['content' => 'Test note content', 'created_by' => '1']);

        $client = $this->action->execute($lead);

        $this->assertDatabaseHas('notes', [
            'noteable_type' => 'App\Models\Client',
            'noteable_id' => $client->id,
            'content' => 'Test note content',
        ]);
    }

    public function test_copies_emails_to_client(): void
    {
        Notification::fake();
        $lead = Lead::factory()->create();
        $lead->emails()->create([
            'from' => 'from@test.com',
            'to' => 'to@test.com',
            'subject' => 'Test Subject',
            'body' => 'Test body',
            'status' => 'sent',
        ]);

        $client = $this->action->execute($lead);

        $this->assertDatabaseHas('emails', [
            'emailable_type' => 'App\Models\Client',
            'emailable_id' => $client->id,
            'subject' => 'Test Subject',
            'to' => 'to@test.com',
        ]);
    }

    public function test_copies_documents_with_new_path(): void
    {
        Storage::fake('local');
        Notification::fake();
        $lead = Lead::factory()->create();

        $file = UploadedFile::fake()->create('test.pdf', 100);
        $path = $file->store('documents', 'local');

        $lead->documents()->create([
            'name' => 'test.pdf',
            'path' => $path,
            'mime_type' => 'application/pdf',
            'size' => 100,
            'user_id' => 1,
        ]);

        $client = $this->action->execute($lead);

        $copiedDoc = Document::where('documentable_type', 'App\Models\Client')
            ->where('documentable_id', $client->id)
            ->first();

        $this->assertNotNull($copiedDoc);
        $this->assertNotEquals($path, $copiedDoc->path);
        $this->assertStringContainsString('clients/' . $client->id, $copiedDoc->path);
        Storage::disk('local')->assertExists($copiedDoc->path);
    }
}
