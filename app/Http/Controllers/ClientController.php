<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GeneratePortalTokenAction;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Models\Client;
use App\Services\ClientService;
use App\Services\MaroniService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function __construct(
        private readonly ClientService $clientService
    ) {}

    public function show(Client $client): Response
    {
        $this->authorize('view', $client);

        $client->load(['notes', 'activities', 'emails', 'projects', 'documents', 'meetings', 'tickets', 'portalUsers', 'intakeFormSubmissions.form']);

        $maroniConfigured = app(MaroniService::class)->isConfigured();

        return Inertia::render('Clients/Show', [
            'client' => $client,
            'maroniConfigured' => $maroniConfigured,
        ]);
    }

    public function generatePortalLink(Client $client, GeneratePortalTokenAction $action): JsonResponse
    {
        $this->authorize('update', $client);

        $client = $action->execute($client);

        return response()->json([
            'portal_token' => $client->portal_token,
            'portal_token_expires_at' => $client->portal_token_expires_at->toISOString(),
            'url' => url('/portal/token/' . $client->portal_token),
        ]);
    }

    public function index(): Response
    {
        $this->authorize('viewAny', Client::class);

        $clients = $this->clientService->list(request()->only(['search', 'status', 'sort_field', 'sort_direction']));

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => request()->only(['search', 'status', 'sort_field', 'sort_direction']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Client::class);

        return Inertia::render('Clients/Create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $this->authorize('create', Client::class);

        try {
            $this->clientService->create($request->validated());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create client. Please try again.');
        }

        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }



    public function edit(Client $client): Response
    {
        $this->authorize('update', $client);

        return Inertia::render('Clients/Edit', [
            'client' => $client,
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        try {
            $this->clientService->update($client, $request->validated());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update client. Please try again.');
        }

        return redirect()->route('clients.show', $client)->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        try {
            $this->clientService->delete($client);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete client. Please try again.');
        }

        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }

    public function trash(): Response
    {
        $this->authorize('viewTrash', Client::class);

        $clients = $this->clientService->listTrashed(request()->only(['search', 'sort_field', 'sort_direction']));

        return Inertia::render('Clients/Trash', [
            'clients' => $clients,
            'filters' => request()->only(['search', 'sort_field', 'sort_direction']),
        ]);
    }

    public function forceDestroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $client->forceDelete();

        return redirect()->route('clients.trash')->with('success', 'Client permanently deleted.');
    }

    public function restore(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $client->restore();

        return redirect()->back()->with('success', 'Client restored successfully.');
    }
}
