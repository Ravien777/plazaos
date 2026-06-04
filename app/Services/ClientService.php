<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Client;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ClientService
{
    private const ALLOWED_SORT_FIELDS = [
        'company_name', 'contact_name', 'email', 'website', 'industry',
        'city', 'country', 'source', 'status', 'created_at', 'updated_at',
        'last_contacted_at',
    ];

    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Client::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('website', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%")
                    ->orWhere('source', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $sortField = $this->resolveSortField($filters['sort_field'] ?? null);
        $sortDirection = $this->resolveSortDirection($filters['sort_direction'] ?? null);

        $perPage = $filters['per_page'] ?? 25;

        return $query->orderBy($sortField, $sortDirection)->paginate((int) $perPage);
    }

    public function create(array $data): Client
    {
        $client = Client::create($data);

        activity()->log($client, 'client.created', "Client {$client->company_name} was created.");

        return $client;
    }

    public function update(Client $client, array $data): Client
    {
        $client->update($data);

        activity()->log($client, 'client.updated', "Client {$client->company_name} was updated.");

        return $client;
    }

    public function delete(Client $client): void
    {
        activity()->log($client, 'client.deleted', "Client {$client->company_name} was deleted.");

        $disk = config('filesystems.default') === 'r2' ? 'r2' : 'local';

        foreach ($client->documents()->get() as $document) {
            if ($document->path && Storage::disk($disk)->exists($document->path)) {
                Storage::disk($disk)->delete($document->path);
            }
        }

        $client->projects()->delete();
        $client->notes()->delete();
        $client->emails()->delete();
        $client->documents()->delete();
        $client->meetings()->delete();
        $client->tickets()->delete();

        $client->delete();
    }

    public function bulkDelete(array $ids): void
    {
        $clients = Client::whereIn('id', $ids)->get();
        $disk = config('filesystems.default') === 'r2' ? 'r2' : 'local';

        foreach ($clients as $client) {
            activity()->log($client, 'client.deleted', "Client {$client->company_name} was deleted (bulk).");

            foreach ($client->documents()->get() as $document) {
                if ($document->path && Storage::disk($disk)->exists($document->path)) {
                    Storage::disk($disk)->delete($document->path);
                }
            }

            $client->projects()->delete();
            $client->notes()->delete();
            $client->emails()->delete();
            $client->documents()->delete();
            $client->meetings()->delete();
            $client->tickets()->delete();

            $client->delete();
        }
    }

    public function bulkUpdateStatus(array $ids, string $status): void
    {
        $clients = Client::whereIn('id', $ids)->get();

        Client::whereIn('id', $ids)->update(['status' => $status]);

        foreach ($clients as $client) {
            activity()->log($client, 'client.updated', "Client {$client->company_name} status changed to {$status} (bulk).");
        }
    }

    private function resolveSortField(?string $field): string
    {
        if ($field && in_array($field, self::ALLOWED_SORT_FIELDS, true)) {
            return $field;
        }

        return 'created_at';
    }

    private function resolveSortDirection(?string $direction): string
    {
        return strtolower($direction ?? '') === 'asc' ? 'asc' : 'desc';
    }
}
