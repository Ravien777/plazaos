<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $client = Auth::guard('client')->user()->client;

        $client->load(['projects', 'meetings']);

        $stats = [
            'projectsCount' => $client->projects->count(),
            'openTicketsCount' => Ticket::where('ticketable_type', 'client')
                ->where('ticketable_id', $client->id)
                ->whereNotIn('status', ['closed'])
                ->count(),
            'upcomingMeetingsCount' => Meeting::where('meetable_type', 'client')
                ->where('meetable_id', $client->id)
                ->where('status', 'scheduled')
                ->where('start_time', '>=', now())
                ->count(),
        ];

        $recentTickets = Ticket::where('ticketable_type', 'client')
            ->where('ticketable_id', $client->id)
            ->latest()
            ->take(5)
            ->get();

        $upcomingMeetings = Meeting::where('meetable_type', 'client')
            ->where('meetable_id', $client->id)
            ->where('status', 'scheduled')
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->take(5)
            ->get();

        return Inertia::render('Portal/Dashboard', [
            'stats' => $stats,
            'recentTickets' => $recentTickets,
            'upcomingMeetings' => $upcomingMeetings,
        ]);
    }
}
