<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/search?q=test')
            ->assertUnauthorized();
    }

    public function test_requires_query_parameter(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/search')
            ->assertStatus(422);
    }

    public function test_requires_minimum_two_characters(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/search?q=a')
            ->assertStatus(422);
    }

    public function test_returns_empty_results_when_no_match(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/search?q=zzzzzzzzz')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'leads',
                    'clients',
                    'projects',
                    'meetings',
                    'tickets',
                    'tasks',
                ],
            ])
            ->assertJsonCount(0, 'data.leads')
            ->assertJsonCount(0, 'data.clients')
            ->assertJsonCount(0, 'data.projects')
            ->assertJsonCount(0, 'data.meetings')
            ->assertJsonCount(0, 'data.tickets')
            ->assertJsonCount(0, 'data.tasks');
    }

    public function test_searches_leads_by_company_name(): void
    {
        Lead::factory()->create(['company_name' => 'Acme Corp']);
        Lead::factory()->create(['company_name' => 'Other Inc']);

        $this->actingAs($this->user)
            ->getJson('/api/search?q=acme')
            ->assertOk()
            ->assertJsonCount(1, 'data.leads')
            ->assertJsonPath('data.leads.0.title', 'Acme Corp');
    }

    public function test_searches_clients_by_company_name(): void
    {
        Client::factory()->create(['company_name' => 'BigCo']);
        Client::factory()->create(['company_name' => 'SmallBiz']);

        $this->actingAs($this->user)
            ->getJson('/api/search?q=bigco')
            ->assertOk()
            ->assertJsonCount(1, 'data.clients');
    }

    public function test_searches_projects_by_name(): void
    {
        $project = Project::factory()->create(['name' => 'Website Redesign']);

        $this->actingAs($this->user)
            ->getJson('/api/search?q=website')
            ->assertOk()
            ->assertJsonCount(1, 'data.projects');
    }

    public function test_limits_to_five_results_per_type(): void
    {
        Lead::factory()->count(7)->create(['company_name' => 'Test Corp']);

        $this->actingAs($this->user)
            ->getJson('/api/search?q=test')
            ->assertOk()
            ->assertJsonCount(5, 'data.leads');
    }
}
