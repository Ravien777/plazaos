<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UserSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    private const SETTING_KEYS = [
        'zoom_enabled',
        'zoom_client_id',
        'zoom_client_secret',
        'zoom_account_id',
        'teams_enabled',
        'teams_client_id',
        'teams_client_secret',
        'teams_tenant_id',
        'google_calendar_enabled',
        'google_calendar_id',
        'google_calendar_credentials',
    ];

    public function edit(): Response
    {
        $user = request()->user();
        $settings = [];

        foreach (self::SETTING_KEYS as $key) {
            $dbValue = $user->getSetting($key);
            $settings[$key] = $dbValue ?? config(match ($key) {
                'zoom_enabled' => 'services.zoom.enabled',
                'zoom_client_id' => 'services.zoom.client_id',
                'zoom_client_secret' => 'services.zoom.client_secret',
                'zoom_account_id' => 'services.zoom.account_id',
                'teams_enabled' => 'services.microsoft_teams.enabled',
                'teams_client_id' => 'services.microsoft_teams.client_id',
                'teams_client_secret' => 'services.microsoft_teams.client_secret',
                'teams_tenant_id' => 'services.microsoft_teams.tenant_id',
                'google_calendar_enabled' => 'google-calendar.enabled',
                'google_calendar_id' => 'google-calendar.calendar_id',
                'google_calendar_credentials' => 'google-calendar.credentials_path',
                default => null,
            });
        }

        return Inertia::render('Settings/Integrations', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'zoom_enabled' => ['nullable', 'string', 'in:true,false'],
            'zoom_client_id' => ['nullable', 'string', 'max:255'],
            'zoom_client_secret' => ['nullable', 'string', 'max:255'],
            'zoom_account_id' => ['nullable', 'string', 'max:255'],
            'teams_enabled' => ['nullable', 'string', 'in:true,false'],
            'teams_client_id' => ['nullable', 'string', 'max:255'],
            'teams_client_secret' => ['nullable', 'string', 'max:255'],
            'teams_tenant_id' => ['nullable', 'string', 'max:255'],
            'google_calendar_enabled' => ['nullable', 'string', 'in:true,false'],
            'google_calendar_id' => ['nullable', 'string', 'max:255'],
            'google_calendar_credentials' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        foreach ($validated as $key => $value) {
            UserSetting::updateOrCreate(
                ['user_id' => $user->id, 'key' => $key],
                ['value' => $value],
            );
        }

        $user->load('settings');

        return redirect()->back()->with('success', 'Integration settings saved.');
    }
}
