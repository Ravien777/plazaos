<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PortalTokenController extends Controller
{
    public function login(string $token): RedirectResponse
    {
        $client = Client::where('portal_token', $token)
            ->where('portal_token_expires_at', '>=', now())
            ->first();

        if (!$client) {
            return redirect()->route('portal.login')->with('error', 'This link has expired or is invalid. Please contact your project manager for a new one.');
        }

        if (Auth::guard('client')->check()) {
            Auth::guard('client')->logout();
        }

        $clientUser = $client->portalUsers()->first();

        if (!$clientUser) {
            $clientUser = $client->portalUsers()->create([
                'name' => $client->contact_name,
                'email' => $client->email ?? $client->company_name . '@portal',
                'password' => Str::random(60),
            ]);
        }

        Auth::guard('client')->login($clientUser);

        request()->session()->regenerate();

        return redirect()->intended(route('portal.dashboard'));
    }
}
