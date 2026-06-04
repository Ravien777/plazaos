<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PortalUserController extends Controller
{
    public function index(Client $client): array
    {
        $this->authorize('view', $client);

        return [
            'data' => $client->portalUsers()->select('id', 'client_id', 'name', 'email', 'created_at')->get(),
        ];
    }

    public function store(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('client_users', 'email')],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $data['password'] ??= \Illuminate\Support\Str::random(60);

        $client->portalUsers()->create($data);

        return redirect()->back()->with('success', 'Portal user created.');
    }

    public function destroy(Client $client, ClientUser $portalUser): RedirectResponse
    {
        $this->authorize('update', $client);

        abort_if($portalUser->client_id !== $client->id, 404);

        $portalUser->delete();

        return redirect()->back()->with('success', 'Portal user removed.');
    }
}
