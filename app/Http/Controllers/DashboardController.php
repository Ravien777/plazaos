<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\LeadStatus;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Testimonial;
use App\Models\Ticket;
use App\Services\ActivityService;
use App\Services\MaroniService;
use App\Services\MeetingService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ActivityService $activityService,
        private readonly MeetingService $meetingService,
        private readonly MaroniService $maroniService
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

        $user = auth()->user();

        $financialSummary = $this->maroniService->getDashboardSummary();

        $defaultStatCards = [
            'stat-new-leads',
            'stat-active-leads',
            'stat-active-clients',
            'stat-open-projects',
            'stat-open-tickets',
            'stat-upcoming-meetings',
        ];

        if ($this->maroniService->isConfigured()) {
            array_splice($defaultStatCards, 3, 0, ['stat-maroni-revenue', 'stat-maroni-outstanding']);
        }

        $defaultLayout = [
            'stat_cards' => $defaultStatCards,
            'bottom_widgets' => [
                'meetings',
                'activity',
                'wall-of-love',
            ],
        ];

        $stats = [
            'newLeads' => $newLeads,
            'activeLeads' => $activeLeads,
            'activeClients' => Client::where('status', 'active')->count(),
            'openProjects' => Project::whereNotIn('status', ['completed'])->count(),
            'openTickets' => Ticket::whereNotIn('status', ['closed'])->count(),
            'upcomingMeetings' => $this->meetingService->upcomingCount(),
        ];

        if ($financialSummary) {
            $stats['monthlyRevenue'] = $financialSummary['monthlyRevenue'] ?? 0;
            $stats['outstandingTotal'] = $financialSummary['outstandingTotal'] ?? 0;
        }

        return Inertia::render('Dashboard', [
            'hasTeam' => $user->team_id !== null,
            'stats' => $stats,
            'recentActivities' => $this->activityService->recent(10),
            'upcomingMeetings' => $this->meetingService->upcoming(5),
            'recentTestimonials' => Testimonial::approved()
                ->where('rating', 5)
                ->with('client:id,company_name')
                ->latest()
                ->take(5)
                ->get(),
            'dashboardLayout' => $user->dashboard_layout ?? $defaultLayout,
            'hasMaroni' => $this->maroniService->isConfigured(),
        ]);
    }
}
