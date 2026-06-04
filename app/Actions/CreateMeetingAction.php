<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\MeetingProviderInterface;
use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Services\MeetingProviderFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateMeetingAction
{
    public function __construct(
        private readonly MeetingProviderFactory $providerFactory
    ) {}

    public function execute(Model $meetable, array $data): Meeting
    {
        $meeting = Meeting::create([
            'meetable_type' => $meetable->getMorphClass(),
            'meetable_id' => $meetable->getKey(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'] ?? null,
            'location' => $data['location'] ?? null,
            'meet_link' => $data['meet_link'] ?? null,
            'status' => MeetingStatus::Scheduled,
            'user_id' => Auth::id(),
        ]);

        activity()->log($meeting, 'meeting.created', "Meeting {$meeting->title} was scheduled.");

        $provider = $this->providerFactory->make($data['provider'] ?? 'google_meet');
        $provider->createEvent($meeting);

        return $meeting;
    }
}
