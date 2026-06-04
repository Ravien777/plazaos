<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Document;
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

        $client->load(['projects', 'meetings', 'activities']);

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

        $projects = $client->projects()->orderBy('created_at', 'desc')->get(['id', 'name', 'status', 'progress_percentage']);

        $recentDocuments = Document::where('documentable_type', 'client')
            ->where('documentable_id', $client->id)
            ->latest()
            ->take(5)
            ->get();

        $recentActivities = $client->activities()->latest()->take(10)->get();

        return Inertia::render('Portal/Dashboard', [
            'stats' => $stats,
            'recentTickets' => $recentTickets,
            'upcomingMeetings' => $upcomingMeetings,
            'projects' => $projects,
            'recentDocuments' => $recentDocuments,
            'recentActivities' => $recentActivities,
            'companyName' => $client->company_name,
            'companyEmail' => $client->email,
        ]);
    }
}
