<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketReplyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_store_creates_reply_and_redirects(): void
    {
        $ticket = Ticket::factory()->create();

        $response = $this->post(route('tickets.replies.store', $ticket), [
            'body' => 'This is a reply.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'body' => 'This is a reply.',
        ]);
    }

    public function test_store_validates_required_body(): void
    {
        $ticket = Ticket::factory()->create();

        $response = $this->post(route('tickets.replies.store', $ticket), []);

        $response->assertSessionHasErrors(['body']);
    }
}
