<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Project\SaveTemplateRequest;
use App\Http\Requests\Project\SendStatusPingRequest;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
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

        $projects = $this->projectService->list(request()->only(['search', 'status', 'client_id']));

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filters' => request()->only(['search', 'status', 'client_id']),
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
            $project = $this->projectService->create($request->validated());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create project. Please try again.');
        }

        $request->user()->completeOnboardingStep('first_project');

        return redirect()->route('projects.show', $project)->with('success', 'Project created successfully.');
    }

    public function saveAsTemplate(SaveTemplateRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('saveAsTemplate', $project);

        try {
            $this->projectService->saveAsTemplate($project, $request->validated()['template_name']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to save template. Please try again.');
        }

        return redirect()->back()->with('success', 'Project saved as template successfully.');
    }

    public function templates(): JsonResponse
    {
        $this->authorize('viewAny', Project::class);

        return response()->json($this->projectService->getTemplates());
    }

    public function previewTemplate(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return response()->json($this->projectService->previewTemplate($project));
    }

    public function statusPingPreview(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return response()->json($this->projectService->statusPingPreview($project));
    }

    public function sendStatusPing(SendStatusPingRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        try {
            $this->projectService->sendStatusPing($project, $request->validated()['personal_note'] ?? null);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send status update. Please try again.');
        }

        return redirect()->back()->with('success', 'Status update sent successfully.');
    }

    public function show(Project $project): Response
    {
        $this->authorize('view', $project);

        $project->load(['client', 'notes', 'activities', 'emails', 'documents', 'meetings', 'tickets', 'tasks.assignee']);

        $assignees = User::where('team_id', auth()->user()->team_id)
            ->select('id', 'name')
            ->get();

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'assignees' => $assignees,
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

    public function trash(): Response
    {
        $this->authorize('viewTrash', Project::class);

        $projects = $this->projectService->listTrashed(request()->only(['status']));

        return Inertia::render('Projects/Trash', [
            'projects' => $projects,
            'filters' => request()->only(['status']),
        ]);
    }

    public function forceDestroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $this->projectService->bulkForceDelete([$project->getKey()]);

        return redirect()->route('projects.trash')->with('success', 'Project permanently deleted.');
    }

    public function restore(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->restore();

        return redirect()->back()->with('success', 'Project restored successfully.');
    }
}
