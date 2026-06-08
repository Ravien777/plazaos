<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class MaroniService
{
    public function isConfigured(): bool
    {
        return !empty($this->setting('api_key')) && !empty($this->setting('base_url'));
    }

    public function syncClient(Client $client): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->post("{$this->setting('base_url')}/api/plazaos-webhook", [
                    'event' => $client->maroni_client_id ? 'client.updated' : 'client.created',
                    'client' => [
                        'plazaos_client_id' => $client->id,
                        'company_name' => $client->company_name,
                        'contact_name' => $client->contact_name,
                        'email' => $client->email,
                        'phone' => $client->phone,
                        'website' => $client->website,
                        'industry' => $client->industry,
                        'city' => $client->city,
                        'country' => $client->country,
                        'status' => $client->status,
                    ],
                ]);

            if ($response->successful()) {
                $maroniClientId = $response->json('client_id') ?? $client->maroni_client_id ?? $client->id;

                $client->update([
                    'maroni_client_id' => $maroniClientId,
                    'maroni_sync_status' => 'synced',
                    'last_maroni_synced_at' => now(),
                ]);

                activity()->log($client, 'maroni.synced', "Client synced to Maroni.");

                return $maroniClientId;
            }

            $client->update(['maroni_sync_status' => 'failed']);
            activity()->log($client, 'maroni.sync_failed', "Failed to sync client to Maroni: {$response->status()}");

            return null;
        } catch (\Exception $e) {
            $client->update(['maroni_sync_status' => 'failed']);
            activity()->log($client, 'maroni.sync_failed', "Failed to sync client to Maroni: {$e->getMessage()}");

            return null;
        }
    }

    public function getClientInvoices(Client $client): array
    {
        if (!$this->isConfigured() || !$client->maroni_client_id) {
            return [];
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->get("{$this->setting('base_url')}/api/clients/{$client->maroni_client_id}/invoices");

            return $response->successful() ? ($response->json('invoices') ?? []) : [];
        } catch (\Exception $e) {
            activity()->log($client, 'maroni.fetch_failed', "Failed to fetch invoices: {$e->getMessage()}");
            return [];
        }
    }

    public function getClientExpenses(Client $client): array
    {
        if (!$this->isConfigured() || !$client->maroni_client_id) {
            return [];
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->get("{$this->setting('base_url')}/api/clients/{$client->maroni_client_id}/expenses");

            return $response->successful() ? ($response->json('expenses') ?? []) : [];
        } catch (\Exception $e) {
            activity()->log($client, 'maroni.fetch_failed', "Failed to fetch expenses: {$e->getMessage()}");
            return [];
        }
    }

    public function getClientSummary(Client $client): ?array
    {
        if (!$this->isConfigured() || !$client->maroni_client_id) {
            return null;
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->get("{$this->setting('base_url')}/api/clients/{$client->maroni_client_id}/summary");

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getDashboardSummary(): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->get("{$this->setting('base_url')}/api/dashboard/summary");

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function headers(): array
    {
        return [
            'X-API-Key' => $this->setting('api_key'),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    private function setting(string $key): mixed
    {
        $user = User::first();
        $dbValue = $user?->getSetting("maroni_{$key}");

        return $dbValue ?? config("maroni.{$key}");
    }
}
