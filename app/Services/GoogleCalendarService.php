<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\MeetingProviderInterface;
use App\Models\Meeting;
use App\Models\User;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\ConferenceData;
use Google\Service\Calendar\CreateConferenceRequest;

class GoogleCalendarService implements MeetingProviderInterface
{
    private ?GoogleClient $client = null;

    public function isEnabled(): bool
    {
        $user = User::first();
        $dbValue = $user?->getSetting('google_calendar_enabled');

        return $dbValue ?? config('google-calendar.enabled', false);
    }

    private function setting(string $key): mixed
    {
        $user = User::first();
        $dbValue = $user?->getSetting("google_calendar_{$key}");

        return $dbValue ?? config("google-calendar.{$key}");
    }

    public function createEvent(Meeting $meeting): ?string
    {
        if (!$this->isEnabled()) {
            return null;
        }

        try {
            $client = $this->getClient();

            if (!$client) {
                return null;
            }

            $service = new Calendar($client);

            $event = new Event([
                'summary' => $meeting->title,
                'description' => $meeting->description ?? '',
                'start' => [
                    'dateTime' => $meeting->start_time->toIso8601String(),
                    'timeZone' => config('app.timezone', 'UTC'),
                ],
                'end' => [
                    'dateTime' => ($meeting->end_time ?? $meeting->start_time->copy()->addHour())->toIso8601String(),
                    'timeZone' => config('app.timezone', 'UTC'),
                ],
                'location' => $meeting->location ?? '',
            ]);

            $conferenceData = new ConferenceData();
            $createRequest = new CreateConferenceRequest();
            $createRequest->setRequestId($meeting->id);
            $conferenceData->setCreateRequest($createRequest);
            $event->setConferenceData($conferenceData);

            $createdEvent = $service->events->insert(
                $this->setting('id') ?? 'primary',
                $event,
                ['conferenceDataVersion' => 1]
            );

            $eventId = $createdEvent->getId();

            $meeting->update([
                'google_event_id' => $eventId,
            ]);

            $meetLink = null;
            if ($createdEvent->getHangoutLink()) {
                $meetLink = $createdEvent->getHangoutLink();
                $meeting->update(['meet_link' => $meetLink]);
            }

            activity()->log($meeting, 'meeting.calendar_created', "Google Calendar event created for {$meeting->title}.");

            return $eventId;
        } catch (\Exception $e) {
            activity()->log($meeting, 'meeting.calendar_error', "Failed to create Google Calendar event: {$e->getMessage()}");

            return null;
        }
    }

    public function updateEvent(Meeting $meeting): void
    {
        if (!$this->isEnabled() || !$meeting->google_event_id) {
            return;
        }

        try {
            $client = $this->getClient();

            if (!$client) {
                return;
            }

            $service = new Calendar($client);

            $event = $service->events->get(
                $this->setting('id') ?? 'primary',
                $meeting->google_event_id
            );

            $event->setSummary($meeting->title);
            $event->setDescription($meeting->description ?? '');
            $event->setLocation($meeting->location ?? '');

            $event->setStart(new \Google\Service\Calendar\EventDateTime([
                'dateTime' => $meeting->start_time->toIso8601String(),
                'timeZone' => config('app.timezone', 'UTC'),
            ]));

            $event->setEnd(new \Google\Service\Calendar\EventDateTime([
                'dateTime' => ($meeting->end_time ?? $meeting->start_time->copy()->addHour())->toIso8601String(),
                'timeZone' => config('app.timezone', 'UTC'),
            ]));

            $service->events->update(
                $this->setting('id') ?? 'primary',
                $meeting->google_event_id,
                $event
            );

            activity()->log($meeting, 'meeting.calendar_updated', "Google Calendar event updated for {$meeting->title}.");
        } catch (\Exception $e) {
            activity()->log($meeting, 'meeting.calendar_error', "Failed to update Google Calendar event: {$e->getMessage()}");
        }
    }

    public function deleteEvent(Meeting $meeting): void
    {
        if (!$this->isEnabled() || !$meeting->google_event_id) {
            return;
        }

        try {
            $client = $this->getClient();

            if (!$client) {
                return;
            }

            $service = new Calendar($client);

            $service->events->delete(
                $this->setting('id') ?? 'primary',
                $meeting->google_event_id
            );

            activity()->log($meeting, 'meeting.calendar_deleted', "Google Calendar event deleted for {$meeting->title}.");
        } catch (\Exception $e) {
            activity()->log($meeting, 'meeting.calendar_error', "Failed to delete Google Calendar event: {$e->getMessage()}");
        }
    }

    private function getClient(): ?GoogleClient
    {
        if ($this->client === null) {
            $credentialsJson = $this->setting('credentials');
            $credentialsPath = $this->setting('credentials_path');

            $client = new GoogleClient();
            $client->setApplicationName(config('app.name', 'AgencyOS'));
            $client->setScopes([Calendar::CALENDAR_EVENTS]);

            if ($credentialsJson && json_decode($credentialsJson, true)) {
                $client->setAuthConfig(json_decode($credentialsJson, true));
            } elseif ($credentialsPath && file_exists($credentialsPath)) {
                $client->setAuthConfig($credentialsPath);
            } else {
                return null;
            }

            $this->client = $client;
        }

        return $this->client;
    }
}
