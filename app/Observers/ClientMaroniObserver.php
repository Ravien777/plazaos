<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Client;
use App\Services\MaroniService;

class ClientMaroniObserver
{
    public function __construct(
        private readonly MaroniService $maroniService
    ) {}

    public function created(Client $client): void
    {
        if (!$this->maroniService->isConfigured()) {
            return;
        }

        $this->maroniService->syncClient($client);
    }

    public function updated(Client $client): void
    {
        if (!$this->maroniService->isConfigured()) {
            return;
        }

        if (!$client->maroni_client_id) {
            return;
        }

        $this->maroniService->syncClient($client);
    }

    public function restored(Client $client): void
    {
        $this->created($client);
    }
}
