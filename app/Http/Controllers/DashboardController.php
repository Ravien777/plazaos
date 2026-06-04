<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\LeadStatus;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Ticket;
use App\Services\ActivityService;
use App\Services\MeetingService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ActivityService $activityService,
        private readonly MeetingService $meetingService
    ) {}

    public function index(): Response
    {
        $newLeads = Lead::where('status', LeadStatus::New)->count();
        $activeLeads = Lead::whereIn('status', [
            LeadStatus::Qualified,
            LeadStatus::Contacted,
            LeadStatus::Interested,
            LeadStatus::MeetingScheduled,
        ])->count();

        return Inertia::render('Dashboard', [
            'stats' => [
                'newLeads' => $newLeads,
                'activeLeads' => $activeLeads,
                'activeClients' => Client::where('status', 'active')->count(),
                'openProjects' => Project::whereNotIn('status', ['completed'])->count(),
                'openTickets' => Ticket::whereNotIn('status', ['closed'])->count(),
                'upcomingMeetings' => $this->meetingService->upcomingCount(),
            ],
            'recentActivities' => $this->activityService->recent(10),
            'upcomingMeetings' => $this->meetingService->upcoming(5),
        ]);
    }
}
