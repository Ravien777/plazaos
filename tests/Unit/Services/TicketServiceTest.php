<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TicketServiceTest extends TestCase
{
    use RefreshDatabase;

    private TicketService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $user = User::factory()->create(['id' => 1]);
        $this->actingAs($user);
        $this->service = app(TicketService::class);
    }

    public function test_list_returns_paginated_results(): void
    {
        Ticket::factory()->count(5)->create();

        $result = $this->service->list();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(5, $result->items());
    }

    public function test_create_creates_ticket_and_sends_notification(): void
    {
        $ticket = $this->service->create([
            'subject' => 'Test ticket',
            'priority' => 'high',
            'category' => 'bug_report',
            'user_id' => 1,
        ]);

        $this->assertDatabaseHas('tickets', ['subject' => 'Test ticket']);
        Notification::assertSentTo(User::first(), \App\Notifications\TicketCreated::class);
    }

    public function test_update_modifies_ticket(): void
    {
        $ticket = Ticket::factory()->create(['subject' => 'Original']);

        $this->service->update($ticket, ['subject' => 'Updated']);

        $this->assertEquals('Updated', $ticket->fresh()->subject);
    }

    public function test_delete_removes_ticket(): void
    {
        $ticket = Ticket::factory()->create();

        $this->service->delete($ticket);

        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
    }

    public function test_close_sets_status_and_sends_notification(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'open']);

        $this->service->close($ticket);

        $this->assertEquals(TicketStatus::Closed, $ticket->fresh()->status);
        Notification::assertSentTo(User::first(), \App\Notifications\TicketClosed::class);
    }

    public function test_reopen_sets_status_to_open(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'closed']);

        $this->service->reopen($ticket);

        $this->assertEquals(TicketStatus::Open, $ticket->fresh()->status);
    }

    public function test_reply_creates_reply_and_sends_notification(): void
    {
        $ticket = Ticket::factory()->create();
        $user = User::first();

        $reply = $this->service->reply($ticket, 'A test reply', $user);

        $this->assertInstanceOf(TicketReply::class, $reply);
        $this->assertEquals('A test reply', $reply->body);
        $this->assertEquals($user->id, $reply->user_id);
        Notification::assertSentTo(User::first(), \App\Notifications\TicketReplied::class);
    }
}
