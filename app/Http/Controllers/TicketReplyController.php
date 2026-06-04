<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TicketReply\StoreTicketReplyRequest;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;

class TicketReplyController extends Controller
{
    public function __construct(
        private readonly TicketService $ticketService
    ) {}

    public function store(StoreTicketReplyRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('create', \App\Models\TicketReply::class);

        try {
            $this->ticketService->reply($ticket, $request->validated()['body']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to add reply. Please try again.');
        }

        return redirect()->back()->with('success', 'Reply added.');
    }
}
