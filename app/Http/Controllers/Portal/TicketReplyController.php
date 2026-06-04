<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\TicketReply\StoreTicketReplyRequest;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class TicketReplyController extends Controller
{
    public function store(StoreTicketReplyRequest $request, Ticket $ticket): RedirectResponse
    {
        $client = Auth::guard('client')->user()->client;

        abort_if($ticket->ticketable_type !== 'client' || $ticket->ticketable_id !== $client->id, 403);

        $ticket->replies()->create([
            'user_id' => 1,
            'body' => $request->body,
        ]);

        activity()->log($ticket, 'ticket.replied', "Client replied to \"{$ticket->subject}\".");

        return redirect()->back()->with('success', 'Reply added.');
    }
}
