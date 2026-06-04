<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Meeting\StoreMeetingRequest;
use App\Http\Requests\Meeting\UpdateMeetingRequest;
use App\Models\Meeting;
use App\Services\MeetingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MeetingController extends Controller
{
    public function __construct(
        private readonly MeetingService $meetingService
    ) {}

    public function upcoming(): \Inertia\Response
    {
        $this->authorize('viewAny', Meeting::class);

        $meetings = $this->meetingService->upcoming(25);

        return Inertia::render('Meetings/Index', [
            'meetings' => $meetings,
        ]);
    }

    public function index(string $meetableType, string $id): JsonResponse
    {
        $this->authorize('viewAny', Meeting::class);

        $model = $this->resolveMeetable($meetableType, $id);

        if (!$model) {
            abort(404);
        }

        return response()->json([
            'data' => $this->meetingService->getFor($model),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Meeting::class);

        return Inertia::render('Meetings/Create');
    }

    public function store(StoreMeetingRequest $request): RedirectResponse
    {
        $this->authorize('create', Meeting::class);

        try {
            $this->meetingService->create($request->validated());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to schedule meeting. Please try again.');
        }

        return redirect()->route('dashboard')->with('success', 'Meeting scheduled.');
    }

    public function storeForMeetable(StoreMeetingRequest $request, string $meetableType, string $id): RedirectResponse
    {
        $this->authorize('create', Meeting::class);

        $model = $this->resolveMeetable($meetableType, $id);

        if (!$model) {
            abort(404);
        }

        try {
            $this->meetingService->create(array_merge($request->validated(), [
                'meetable_type' => $model->getMorphClass(),
                'meetable_id' => $model->getKey(),
            ]));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to schedule meeting. Please try again.');
        }

        return redirect()->back()->with('success', 'Meeting scheduled.');
    }

    public function show(Meeting $meeting): Response
    {
        $this->authorize('view', $meeting);

        $meeting->load('meetable');

        return Inertia::render('Meetings/Show', [
            'meeting' => $meeting,
        ]);
    }

    public function edit(Meeting $meeting): Response
    {
        $this->authorize('update', $meeting);

        $meeting->load('meetable');

        return Inertia::render('Meetings/Edit', [
            'meeting' => $meeting,
        ]);
    }

    public function update(UpdateMeetingRequest $request, Meeting $meeting): RedirectResponse
    {
        $this->authorize('update', $meeting);

        try {
            $this->meetingService->update($meeting, $request->validated());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update meeting. Please try again.');
        }

        return redirect()->back()->with('success', 'Meeting updated.');
    }

    public function destroy(Meeting $meeting): RedirectResponse
    {
        $this->authorize('delete', $meeting);

        try {
            $this->meetingService->delete($meeting);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to cancel meeting. Please try again.');
        }

        return redirect()->back()->with('success', 'Meeting cancelled.');
    }

    private function resolveMeetable(string $type, string $id): ?Model
    {
        $modelClass = match ($type) {
            'client' => \App\Models\Client::class,
            'lead' => \App\Models\Lead::class,
            'project' => \App\Models\Project::class,
            default => null,
        };

        if (!$modelClass) {
            return null;
        }

        return $modelClass::find($id);
    }
}
