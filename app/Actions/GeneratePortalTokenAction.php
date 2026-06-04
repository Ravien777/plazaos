<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Client;
use Illuminate\Support\Str;

class GeneratePortalTokenAction
{
    public function execute(Client $client): Client
    {
        $client->update([
            'portal_token' => (string) Str::uuid(),
            'portal_token_expires_at' => now()->addDays(7),
        ]);

        return $client->fresh();
    }
}
