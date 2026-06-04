<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ActivityService
{
    public function __construct(
        private readonly WebhookService $webhookService
    ) {}

    public function log(Model $subject, string $event, string $description, array $metadata = []): Activity
    {
        $activity = Activity::create([
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'event' => $event,
            'description' => $description,
            'metadata' => $metadata,
        ]);

        $this->webhookService->dispatch($event, $subject);

        return $activity;
    }

    public function getFor(Model $subject): Collection
    {
        return $subject->activities()->latest()->get();
    }

    public function recent(int $limit = 10): Collection
    {
        return Activity::with('subject')->latest()->limit($limit)->get();
    }

    public function countByEvent(string $event, ?string $since = null): int
    {
        $query = Activity::where('event', $event);

        if ($since) {
            $query->where('created_at', '>=', $since);
        }

        return $query->count();
    }
}
