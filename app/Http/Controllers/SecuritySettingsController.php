<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SecuritySettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load('passkeys');

        $twoFactor = [
            'enabled' => ! is_null($user->two_factor_secret),
            'confirmed' => ! is_null($user->two_factor_confirmed_at),
        ];

        if ($twoFactor['enabled'] && ! $twoFactor['confirmed']) {
            $twoFactor['qrCodeSvg'] = $user->twoFactorQrCodeSvg();
            $twoFactor['recoveryCodes'] = $user->recoveryCodes();
        }

        return Inertia::render('Settings/Security', [
            'twoFactor' => $twoFactor,
            'passkeys' => $user->passkeys->map(fn ($pk) => [
                'id' => $pk->id,
                'name' => $pk->name,
                'authenticator' => $pk->authenticator,
                'created_at' => $pk->created_at?->diffForHumans(),
                'last_used_at' => $pk->last_used_at?->diffForHumans(),
            ]),
        ]);
    }
}
