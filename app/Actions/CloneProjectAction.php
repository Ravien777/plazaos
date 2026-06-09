<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class CloneProjectAction
{
    public function execute(Project $source, string $clientId, string $name): Project
    {
        if (!$source->is_template) {
            throw new \InvalidArgumentException('Source project is not a template.');
        }

        return DB::transaction(function () use ($source, $clientId, $name) {
        $project = Project::create([
            'team_id' => $source->team_id,
            'client_id' => $clientId,
            'name' => $name,
            'description' => $source->description,
            'status' => 'discovery',
            'budget' => $source->budget,
            'start_date' => null,
            'due_date' => null,
            'progress_percentage' => 0,
            'is_template' => false,
            'template_name' => null,
        ]);

        foreach ($source->tasks()->get() as $task) {
            Task::create([
                'team_id' => $project->team_id,
                'project_id' => $project->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => 'todo',
                'priority' => $task->priority,
                'order' => $task->order,
            ]);
        }

        activity()->log($project, 'project.cloned', "Project {$project->name} was cloned from template.");

        return $project;
        });
    }
}
