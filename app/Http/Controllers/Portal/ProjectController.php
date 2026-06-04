<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        $client = Auth::guard('client')->user()->client;

        $projects = Project::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return Inertia::render('Portal/Projects/Index', [
            'projects' => $projects,
        ]);
    }

    public function show(Project $project): Response
    {
        $client = Auth::guard('client')->user()->client;

        abort_if($project->client_id !== $client->id, 403);

        return Inertia::render('Portal/Projects/Show', [
            'project' => $project,
        ]);
    }
}
