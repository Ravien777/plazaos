<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Comment;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_index_returns_comments_json(): void
    {
        $lead = Lead::factory()->create();
        $comment = Comment::create([
            'commentable_type' => $lead->getMorphClass(),
            'commentable_id' => $lead->id,
            'user_id' => auth()->id(),
            'body' => 'Test comment.',
        ]);

        $response = $this->get(route('comments.index', ['lead', $lead->id]));

        $response->assertOk();
        $response->assertJsonStructure(['data' => [['id', 'body', 'user', 'created_at', 'can_delete']]]);
    }

    public function test_store_creates_comment(): void
    {
        $lead = Lead::factory()->create();

        $response = $this->post(route('comments.store', ['lead', $lead->id]), [
            'body' => 'New comment body.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', ['body' => 'New comment body.']);
    }

    public function test_store_validates_body(): void
    {
        $lead = Lead::factory()->create();

        $response = $this->post(route('comments.store', ['lead', $lead->id]), []);

        $response->assertSessionHasErrors(['body']);
    }

    public function test_destroy_deletes_comment(): void
    {
        $lead = Lead::factory()->create();
        $comment = Comment::create([
            'commentable_type' => $lead->getMorphClass(),
            'commentable_id' => $lead->id,
            'user_id' => auth()->id(),
            'body' => 'To be deleted.',
        ]);

        $response = $this->delete(route('comments.destroy', $comment));

        $response->assertRedirect();
        $this->assertSoftDeleted($comment);
    }

    public function test_destroy_unauthorized(): void
    {
        $otherUser = User::factory()->create();
        $lead = Lead::factory()->create();
        $comment = Comment::create([
            'commentable_type' => $lead->getMorphClass(),
            'commentable_id' => $lead->id,
            'user_id' => $otherUser->id,
            'body' => 'Other user comment.',
        ]);

        $response = $this->delete(route('comments.destroy', $comment));

        $response->assertForbidden();
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->post('/logout');

        $lead = Lead::factory()->create();
        $this->get(route('comments.index', ['lead', $lead->id]))->assertRedirect(route('login'));
    }
}
