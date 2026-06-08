<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\MaroniService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaroniWebhookController extends Controller
{
    public function __construct(
        private readonly MaroniService $maroniService
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $signature = $request->header('X-Webhook-Signature');
        $payload = $request->getContent();
        $secret = config('maroni.webhook_secret');

        if (!$secret) {
            return response()->json(['error' => 'Webhook not configured.'], 500);
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        if (!$signature || !hash_equals($expected, $signature)) {
            return response()->json(['error' => 'Invalid signature.'], 401);
        }

        $event = $request->input('event');
        $data = $request->input('data', []);

        $clientId = $data['client_id'] ?? null;

        if (!$clientId) {
            return response()->json(['error' => 'Missing client_id.'], 400);
        }

        $client = Client::where('maroni_client_id', $clientId)->first();

        if (!$client) {
            return response()->json(['error' => 'Client not found.'], 404);
        }

        match ($event) {
            'invoice.created' => activity()->log($client, 'maroni.invoice_created', "Invoice {$data['invoice_id']} created in Maroni."),
            'invoice.paid' => activity()->log($client, 'maroni.invoice_paid', "Invoice {$data['invoice_id']} paid in Maroni."),
            'client.updated' => $this->maroniService->syncClient($client),
            default => activity()->log($client, 'maroni.event_received', "Maroni event received: {$event}"),
        };

        return response()->json(['status' => 'ok']);
    }
}
