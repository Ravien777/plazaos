<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\CloneProjectAction;
use App\Actions\GenerateStatusPingAction;
use App\Mail\StatusPingMail;
use App\Models\Project;
use App\Models\User;
use App\Notifications\ProjectStatusChanged;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class ProjectService
{
    public function __construct(
        private readonly CloneProjectAction $cloneProjectAction,
        private readonly GenerateStatusPingAction $generateStatusPingAction,
    ) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Project::with('client')->where('is_template', false);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(25);
    }

    public function listTrashed(array $filters = []): LengthAwarePaginator
    {
        $query = Project::onlyTrashed()->with('client');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(25);
    }

    public function create(array $data): Project
    {
        if (!empty($data['template_id'])) {
            $source = Project::findOrFail($data['template_id']);
            return $this->cloneProjectAction->execute($source, $data['client_id'], $data['name']);
        }

        $project = Project::create($data);

        activity()->log($project, 'project.created', "Project {$project->name} was created.");

        return $project;
    }

    public function update(Project $project, array $data): Project
    {
        $oldStatus = $project->status;

        $project->update($data);

        activity()->log($project, 'project.updated', "Project {$project->name} was updated.");

        if (isset($data['status']) && $data['status'] !== $oldStatus?->value) {
            $users = User::where('team_id', $project->team_id)->get();
            Notification::send($users, new ProjectStatusChanged($project, $oldStatus?->value, $data['status']));
        }

        return $project;
    }

    public function delete(Project $project): void
    {
        activity()->log($project, 'project.deleted', "Project {$project->name} was deleted.");

        $project->delete();
    }

    public function bulkArchive(array $ids): void
    {
        $projects = Project::whereIn('id', $ids)->get();

        foreach ($projects as $project) {
            activity()->log($project, 'project.deleted', "Project {$project->name} was archived (bulk).");
        }

        Project::whereIn('id', $ids)->delete();
    }

    public function bulkForceDelete(array $ids): void
    {
        $projects = Project::whereIn('id', $ids)->get();

        foreach ($projects as $project) {
            activity()->log($project, 'project.deleted', "Project {$project->name} was permanently deleted (bulk).");
        }

        Project::whereIn('id', $ids)->forceDelete();
    }

    public function bulkUpdateStatus(array $ids, string $status): void
    {
        $projects = Project::whereIn('id', $ids)->get();

        Project::whereIn('id', $ids)->update(['status' => $status]);

        foreach ($projects as $project) {
            activity()->log($project, 'project.updated', "Project {$project->name} status changed to {$status} (bulk).");
        }
    }

    public function saveAsTemplate(Project $project, string $templateName): Project
    {
        $project->update([
            'is_template' => true,
            'template_name' => $templateName,
        ]);

        activity()->log($project, 'project.saved-as-template', "Project {$project->name} was saved as template '{$templateName}'.");

        return $project;
    }

    public function getTemplates(): \Illuminate\Support\Collection
    {
        return Project::where('is_template', true)
            ->withCount('tasks')
            ->orderBy('template_name')
            ->get(['id', 'template_name', 'description']);
    }

    public function previewTemplate(Project $project): array
    {
        $tasks = $project->tasks()->get(['title', 'description', 'priority']);

        return [
            'id' => $project->id,
            'template_name' => $project->template_name,
            'description' => $project->description,
            'tasks' => $tasks,
        ];
    }

    public function statusPingPreview(Project $project): array
    {
        return $this->generateStatusPingAction->execute($project);
    }

    public function sendStatusPing(Project $project, ?string $personalNote = null): void
    {
        $data = $this->generateStatusPingAction->execute($project, $personalNote);

        if ($project->client?->email) {
            Mail::to($project->client->email)->queue(new StatusPingMail($project, $data));
        }

        $clientName = $project->client?->contact_name ?? 'client';
        activity()->log($project, 'status-ping.sent', "Sent weekly status ping to {$clientName} for project {$project->name}.");
    }
}
