<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\Notifications\TicketClosed;
use App\Notifications\TicketCreated;
use App\Notifications\TicketReplied;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class TicketService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Ticket::with('ticketable');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['ticketable_type'])) {
            $query->where('ticketable_type', $filters['ticketable_type']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(25);
    }

    public function create(array $data): Ticket
    {
        $ticket = Ticket::create($data);

        activity()->log($ticket, 'ticket.created', "Ticket \"{$ticket->subject}\" was created.");
        Notification::send(User::first(), new TicketCreated($ticket));

        return $ticket;
    }

    public function update(Ticket $ticket, array $data): Ticket
    {
        $ticket->update($data);

        activity()->log($ticket, 'ticket.updated', "Ticket \"{$ticket->subject}\" was updated.");

        return $ticket;
    }

    public function delete(Ticket $ticket): void
    {
        activity()->log($ticket, 'ticket.deleted', "Ticket \"{$ticket->subject}\" was deleted.");

        $ticket->delete();
    }

    public function close(Ticket $ticket): Ticket
    {
        $ticket->update(['status' => TicketStatus::Closed]);

        activity()->log($ticket, 'ticket.closed', "Ticket \"{$ticket->subject}\" was closed.");
        Notification::send(User::first(), new TicketClosed($ticket));

        return $ticket;
    }

    public function reopen(Ticket $ticket): Ticket
    {
        $ticket->update(['status' => TicketStatus::Open]);

        activity()->log($ticket, 'ticket.reopened', "Ticket \"{$ticket->subject}\" was reopened.");

        return $ticket;
    }

    public function reply(Ticket $ticket, string $body, ?User $user = null): TicketReply
    {
        $user = $user ?? Auth::user();

        $reply = $ticket->replies()->create([
            'user_id' => $user->id,
            'body' => $body,
        ]);

        activity()->log($ticket, 'ticket.replied', "New reply on \"{$ticket->subject}\" by {$user->name}.");
        Notification::send(User::first(), new TicketReplied($ticket));

        return $reply;
    }
}
