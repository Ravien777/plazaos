<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Portal;

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketControllerTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private Client $otherClient;
    private ClientUser $user;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create(['id' => 1]);

        $this->client = Client::factory()->create();
        $this->otherClient = Client::factory()->create();
        $this->user = ClientUser::factory()->create(['client_id' => $this->client->id]);
        $this->actingAs($this->user, 'client');
    }

    public function test_index_shows_only_own_tickets(): void
    {
        Ticket::factory()->create([
            'ticketable_type' => 'client',
            'ticketable_id' => $this->client->id,
            'subject' => 'Our Issue',
            'user_id' => 1,
        ]);
        Ticket::factory()->create([
            'ticketable_type' => 'client',
            'ticketable_id' => $this->otherClient->id,
            'subject' => 'Their Issue',
            'user_id' => 1,
        ]);

        $response = $this->get(route('portal.tickets.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Portal/Tickets/Index')
            ->has('tickets.data', 1)
            ->where('tickets.data.0.subject', 'Our Issue')
        );
    }

    public function test_create_renders(): void
    {
        $response = $this->get(route('portal.tickets.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Portal/Tickets/Create'));
    }

    public function test_store_creates_ticket_linked_to_client(): void
    {
        $response = $this->post(route('portal.tickets.store'), [
            'subject' => 'Portal Issue',
            'description' => 'Having trouble with login.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'subject' => 'Portal Issue',
            'ticketable_type' => 'client',
            'ticketable_id' => $this->client->id,
        ]);
    }

    public function test_show_returns_own_ticket(): void
    {
        $ticket = Ticket::factory()->create([
            'ticketable_type' => 'client',
            'ticketable_id' => $this->client->id,
            'user_id' => 1,
        ]);

        $response = $this->get(route('portal.tickets.show', $ticket));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Portal/Tickets/Show'));
    }

    public function test_cannot_view_other_clients_ticket(): void
    {
        $ticket = Ticket::factory()->create([
            'ticketable_type' => 'client',
            'ticketable_id' => $this->otherClient->id,
            'user_id' => 1,
        ]);

        $response = $this->get(route('portal.tickets.show', $ticket));

        $response->assertForbidden();
    }
}
