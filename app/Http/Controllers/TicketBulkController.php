<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TicketBulkController extends Controller
{
    public function __construct(
        private readonly TicketService $ticketService
    ) {}

    public function destroy(Request $request): RedirectResponse
    {
        $this->authorize('delete', new Ticket);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', 'exists:tickets,id'],
        ]);

        try {
            $this->ticketService->bulkArchive($data['ids']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to archive tickets. Please try again.');
        }

        return redirect()->back()->with('success', count($data['ids']) . ' ticket(s) archived successfully.');
    }

    public function forceDestroy(Request $request): RedirectResponse
    {
        $this->authorize('delete', new Ticket);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', 'exists:tickets,id'],
        ]);

        try {
            $this->ticketService->bulkForceDelete($data['ids']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete tickets. Please try again.');
        }

        return redirect()->back()->with('success', count($data['ids']) . ' ticket(s) permanently deleted.');
    }

    public function updateStatus(Request $request): RedirectResponse
    {
        $this->authorize('update', new Ticket);

        $statuses = array_map(fn (TicketStatus $case) => $case->value, TicketStatus::cases());

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', 'exists:tickets,id'],
            'status' => ['required', 'string', 'in:' . implode(',', $statuses)],
        ]);

        try {
            $this->ticketService->bulkUpdateStatus($data['ids'], $data['status']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update tickets. Please try again.');
        }

        return redirect()->back()->with('success', count($data['ids']) . ' ticket(s) updated successfully.');
    }
}
