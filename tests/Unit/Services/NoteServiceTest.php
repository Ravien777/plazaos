<?php

namespace Tests\Unit\Services;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Note;
use App\Models\Project;
use App\Models\User;
use App\Services\NoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteServiceTest extends TestCase
{
    use RefreshDatabase;

    private NoteService $noteService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->noteService = app(NoteService::class);
        User::factory()->create();
    }

    public function test_add_creates_note_and_logs_activity(): void
    {
        $lead = Lead::factory()->create();
        $user = User::factory()->create();

        $note = $this->noteService->add($lead, 'Test note content', $user);

        $this->assertDatabaseHas('notes', ['id' => $note->id, 'content' => 'Test note content']);
        $this->assertDatabaseHas('activities', [
            'event' => 'note.added',
        ]);
    }

    public function test_add_creates_note_on_all_polymorphic_types(): void
    {
        $user = User::find(1);
        $lead = Lead::factory()->create();
        $client = Client::factory()->create();
        $project = Project::factory()->create();

        $this->noteService->add($lead, 'Lead note', $user);
        $this->noteService->add($client, 'Client note', $user);
        $this->noteService->add($project, 'Project note', $user);

        $this->assertCount(1, $this->noteService->getFor($lead));
        $this->assertCount(1, $this->noteService->getFor($client));
        $this->assertCount(1, $this->noteService->getFor($project));
    }

    public function test_update_changes_note_content_and_logs_activity(): void
    {
        $lead = Lead::factory()->create();
        $user = User::factory()->create();
        $note = $this->noteService->add($lead, 'Original content', $user);

        $this->noteService->update($note, 'Updated content');

        $this->assertEquals('Updated content', $note->fresh()->content);
        $this->assertDatabaseHas('activities', [
            'event' => 'note.updated',
        ]);
    }

    public function test_delete_removes_note_and_logs_activity(): void
    {
        $lead = Lead::factory()->create();
        $user = User::factory()->create();
        $note = $this->noteService->add($lead, 'Content', $user);

        $this->noteService->delete($note);

        $this->assertSoftDeleted($note);
        $this->assertDatabaseHas('activities', [
            'event' => 'note.deleted',
        ]);
    }
}
