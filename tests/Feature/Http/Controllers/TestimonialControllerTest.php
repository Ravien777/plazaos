<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\Team;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TestimonialControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->client = Client::factory()->create();
    }

    public function test_index_returns_testimonials_page(): void
    {
        Testimonial::factory()->count(3)->create(['client_id' => $this->client->id]);

        $response = $this->get(route('testimonials.index'));

        $response->assertOk();
    }

    public function test_show_public_form_for_valid_token(): void
    {
        $testimonial = Testimonial::factory()->pending()->create([
            'client_id' => $this->client->id,
        ]);

        $response = $this->get(route('review.show', $testimonial->review_token));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('submitted', false)
            ->has('testimonial')
        );
    }

    public function test_show_already_submitted(): void
    {
        $testimonial = Testimonial::factory()->create([
            'client_id' => $this->client->id,
            'submitted_at' => now(),
        ]);

        $response = $this->get(route('review.show', $testimonial->review_token));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('submitted', true)
        );
    }

    public function test_show_404_for_invalid_token(): void
    {
        $response = $this->get(route('review.show', 'nonexistent-token'));

        $response->assertNotFound();
    }

    public function test_submit_saves_review(): void
    {
        $testimonial = Testimonial::factory()->pending()->create([
            'client_id' => $this->client->id,
        ]);

        $response = $this->post(route('review.submit', $testimonial->review_token), [
            'rating' => 5,
            'content' => 'Amazing work!',
        ]);

        $response->assertRedirect(route('review.thanks', $testimonial->review_token));
        $this->assertEquals(5, $testimonial->fresh()->rating);
        $this->assertEquals('Amazing work!', $testimonial->fresh()->content);
        $this->assertNotNull($testimonial->fresh()->submitted_at);
        $this->assertTrue($testimonial->fresh()->is_approved);
    }

    public function test_submit_validates_rating(): void
    {
        $testimonial = Testimonial::factory()->pending()->create([
            'client_id' => $this->client->id,
        ]);

        $response = $this->post(route('review.submit', $testimonial->review_token), [
            'rating' => 0,
        ]);

        $response->assertSessionHasErrors(['rating']);
    }

    public function test_destroy_deletes_testimonial(): void
    {
        $testimonial = Testimonial::factory()->create(['client_id' => $this->client->id]);

        $response = $this->delete(route('testimonials.destroy', $testimonial));

        $response->assertRedirect();
        $this->assertSoftDeleted($testimonial);
    }

    public function test_destroy_forbidden_for_member(): void
    {
        $team = Team::create(['name' => 'Test Team', 'owner_id' => $this->user->id]);
        $member = User::factory()->create(['role' => 'member', 'team_id' => $team->id]);
        $this->actingAs($member);

        $testimonial = Testimonial::factory()->create(['client_id' => $this->client->id]);

        $response = $this->delete(route('testimonials.destroy', $testimonial));

        $response->assertForbidden();
    }

    public function test_request_from_client_creates_testimonial(): void
    {
        $response = $this->postJson(route('clients.request-review', $this->client));

        $response->assertOk();
        $response->assertJson(['message' => true]);
        $this->assertDatabaseHas('testimonials', ['client_id' => $this->client->id]);
    }

    public function test_request_from_project_creates_testimonial(): void
    {
        $project = Project::factory()->create(['client_id' => $this->client->id]);

        $response = $this->postJson(route('projects.request-review', $project));

        $response->assertOk();
        $this->assertDatabaseHas('testimonials', [
            'client_id' => $this->client->id,
            'project_id' => $project->id,
        ]);
    }
}
