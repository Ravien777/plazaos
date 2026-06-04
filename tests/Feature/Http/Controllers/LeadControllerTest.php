<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadControllerTest extends TestCase
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
        Lead::factory()->count(3)->create();

        $response = $this->get(route('leads.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Leads/Index'));
    }

    public function test_index_paginates_results(): void
    {
        Lead::factory()->count(30)->create();

        $response = $this->get(route('leads.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Leads/Index')
            ->has('leads.data', 25)
        );
    }

    public function test_store_creates_lead_and_redirects(): void
    {
        $data = [
            'company_name' => 'New Corp',
            'contact_name' => 'John Doe',
            'email' => 'john@newcorp.com',
        ];

        $response = $this->post(route('leads.store'), $data);

        $response->assertRedirect(route('leads.index'));
        $this->assertDatabaseHas('leads', ['company_name' => 'New Corp']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->post(route('leads.store'), []);

        $response->assertSessionHasErrors(['company_name', 'contact_name']);
    }

    public function test_show_returns_200_with_relations(): void
    {
        $lead = Lead::factory()->create();

        $response = $this->get(route('leads.show', $lead));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Leads/Show')
            ->has('lead')
        );
    }

    public function test_update_updates_lead_and_redirects(): void
    {
        $lead = Lead::factory()->create();

        $response = $this->put(route('leads.update', $lead), [
            'company_name' => 'Updated Corp',
            'contact_name' => 'Jane',
        ]);

        $response->assertRedirect(route('leads.show', $lead));
        $this->assertEquals('Updated Corp', $lead->fresh()->company_name);
    }

    public function test_destroy_deletes_lead_and_redirects(): void
    {
        $lead = Lead::factory()->create();

        $response = $this->delete(route('leads.destroy', $lead));

        $response->assertRedirect(route('leads.index'));
        $this->assertSoftDeleted($lead);
    }

    public function test_index_sorts_by_company_name_asc(): void
    {
        Lead::factory()->create(['company_name' => 'Beta']);
        Lead::factory()->create(['company_name' => 'Alpha']);

        $response = $this->get(route('leads.index', ['sort_field' => 'company_name', 'sort_direction' => 'asc']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Leads/Index')
            ->has('leads.data', 2)
        );
    }

    public function test_index_sorts_by_created_at_desc_default(): void
    {
        Lead::factory()->create(['company_name' => 'First']);
        Lead::factory()->create(['company_name' => 'Second']);

        $response = $this->get(route('leads.index'));

        $response->assertOk();
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->post('/logout');

        $this->get(route('leads.index'))->assertRedirect(route('login'));
    }
}
