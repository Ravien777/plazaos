<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class NotificationPreferenceController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Settings/Notifications', [
            'preferences' => auth()->user()->notification_preferences ?? [],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'slack_enabled' => 'boolean',
            'digest_enabled' => 'boolean',
            'digest_time' => 'nullable|date_format:H:i',
        ]);

        auth()->user()->update([
            'notification_preferences' => $validated,
        ]);

        return back()->with('success', 'Notification preferences saved.');
    }
}
