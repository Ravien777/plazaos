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

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
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
            Notification::send(User::first(), new ProjectStatusChanged($project, $oldStatus->value, $data['status']));
        }

        return $project;
    }

    public function delete(Project $project): void
    {
        activity()->log($project, 'project.deleted', "Project {$project->name} was deleted.");

        $project->delete();
    }
}
