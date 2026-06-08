<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResendWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $secret = $this->webhookSecret();

        if ($secret) {
            if (!$this->verifySignature($request, $secret)) {
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

    private function webhookSecret(): ?string
    {
        $user = User::first();
        $dbValue = $user?->getSetting('resend_webhook_secret');

        return $dbValue ?? config('services.resend.webhook_secret');
    }

    private function verifySignature(Request $request, string $secret): bool
    {
        $header = $request->header('Resend-Signature');

        if (!$header) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $header) as $pair) {
            $pair = trim($pair);
            if (str_contains($pair, '=')) {
                [$key, $value] = explode('=', $pair, 2);
                $parts[trim($key)] = trim($value);
            }
        }

        $timestamp = $parts['t'] ?? null;
        $signature = $parts['s'] ?? null;

        if (!$timestamp || !$signature) {
            return false;
        }

        $payload = $timestamp . '.' . $request->getContent();
        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }
}
