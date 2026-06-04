<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Meeting;
use App\Models\User;
use App\Notifications\MeetingScheduled;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class MeetingService
{
    public function __construct(
        private readonly MeetingProviderFactory $providerFactory
    ) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Meeting::query()->with('meetable');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['upcoming'])) {
            $query->where('start_time', '>=', now())->orderBy('start_time');
        } else {
            $query->orderBy('start_time', 'desc');
        }

        return $query->paginate(25);
    }

    public function getFor(Model $meetable): Collection
    {
        return $meetable->meetings()->orderBy('start_time', 'desc')->get();
    }

    public function upcoming(int $limit = 10): Collection
    {
        return Meeting::with('meetable')
            ->where('start_time', '>=', now())
            ->where('status', 'scheduled')
            ->orderBy('start_time')
            ->limit($limit)
            ->get();
    }

    public function upcomingCount(): int
    {
        return Meeting::where('start_time', '>=', now())
            ->where('status', 'scheduled')
            ->count();
    }

    public function create(array $data): Meeting
    {
        $meeting = Meeting::create($data);

        activity()->log($meeting, 'meeting.created', "Meeting {$meeting->title} was scheduled.");
        Notification::send(User::all(), new MeetingScheduled($meeting));

        $provider = $this->providerFactory->make($data['provider'] ?? 'google_meet');
        $provider->createEvent($meeting);

        return $meeting;
    }

    public function update(Meeting $meeting, array $data): Meeting
    {
        $meeting->update($data);

        activity()->log($meeting, 'meeting.updated', "Meeting {$meeting->title} was updated.");

        $provider = $this->providerFactory->make($meeting->provider ?? 'google_meet');
        $provider->updateEvent($meeting);

        return $meeting;
    }

    public function delete(Meeting $meeting): void
    {
        $provider = $this->providerFactory->make($meeting->provider ?? 'google_meet');
        $provider->deleteEvent($meeting);

        activity()->log($meeting, 'meeting.deleted', "Meeting {$meeting->title} was cancelled.");

        $meeting->delete();
    }
}
