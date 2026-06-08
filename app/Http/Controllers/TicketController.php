<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Models\Client;
use App\Models\Project;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketService $ticketService
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Ticket::class);

        $tickets = $this->ticketService->list(request()->only(['search', 'status', 'priority', 'category', 'ticketable_type']));

        return Inertia::render('Tickets/Index', [
            'tickets' => $tickets,
            'filters' => request()->only(['search', 'status', 'priority', 'category']),
        ]);
    }

    public function searchEntities(): JsonResponse
    {
        $type = request()->query('type');
        $query = request()->query('q', '');

        return match ($type) {
            'client' => response()->json(
                Client::where('company_name', 'ilike', "%{$query}%")
                    ->select('id', 'company_name')
                    ->orderBy('company_name')
                    ->limit(10)
                    ->get()
                    ->map(fn (Client $c) => ['id' => $c->id, 'label' => $c->company_name, 'type' => 'client'])
            ),
            'project' => response()->json(
                Project::where('name', 'ilike', "%{$query}%")
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->limit(10)
                    ->get()
                    ->map(fn (Project $p) => ['id' => $p->id, 'label' => $p->name, 'type' => 'project'])
            ),
            default => response()->json([]),
        };
    }

    public function create(): Response
    {
        $this->authorize('create', Ticket::class);

        return Inertia::render('Tickets/Create');
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $this->authorize('create', Ticket::class);

        $data = $request->validated();

        if (!empty($data['ticketable_type']) && !empty($data['ticketable_id'])) {
            $modelClass = match ($data['ticketable_type']) {
                'client' => Client::class,
                'project' => Project::class,
                default => null,
            };

            if ($modelClass) {
                $model = $modelClass::find($data['ticketable_id']);

                if ($model) {
                    $data['ticketable_type'] = $model->getMorphClass();
                }
            }
        }

        $data['user_id'] = auth()->id();

        try {
            $this->ticketService->create($data);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create ticket. Please try again.');
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket created successfully.');
    }

    public function show(Ticket $ticket): Response
    {
        $this->authorize('view', $ticket);

        $ticket->load(['ticketable', 'replies.user', 'activities']);

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
        ]);
    }

    public function edit(Ticket $ticket): Response
    {
        $this->authorize('update', $ticket);

        $ticket->load('ticketable');

        return Inertia::render('Tickets/Edit', [
            'ticket' => $ticket,
        ]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        try {
            $this->ticketService->update($ticket, $request->validated());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update ticket. Please try again.');
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket updated successfully.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $this->authorize('delete', $ticket);

        try {
            $this->ticketService->delete($ticket);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete ticket. Please try again.');
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket deleted successfully.');
    }

    public function trash(): Response
    {
        $this->authorize('viewTrash', Ticket::class);

        $tickets = $this->ticketService->listTrashed(request()->only(['status']));

        return Inertia::render('Tickets/Trash', [
            'tickets' => $tickets,
            'filters' => request()->only(['status']),
        ]);
    }

    public function forceDestroy(Ticket $ticket): RedirectResponse
    {
        $this->authorize('delete', $ticket);

        $ticket->forceDelete();

        return redirect()->route('tickets.trash')->with('success', 'Ticket permanently deleted.');
    }

    public function restore(Ticket $ticket): RedirectResponse
    {
        $this->authorize('delete', $ticket);

        $ticket->restore();

        return redirect()->back()->with('success', 'Ticket restored successfully.');
    }

    public function close(Ticket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        try {
            $this->ticketService->close($ticket);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to close ticket. Please try again.');
        }

        return redirect()->back()->with('success', 'Ticket closed.');
    }

    public function reopen(Ticket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        try {
            $this->ticketService->reopen($ticket);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to reopen ticket. Please try again.');
        }

        return redirect()->back()->with('success', 'Ticket reopened.');
    }
}
