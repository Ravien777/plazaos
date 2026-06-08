<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientBulkController extends Controller
{
    public function __construct(
        private readonly ClientService $clientService
    ) {}

    public function destroy(Request $request): RedirectResponse
    {
        $this->authorize('delete', new Client);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', 'exists:clients,id'],
        ]);

        try {
            $this->clientService->bulkDelete($data['ids']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete clients. Please try again.');
        }

        return redirect()->back()->with('success', count($data['ids']) . ' client(s) deleted successfully.');
    }

    public function forceDestroy(Request $request): RedirectResponse
    {
        $this->authorize('delete', new Client);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', 'exists:clients,id'],
        ]);

        try {
            $this->clientService->bulkForceDelete($data['ids']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to permanently delete clients. Please try again.');
        }

        return redirect()->back()->with('success', count($data['ids']) . ' client(s) permanently deleted.');
    }

    public function updateStatus(Request $request): RedirectResponse
    {
        $this->authorize('update', new Client);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', 'exists:clients,id'],
            'status' => ['required', 'string', 'in:active,inactive,archived'],
        ]);

        try {
            $this->clientService->bulkUpdateStatus($data['ids'], $data['status']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update clients. Please try again.');
        }

        return redirect()->back()->with('success', count($data['ids']) . ' client(s) updated successfully.');
    }
}
