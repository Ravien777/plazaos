<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Webhook\StoreWebhookRequest;
use App\Http\Requests\Webhook\UpdateWebhookRequest;
use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WebhookController extends Controller
{
    public function __construct(
        private readonly WebhookService $webhookService
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Webhook::class);

        return Inertia::render('Settings/Webhooks/Index', [
            'webhooks' => $this->webhookService->list(),
            'allowedEvents' => collect(Webhook::allowedEvents())->map(fn ($label, $key) => [
                'value' => $key,
                'label' => $label,
            ])->values(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Webhook::class);

        return Inertia::render('Settings/Webhooks/Create', [
            'allowedEvents' => collect(Webhook::allowedEvents())->map(fn ($label, $key) => [
                'value' => $key,
                'label' => $label,
            ])->values(),
        ]);
    }

    public function store(StoreWebhookRequest $request): RedirectResponse
    {
        $this->authorize('create', Webhook::class);

        try {
            $webhook = $this->webhookService->create($request->validated());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create webhook. Please try again.');
        }

        return redirect()->route('settings.webhooks')
            ->with('success', 'Webhook created successfully.')
            ->with('webhook_secret', $webhook->secret);
    }

    public function edit(Webhook $webhook): Response
    {
        $this->authorize('update', $webhook);

        return Inertia::render('Settings/Webhooks/Edit', [
            'webhook' => $webhook,
            'allowedEvents' => collect(Webhook::allowedEvents())->map(fn ($label, $key) => [
                'value' => $key,
                'label' => $label,
            ])->values(),
        ]);
    }

    public function update(UpdateWebhookRequest $request, Webhook $webhook): RedirectResponse
    {
        $this->authorize('update', $webhook);

        try {
            $this->webhookService->update($webhook, $request->validated());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update webhook. Please try again.');
        }

        return redirect()->route('settings.webhooks')->with('success', 'Webhook updated successfully.');
    }

    public function destroy(Webhook $webhook): RedirectResponse
    {
        $this->authorize('delete', $webhook);

        try {
            $this->webhookService->delete($webhook);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete webhook. Please try again.');
        }

        return redirect()->route('settings.webhooks')->with('success', 'Webhook deleted successfully.');
    }

    public function test(Webhook $webhook): RedirectResponse
    {
        $this->authorize('update', $webhook);

        $this->webhookService->dispatch('webhook.test', auth()->user());

        return redirect()->route('settings.webhooks')->with('success', 'Test webhook dispatched.');
    }
}
