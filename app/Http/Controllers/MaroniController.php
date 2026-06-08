<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\MaroniService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaroniController extends Controller
{
    public function __construct(
        private readonly MaroniService $maroniService
    ) {}

    public function summary(Client $client): JsonResponse
    {
        $this->authorize('view', $client);

        return response()->json([
            'configured' => $this->maroniService->isConfigured() && $client->maroni_client_id !== null,
            'summary' => $this->maroniService->getClientSummary($client),
        ]);
    }

    public function invoices(Client $client): JsonResponse
    {
        $this->authorize('view', $client);

        return response()->json([
            'configured' => $this->maroniService->isConfigured(),
            'invoices' => $this->maroniService->getClientInvoices($client),
        ]);
    }

    public function expenses(Client $client): JsonResponse
    {
        $this->authorize('view', $client);

        return response()->json([
            'configured' => $this->maroniService->isConfigured(),
            'expenses' => $this->maroniService->getClientExpenses($client),
        ]);
    }

    public function syncAll(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Client::class);

        if (!$this->maroniService->isConfigured()) {
            return response()->json(['error' => 'Maroni not configured.'], 400);
        }

        $clients = Client::whereNull('maroni_client_id')->get();
        $count = 0;

        foreach ($clients as $client) {
            if ($this->maroniService->syncClient($client)) {
                $count++;
            }
        }

        activity()->log(auth()->user(), 'maroni.bulk_sync', "Synced {$count} clients to Maroni.");

        return response()->json(['synced' => $count]);
    }
}
