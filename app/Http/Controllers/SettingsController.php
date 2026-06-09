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
        'maroni_enabled',
        'maroni_base_url',
        'maroni_api_key',
        'maroni_webhook_secret',
        'trello_api_key',
        'trello_api_token',
        'resend_api_key',
        'resend_webhook_secret',
        'mail_from_address',
        'mail_from_name',
        'openai_api_key',
        'openai_model',
    ];

    public function edit(): Response
    {
        $user = request()->user();
        $user->completeOnboardingStep('integrations');
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
                'maroni_enabled' => 'maroni.base_url',
                'maroni_base_url' => 'maroni.base_url',
                'maroni_api_key' => 'maroni.api_key',
                'maroni_webhook_secret' => 'maroni.webhook_secret',
                'trello_api_key' => 'trello.api_key',
                'trello_api_token' => 'trello.api_token',
                'resend_api_key' => 'services.resend.key',
                'resend_webhook_secret' => 'services.resend.webhook_secret',
                'mail_from_address' => 'mail.from.address',
                'mail_from_name' => 'mail.from.name',
                'openai_api_key' => 'services.openai.api_key',
                'openai_model' => 'services.openai.model',
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
            'maroni_enabled' => ['nullable', 'string', 'in:true,false'],
            'maroni_base_url' => ['nullable', 'url', 'max:255'],
            'maroni_api_key' => ['nullable', 'string', 'max:255'],
            'maroni_webhook_secret' => ['nullable', 'string', 'max:255'],
            'trello_api_key' => ['nullable', 'string', 'max:255'],
            'trello_api_token' => ['nullable', 'string', 'max:255'],
            'resend_api_key' => ['nullable', 'string', 'max:255'],
            'resend_webhook_secret' => ['nullable', 'string', 'max:255'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            'openai_api_key' => ['nullable', 'string', 'max:255'],
            'openai_model' => ['nullable', 'string', 'max:255'],
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
