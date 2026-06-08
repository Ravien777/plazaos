<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\MaroniService;
use Illuminate\Console\Command;

class SyncClientsToMaroni extends Command
{
    protected $signature = 'maroni:sync-clients {--new : Only sync clients without maroni_client_id}';

    protected $description = 'Sync all clients to Maroni.';

    public function handle(MaroniService $maroniService): int
    {
        if (!$maroniService->isConfigured()) {
            $this->error('Maroni is not configured. Set MARONI_BASE_URL and MARONI_API_KEY.');
            return self::FAILURE;
        }

        $query = Client::query();

        if ($this->option('new')) {
            $query->whereNull('maroni_client_id');
        }

        $clients = $query->get();

        if ($clients->isEmpty()) {
            $this->info('No clients to sync.');
            return self::SUCCESS;
        }

        $success = 0;
        $failed = 0;

        foreach ($clients as $client) {
            $result = $maroniService->syncClient($client);

            if ($result) {
                $success++;
                $this->line("Synced: {$client->company_name}");
            } else {
                $failed++;
                $this->error("Failed: {$client->company_name}");
            }
        }

        $this->info("Sync complete. {$success} synced, {$failed} failed.");

        return self::SUCCESS;
    }
}
