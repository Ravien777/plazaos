<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\MeetingProviderInterface;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class TeamsMeetingProvider implements MeetingProviderInterface
{
    private ?string $accessToken = null;

    private ?int $tokenExpiresAt = null;

    public function createEvent(Meeting $meeting): ?string
    {
        if (!$this->isEnabled()) {
            return null;
        }

        try {
            $token = $this->getAccessToken();

            if (!$token) {
                return null;
            }

            $response = Http::withToken($token)->post(
                'https://graph.microsoft.com/v1.0/users/onlineMeetings',
                [
                    'subject' => $meeting->title,
                    'startDateTime' => $meeting->start_time->toIso8601String(),
                    'endDateTime' => ($meeting->end_time ?? $meeting->start_time->copy()->addHour())->toIso8601String(),
                ]
            );

            if ($response->failed()) {
                activity()->log($meeting, 'meeting.calendar_error', "Teams API error (create): {$response->body()}");
                return null;
            }

            $data = $response->json();
            $meetingId = $data['id'] ?? null;
            $joinUrl = $data['joinUrl'] ?? null;

            if (!$meetingId) {
                activity()->log($meeting, 'meeting.calendar_error', 'Teams API returned no meeting ID.');
                return null;
            }

            $updates = ['google_event_id' => $meetingId];
            if ($joinUrl) {
                $updates['meet_link'] = $joinUrl;
            }
            $meeting->update($updates);

            activity()->log($meeting, 'meeting.calendar_created', "Teams meeting created for {$meeting->title}.");

            return $meetingId;
        } catch (\Exception $e) {
            activity()->log($meeting, 'meeting.calendar_error', "Failed to create Teams meeting: {$e->getMessage()}");
            return null;
        }
    }

    public function updateEvent(Meeting $meeting): void
    {
        if (!$this->isEnabled() || !$meeting->google_event_id) {
            return;
        }

        try {
            $token = $this->getAccessToken();

            if (!$token) {
                return;
            }

            $response = Http::withToken($token)->patch(
                "https://graph.microsoft.com/v1.0/users/onlineMeetings/{$meeting->google_event_id}",
                [
                    'subject' => $meeting->title,
                    'startDateTime' => $meeting->start_time->toIso8601String(),
                    'endDateTime' => ($meeting->end_time ?? $meeting->start_time->copy()->addHour())->toIso8601String(),
                ]
            );

            if ($response->failed()) {
                activity()->log($meeting, 'meeting.calendar_error', "Teams API error (update): {$response->body()}");
                return;
            }

            $data = $response->json();
            if ($joinUrl = $data['joinUrl'] ?? null) {
                $meeting->update(['meet_link' => $joinUrl]);
            }

            activity()->log($meeting, 'meeting.calendar_updated', "Teams meeting updated for {$meeting->title}.");
        } catch (\Exception $e) {
            activity()->log($meeting, 'meeting.calendar_error', "Failed to update Teams meeting: {$e->getMessage()}");
        }
    }

    public function deleteEvent(Meeting $meeting): void
    {
        if (!$this->isEnabled() || !$meeting->google_event_id) {
            return;
        }

        try {
            $token = $this->getAccessToken();

            if (!$token) {
                return;
            }

            $response = Http::withToken($token)->delete(
                "https://graph.microsoft.com/v1.0/users/onlineMeetings/{$meeting->google_event_id}"
            );

            if ($response->failed()) {
                activity()->log($meeting, 'meeting.calendar_error', "Teams API error (delete): {$response->body()}");
                return;
            }

            activity()->log($meeting, 'meeting.calendar_deleted', "Teams meeting deleted for {$meeting->title}.");
        } catch (\Exception $e) {
            activity()->log($meeting, 'meeting.calendar_error', "Failed to delete Teams meeting: {$e->getMessage()}");
        }
    }

    public function isEnabled(): bool
    {
        $user = User::first();
        $dbValue = $user?->getSetting('teams_enabled');

        return $dbValue ?? config('services.microsoft_teams.enabled', false);
    }

    private function setting(string $key): mixed
    {
        $user = User::first();
        $dbValue = $user?->getSetting("teams_{$key}");

        return $dbValue ?? config("services.microsoft_teams.{$key}");
    }

    private function getAccessToken(): ?string
    {
        if ($this->accessToken && $this->tokenExpiresAt && now()->timestamp < $this->tokenExpiresAt) {
            return $this->accessToken;
        }

        $clientId = $this->setting('client_id');
        $clientSecret = $this->setting('client_secret');
        $tenantId = $this->setting('tenant_id');

        if (!$clientId || !$clientSecret || !$tenantId) {
            return null;
        }

        $response = Http::asForm()->post(
            "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token",
            [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'https://graph.microsoft.com/.default',
            ]
        );

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();
        $this->accessToken = $data['access_token'] ?? null;
        $this->tokenExpiresAt = now()->timestamp + ($data['expires_in'] ?? 3600) - 60;

        return $this->accessToken;
    }
}
