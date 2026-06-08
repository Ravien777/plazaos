<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Portal;

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketReplyControllerTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private Client $otherClient;
    private ClientUser $user;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();

        $this->client = Client::factory()->create();
        $this->otherClient = Client::factory()->create();
        $this->user = ClientUser::factory()->create(['client_id' => $this->client->id]);
        $this->actingAs($this->user, 'client');
    }

    public function test_store_creates_reply(): void
    {
        $ticket = Ticket::factory()->create([
            'ticketable_type' => 'client',
            'ticketable_id' => $this->client->id,
        ]);

        $response = $this->post(route('portal.tickets.replies.store', $ticket), [
            'body' => 'This is a reply.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'body' => 'This is a reply.',
        ]);
    }

    public function test_store_validates_body(): void
    {
        $ticket = Ticket::factory()->create([
            'ticketable_type' => 'client',
            'ticketable_id' => $this->client->id,
        ]);

        $response = $this->post(route('portal.tickets.replies.store', $ticket), [
            'body' => '',
        ]);

        $response->assertSessionHasErrors(['body']);
    }

    public function test_store_forbidden_for_other_client(): void
    {
        $ticket = Ticket::factory()->create([
            'ticketable_type' => 'client',
            'ticketable_id' => $this->otherClient->id,
        ]);

        $response = $this->post(route('portal.tickets.replies.store', $ticket), [
            'body' => 'Should not work.',
        ]);

        $response->assertForbidden();
    }

    public function test_guest_redirects(): void
    {
        auth('client')->logout();

        $ticket = Ticket::factory()->create([
            'ticketable_type' => 'client',
            'ticketable_id' => $this->client->id,
        ]);

        $response = $this->post(route('portal.tickets.replies.store', $ticket), [
            'body' => 'Should be redirected.',
        ]);

        $response->assertRedirect(route('portal.login'));
    }
}
