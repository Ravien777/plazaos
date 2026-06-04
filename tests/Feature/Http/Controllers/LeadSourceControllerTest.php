<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\LeadSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadSourceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create(['id' => 1]);
        $this->actingAs($user);
    }

    public function test_index_returns_200(): void
    {
        LeadSource::factory()->count(3)->create();

        $response = $this->get(route('lead-sources.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('LeadSources/Index'));
    }

    public function test_create_returns_200(): void
    {
        $response = $this->get(route('lead-sources.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('LeadSources/Create'));
    }

    public function test_store_creates_source_and_redirects(): void
    {
        $response = $this->post(route('lead-sources.store'), [
            'name' => 'LinkedIn',
            'type' => 'linkedin',
            'frequency' => 'daily',
            'config' => '{"keywords":"developer"}',
        ]);

        $response->assertRedirect(route('lead-sources.index'));
        $this->assertDatabaseHas('lead_sources', ['name' => 'LinkedIn']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->post(route('lead-sources.store'), []);

        $response->assertSessionHasErrors(['name', 'type', 'frequency']);
    }

    public function test_edit_returns_200(): void
    {
        $source = LeadSource::factory()->create();

        $response = $this->get(route('lead-sources.edit', $source));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('LeadSources/Edit'));
    }

    public function test_update_updates_source_and_redirects(): void
    {
        $source = LeadSource::factory()->create();

        $response = $this->put(route('lead-sources.update', $source), [
            'name' => 'Updated Source',
            'type' => 'linkedin',
            'frequency' => 'hourly',
        ]);

        $response->assertRedirect(route('lead-sources.index'));
        $this->assertEquals('Updated Source', $source->fresh()->name);
    }

    public function test_destroy_deletes_source_and_redirects(): void
    {
        $source = LeadSource::factory()->create();

        $response = $this->delete(route('lead-sources.destroy', $source));

        $response->assertRedirect(route('lead-sources.index'));
        $this->assertDatabaseMissing('lead_sources', ['id' => $source->id]);
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->post('/logout');

        $this->get(route('lead-sources.index'))->assertRedirect(route('login'));
    }
}
