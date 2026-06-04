<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Email;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResendWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $secret = config('services.resend.webhook_secret');

        if ($secret) {
            $signature = $request->header('Resend-Signature');

            if (!$signature || !hash_equals($secret, $signature)) {
                return response()->json(['error' => 'Invalid signature.'], 401);
            }
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return response()->json(['error' => 'Invalid payload.'], 400);
        }

        $type = $payload['type'] ?? '';

        if ($type !== 'email.opened') {
            return response()->json(['status' => 'ignored']);
        }

        $emailData = $payload['data'] ?? [];
        $externalId = $emailData['email_id'] ?? null;

        if (!$externalId) {
            return response()->json(['error' => 'Missing email_id.'], 400);
        }

        $email = Email::where('external_id', $externalId)->first();

        if (!$email) {
            return response()->json(['error' => 'Email not found.'], 404);
        }

        $email->update(['opened_at' => now()]);

        return response()->json(['status' => 'ok']);
    }
}
