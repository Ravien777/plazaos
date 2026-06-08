<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_index_returns_200(): void
    {
        Client::factory()->count(3)->create();

        $response = $this->get(route('clients.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Clients/Index'));
    }

    public function test_store_creates_client_and_redirects(): void
    {
        $data = [
            'company_name' => 'Client Corp',
            'contact_name' => 'Jane Doe',
            'email' => 'jane@clientcorp.com',
        ];

        $response = $this->post(route('clients.store'), $data);

        $response->assertRedirect(route('clients.index'));
        $this->assertDatabaseHas('clients', ['company_name' => 'Client Corp']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->post(route('clients.store'), []);

        $response->assertSessionHasErrors(['company_name', 'contact_name']);
    }

    public function test_show_returns_200_with_all_relations(): void
    {
        $client = Client::factory()->create();
        $client->projects()->save(Project::factory()->make());

        $response = $this->get(route('clients.show', $client));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Clients/Show')
            ->has('client')
        );
    }

    public function test_update_updates_client_and_redirects(): void
    {
        $client = Client::factory()->create();

        $response = $this->put(route('clients.update', $client), [
            'company_name' => 'Updated Client',
            'contact_name' => 'John',
        ]);

        $response->assertRedirect(route('clients.show', $client));
        $this->assertEquals('Updated Client', $client->fresh()->company_name);
    }

    public function test_destroy_deletes_client_and_redirects(): void
    {
        $client = Client::factory()->create();

        $response = $this->delete(route('clients.destroy', $client));

        $response->assertRedirect(route('clients.index'));
        $this->assertSoftDeleted($client);
    }

    public function test_index_sorts_by_company_name_asc(): void
    {
        Client::factory()->create(['company_name' => 'Beta']);
        Client::factory()->create(['company_name' => 'Alpha']);

        $response = $this->get(route('clients.index', ['sort_field' => 'company_name', 'sort_direction' => 'asc']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Clients/Index')
            ->has('clients.data', 2)
        );
    }

    public function test_index_sorts_by_created_at_desc_default(): void
    {
        Client::factory()->create(['company_name' => 'First']);
        Client::factory()->create(['company_name' => 'Second']);

        $response = $this->get(route('clients.index'));

        $response->assertOk();
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->post('/logout');

        $this->get(route('clients.index'))->assertRedirect(route('login'));
    }
}
