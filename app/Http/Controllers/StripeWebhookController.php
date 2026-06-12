<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\HandleStripeWebhookAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly HandleStripeWebhookAction $action
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('billing.stripe.webhook_secret');

        if (!$webhookSecret) {
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $this->action->execute($event->type, $event->toArray());

        return response()->json(['received' => true]);
    }
}
