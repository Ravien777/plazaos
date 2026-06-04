<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(): Response
    {
        $client = Auth::guard('client')->user()->client;

        $tickets = Ticket::where('ticketable_type', 'client')
            ->where('ticketable_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return Inertia::render('Portal/Tickets/Index', [
            'tickets' => $tickets,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Portal/Tickets/Create');
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $client = Auth::guard('client')->user()->client;

        $ticket = Ticket::create([
            'subject' => $request->subject,
            'description' => $request->description,
            'status' => 'open',
            'priority' => $request->priority ?? 'medium',
            'category' => $request->category ?? 'support',
            'ticketable_type' => 'client',
            'ticketable_id' => $client->id,
            'user_id' => 1,
        ]);

        activity()->log($ticket, 'ticket.created', "Ticket \"{$ticket->subject}\" was submitted via portal by {$client->company_name}.");

        return redirect()->route('portal.tickets.show', $ticket)->with('success', 'Ticket submitted successfully.');
    }

    public function show(Ticket $ticket): Response
    {
        $client = Auth::guard('client')->user()->client;

        abort_if($ticket->ticketable_type !== 'client' || $ticket->ticketable_id !== $client->id, 403);

        $ticket->load('replies');

        return Inertia::render('Portal/Tickets/Show', [
            'ticket' => $ticket,
        ]);
    }
}
