<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use App\Notifications\ProjectStatusChanged;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Notification;

class ProjectService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Project::with('client');

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
        $project = Project::create($data);

        activity()->log($project, 'project.created', "Project {$project->name} was created.");

        return $project;
    }

    public function update(Project $project, array $data): Project
    {
        $oldStatus = $project->status;

        $project->update($data);

        activity()->log($project, 'project.updated', "Project {$project->name} was updated.");

        if (isset($data['status']) && $data['status'] !== $oldStatus->value) {
            Notification::send(User::all(), new ProjectStatusChanged($project, $oldStatus->value, $data['status']));
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
}
