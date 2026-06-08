<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Enums\TicketStatus;
use App\Models\Client;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketControllerTest extends TestCase
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
        Ticket::factory()->count(3)->create();

        $response = $this->get(route('tickets.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Tickets/Index'));
    }

    public function test_index_filters_by_status(): void
    {
        Ticket::factory()->create(['status' => 'open']);
        Ticket::factory()->create(['status' => 'closed']);

        $response = $this->get(route('tickets.index', ['status' => 'open']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Tickets/Index')
            ->has('tickets.data', 1)
        );
    }

    public function test_create_returns_200(): void
    {
        $response = $this->get(route('tickets.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Tickets/Create'));
    }

    public function test_store_creates_ticket_and_redirects(): void
    {
        $response = $this->post(route('tickets.store'), [
            'subject' => 'Login page broken',
            'description' => 'Cannot log in with valid credentials',
            'priority' => 'high',
            'category' => 'bug_report',
        ]);

        $response->assertRedirect(route('tickets.index'));
        $this->assertDatabaseHas('tickets', ['subject' => 'Login page broken']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->post(route('tickets.store'), []);

        $response->assertSessionHasErrors(['subject']);
    }

    public function test_store_with_ticketable_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->post(route('tickets.store'), [
            'subject' => 'Client issue',
            'ticketable_type' => 'client',
            'ticketable_id' => $client->id,
        ]);

        $response->assertRedirect(route('tickets.index'));
        $this->assertDatabaseHas('tickets', [
            'subject' => 'Client issue',
            'ticketable_type' => 'App\Models\Client',
            'ticketable_id' => $client->id,
        ]);
    }

    public function test_store_with_ticketable_project(): void
    {
        $project = Project::factory()->create();

        $response = $this->post(route('tickets.store'), [
            'subject' => 'Project issue',
            'ticketable_type' => 'project',
            'ticketable_id' => $project->id,
        ]);

        $response->assertRedirect(route('tickets.index'));
        $this->assertDatabaseHas('tickets', [
            'subject' => 'Project issue',
            'ticketable_type' => 'App\Models\Project',
            'ticketable_id' => $project->id,
        ]);
    }

    public function test_show_returns_200_with_relations(): void
    {
        $ticket = Ticket::factory()->create();

        $response = $this->get(route('tickets.show', $ticket));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Tickets/Show')
            ->has('ticket')
        );
    }

    public function test_edit_returns_200(): void
    {
        $ticket = Ticket::factory()->create();

        $response = $this->get(route('tickets.edit', $ticket));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Tickets/Edit'));
    }

    public function test_update_updates_ticket_and_redirects(): void
    {
        $ticket = Ticket::factory()->create(['subject' => 'Old title']);

        $response = $this->put(route('tickets.update', $ticket), [
            'subject' => 'Updated title',
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));
        $this->assertEquals('Updated title', $ticket->fresh()->subject);
    }

    public function test_destroy_deletes_ticket_and_redirects(): void
    {
        $ticket = Ticket::factory()->create();

        $response = $this->delete(route('tickets.destroy', $ticket));

        $response->assertRedirect(route('tickets.index'));
        $this->assertSoftDeleted($ticket);
    }

    public function test_close_sets_status_to_closed(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'open']);

        $response = $this->post(route('tickets.close', $ticket));

        $response->assertRedirect();
        $this->assertEquals(TicketStatus::Closed, $ticket->fresh()->status);
    }

    public function test_reopen_sets_status_to_open(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'closed']);

        $response = $this->post(route('tickets.reopen', $ticket));

        $response->assertRedirect();
        $this->assertEquals(TicketStatus::Open, $ticket->fresh()->status);
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->post('/logout');

        $this->get(route('tickets.index'))->assertRedirect(route('login'));
    }
}
