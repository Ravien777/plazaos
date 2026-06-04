<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\MeetingProviderInterface;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class ZoomMeetingProvider implements MeetingProviderInterface
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

            $duration = $meeting->end_time
                ? (int) ceil($meeting->start_time->diffInMinutes($meeting->end_time))
                : 60;

            $response = Http::withToken($token)->post('https://api.zoom.us/v2/users/me/meetings', [
                'topic' => $meeting->title,
                'type' => 2,
                'start_time' => $meeting->start_time->toIso8601String(),
                'duration' => $duration,
                'timezone' => config('app.timezone', 'UTC'),
                'settings' => [
                    'host_video' => true,
                    'participant_video' => true,
                    'join_before_host' => false,
                    'approval_type' => 0,
                    'mute_upon_entry' => true,
                    'waiting_room' => false,
                ],
            ]);

            if ($response->failed()) {
                activity()->log($meeting, 'meeting.calendar_error', "Zoom API error (create): {$response->body()}");
                return null;
            }

            $data = $response->json();
            $meetingId = (string) $data['id'];
            $joinUrl = $data['join_url'] ?? null;

            $updates = ['google_event_id' => $meetingId];
            if ($joinUrl) {
                $updates['meet_link'] = $joinUrl;
            }
            $meeting->update($updates);

            activity()->log($meeting, 'meeting.calendar_created', "Zoom meeting created for {$meeting->title}.");

            return $meetingId;
        } catch (\Exception $e) {
            activity()->log($meeting, 'meeting.calendar_error', "Failed to create Zoom meeting: {$e->getMessage()}");
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

            $duration = $meeting->end_time
                ? (int) ceil($meeting->start_time->diffInMinutes($meeting->end_time))
                : 60;

            $response = Http::withToken($token)->patch(
                "https://api.zoom.us/v2/meetings/{$meeting->google_event_id}",
                [
                    'topic' => $meeting->title,
                    'start_time' => $meeting->start_time->toIso8601String(),
                    'duration' => $duration,
                    'timezone' => config('app.timezone', 'UTC'),
                ]
            );

            if ($response->failed()) {
                activity()->log($meeting, 'meeting.calendar_error', "Zoom API error (update): {$response->body()}");
                return;
            }

            $data = $response->json();
            if ($joinUrl = $data['join_url'] ?? null) {
                $meeting->update(['meet_link' => $joinUrl]);
            }

            activity()->log($meeting, 'meeting.calendar_updated', "Zoom meeting updated for {$meeting->title}.");
        } catch (\Exception $e) {
            activity()->log($meeting, 'meeting.calendar_error', "Failed to update Zoom meeting: {$e->getMessage()}");
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
                "https://api.zoom.us/v2/meetings/{$meeting->google_event_id}"
            );

            if ($response->failed()) {
                activity()->log($meeting, 'meeting.calendar_error', "Zoom API error (delete): {$response->body()}");
                return;
            }

            activity()->log($meeting, 'meeting.calendar_deleted', "Zoom meeting deleted for {$meeting->title}.");
        } catch (\Exception $e) {
            activity()->log($meeting, 'meeting.calendar_error', "Failed to delete Zoom meeting: {$e->getMessage()}");
        }
    }

    public function isEnabled(): bool
    {
        $user = User::first();
        $dbValue = $user?->getSetting('zoom_enabled');

        return $dbValue ?? config('services.zoom.enabled', false);
    }

    private function setting(string $key): mixed
    {
        $user = User::first();
        $dbValue = $user?->getSetting("zoom_{$key}");

        return $dbValue ?? config("services.zoom.{$key}");
    }

    private function getAccessToken(): ?string
    {
        if ($this->accessToken && $this->tokenExpiresAt && now()->timestamp < $this->tokenExpiresAt) {
            return $this->accessToken;
        }

        $clientId = $this->setting('client_id');
        $clientSecret = $this->setting('client_secret');
        $accountId = $this->setting('account_id');

        if (!$clientId || !$clientSecret || !$accountId) {
            return null;
        }

        $response = Http::withBasicAuth($clientId, $clientSecret)->post('https://zoom.us/oauth/token', [
            'grant_type' => 'account_credentials',
            'account_id' => $accountId,
        ]);

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();
        $this->accessToken = $data['access_token'] ?? null;
        $this->tokenExpiresAt = now()->timestamp + ($data['expires_in'] ?? 3600) - 60;

        return $this->accessToken;
    }
}
