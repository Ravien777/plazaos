<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Lead;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_index_returns_notes(): void
    {
        $lead = Lead::factory()->create();
        Note::factory()->count(2)->create([
            'noteable_type' => $lead->getMorphClass(),
            'noteable_id' => $lead->id,
        ]);

        $response = $this->get(route('notes.index', ['lead', $lead->id]));

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    public function test_store_creates_note_and_redirects(): void
    {
        $lead = Lead::factory()->create();

        $response = $this->post(route('notes.store', ['lead', $lead->id]), [
            'content' => 'Called client, interested.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('notes', ['content' => 'Called client, interested.']);
    }

    public function test_store_validates_required_content(): void
    {
        $lead = Lead::factory()->create();

        $response = $this->post(route('notes.store', ['lead', $lead->id]), []);

        $response->assertSessionHasErrors(['content']);
    }

    public function test_update_updates_note_and_redirects(): void
    {
        $note = Note::factory()->create();

        $response = $this->put(route('notes.update', $note), [
            'content' => 'Updated note.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('Updated note.', $note->fresh()->content);
    }

    public function test_destroy_deletes_note_and_redirects(): void
    {
        $note = Note::factory()->create();

        $response = $this->delete(route('notes.destroy', $note));

        $response->assertRedirect();
        $this->assertSoftDeleted($note);
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->post('/logout');

        $lead = Lead::factory()->create();
        $this->get(route('notes.index', ['lead', $lead->id]))->assertRedirect(route('login'));
    }
}
