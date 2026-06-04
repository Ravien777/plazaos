<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CalendarController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Meeting::class);

        $teamMembers = User::where('team_id', auth()->user()->team_id)
            ->select('id', 'name')
            ->get();

        $today = now()->format('Y-m-d');
        $todayMeetings = Meeting::with('user')
            ->whereDate('start_time', $today)
            ->where('status', 'scheduled')
            ->orderBy('start_time')
            ->get();

        return Inertia::render('Calendar/Index', [
            'teamMembers' => $teamMembers,
            'todayMeetings' => $todayMeetings,
        ]);
    }

    public function events(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Meeting::class);

        $validated = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
        ]);

        $meetings = Meeting::with('user')
            ->whereBetween('start_time', [$validated['start'], $validated['end']])
            ->where('status', 'scheduled')
            ->orderBy('start_time')
            ->get()
            ->map(fn (Meeting $m) => [
                'id' => $m->id,
                'title' => $m->title,
                'start_time' => $m->start_time->toIso8601String(),
                'end_time' => $m->end_time?->toIso8601String(),
                'status' => $m->status->value,
                'user_id' => $m->user_id,
                'user' => $m->user ? ['id' => $m->user->id, 'name' => $m->user->name] : null,
            ]);

        return response()->json($meetings);
    }

    public function exportIcs(Meeting $meeting): Response
    {
        $this->authorize('view', $meeting);

        $start = $meeting->start_time->format('Ymd\THis');
        $end = $meeting->end_time?->format('Ymd\THis') ?? $meeting->start_time->addHour()->format('Ymd\THis');
        $uid = $meeting->id . '@plazaos';
        $now = now()->format('Ymd\THis');
        $description = str_replace(["\r", "\n"], ['', '\\n'], $meeting->description ?? '');
        $title = str_replace(',', '\,', $meeting->title);

        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//PlazaOS//Calendar//EN',
            'BEGIN:VEVENT',
            'UID:' . $uid,
            'DTSTAMP:' . $now,
            'DTSTART:' . $start,
            'DTEND:' . $end,
            'SUMMARY:' . $title,
            'DESCRIPTION:' . $description,
            'LOCATION:' . ($meeting->location ?? ''),
            'END:VEVENT',
            'END:VCALENDAR',
        ]);

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="meeting-' . $meeting->id . '.ics"',
        ]);
    }
}
