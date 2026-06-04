<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectService $projectService
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Project::class);

        $projects = $this->projectService->list(request()->only(['status', 'client_id']));

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filters' => request()->only(['status', 'client_id']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Project::class);

        return Inertia::render('Projects/Create');
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $this->authorize('create', Project::class);

        try {
            $this->projectService->create($request->validated());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create project. Please try again.');
        }

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    public function show(Project $project): Response
    {
        $this->authorize('view', $project);

        $project->load(['client', 'notes', 'activities', 'emails', 'documents', 'meetings', 'tickets']);

        return Inertia::render('Projects/Show', [
            'project' => $project,
        ]);
    }

    public function edit(Project $project): Response
    {
        $this->authorize('update', $project);

        $project->load('client');

        return Inertia::render('Projects/Edit', [
            'project' => $project,
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        try {
            $this->projectService->update($project, $request->validated());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update project. Please try again.');
        }

        return redirect()->route('projects.show', $project)->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        try {
            $this->projectService->delete($project);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete project. Please try again.');
        }

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }
}
