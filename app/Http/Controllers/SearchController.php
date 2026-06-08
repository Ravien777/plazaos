<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = $request->validate(['q' => ['required', 'string', 'min:2']])['q'];

        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';

        $leads = Lead::query()
            ->where(function ($query) use ($q, $like) {
                $query->where('company_name', $like, "%{$q}%")
                    ->orWhere('contact_name', $like, "%{$q}%")
                    ->orWhere('email', $like, "%{$q}%");
            })
            ->limit(5)
            ->get()
            ->map(fn (Lead $lead) => [
                'id' => $lead->id,
                'type' => 'lead',
                'title' => $lead->company_name,
                'subtitle' => $lead->contact_name ?? $lead->email,
                'url' => "/leads/{$lead->id}",
            ]);

        $clients = Client::query()
            ->where(function ($query) use ($q, $like) {
                $query->where('company_name', $like, "%{$q}%")
                    ->orWhere('contact_name', $like, "%{$q}%")
                    ->orWhere('email', $like, "%{$q}%");
            })
            ->limit(5)
            ->get()
            ->map(fn (Client $client) => [
                'id' => $client->id,
                'type' => 'client',
                'title' => $client->company_name,
                'subtitle' => $client->contact_name ?? $client->email,
                'url' => "/clients/{$client->id}",
            ]);

        $projects = Project::query()
            ->where(function ($query) use ($q, $like) {
                $query->where('name', $like, "%{$q}%")
                    ->orWhere('description', $like, "%{$q}%");
            })
            ->limit(5)
            ->get()
            ->map(fn (Project $project) => [
                'id' => $project->id,
                'type' => 'project',
                'title' => $project->name,
                'subtitle' => $project->client?->company_name ?? '',
                'url' => "/projects/{$project->id}",
            ]);

        $meetings = Meeting::query()
            ->where(function ($query) use ($q, $like) {
                $query->where('title', $like, "%{$q}%")
                    ->orWhere('description', $like, "%{$q}%");
            })
            ->limit(5)
            ->get()
            ->map(fn (Meeting $meeting) => [
                'id' => $meeting->id,
                'type' => 'meeting',
                'title' => $meeting->title,
                'subtitle' => $meeting->start_time?->format('M j, Y g:i A') ?? '',
                'url' => "/meetings/{$meeting->id}",
            ]);

        $tickets = Ticket::query()
            ->where(function ($query) use ($q, $like) {
                $query->where('subject', $like, "%{$q}%")
                    ->orWhere('description', $like, "%{$q}%");
            })
            ->limit(5)
            ->get()
            ->map(fn (Ticket $ticket) => [
                'id' => $ticket->id,
                'type' => 'ticket',
                'title' => $ticket->subject,
                'subtitle' => "{$ticket->status->value} / {$ticket->priority->value}",
                'url' => "/tickets/{$ticket->id}",
            ]);

        $tasks = Task::query()
            ->with('project')
            ->where(function ($query) use ($q, $like) {
                $query->where('title', $like, "%{$q}%");
            })
            ->limit(5)
            ->get()
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'type' => 'task',
                'title' => $task->title,
                'subtitle' => $task->project?->name ?? $task->status->value,
                'url' => $task->project_id ? "/projects/{$task->project_id}" : '/tasks',
            ]);

        return response()->json([
            'data' => [
                'leads' => $leads,
                'clients' => $clients,
                'projects' => $projects,
                'meetings' => $meetings,
                'tickets' => $tickets,
                'tasks' => $tasks,
            ],
        ]);
    }
}
