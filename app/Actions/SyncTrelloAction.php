<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Services\TrelloService;

class SyncTrelloAction
{
    public function __construct(
        private readonly TrelloService $trello,
    ) {}

    public function execute(Team $team): array
    {
        if (!$this->trello->configured()) {
            return ['created' => 0, 'updated' => 0, 'deleted' => 0, 'error' => 'Trello not configured'];
        }

        $boards = $this->trello->getBoards();
        $syncedCardIds = [];
        $created = 0;
        $updated = 0;

        foreach ($boards as $boardKey => $board) {
            $project = $this->findOrCreateProject($board, $team);
            $cards = $this->trello->getCards($board['id']);

            foreach ($cards as $card) {
                if ($card['closed']) {
                    continue;
                }

                $syncedCardIds[] = $card['id'];

                $existingTask = Task::where('trello_card_id', $card['id'])->first();

                if ($existingTask) {
                    $existingTask->update([
                        'title' => $card['name'],
                        'description' => ($card['desc'] ?? '') ?: null,
                        'due_date' => $card['due'] ?? null,
                        'trello_url' => $card['url'] ?? null,
                        'project_id' => $project->id,
                    ]);
                    $updated++;
                } else {
                    Task::create([
                        'trello_card_id' => $card['id'],
                        'trello_url' => $card['url'] ?? null,
                        'title' => $card['name'],
                        'description' => ($card['desc'] ?? '') ?: null,
                        'due_date' => $card['due'] ?? null,
                        'status' => 'todo',
                        'priority' => 'medium',
                        'order' => 0,
                        'project_id' => $project->id,
                        'created_by' => $team->owner_id,
                    ]);
                    $created++;
                }
            }
        }

        $deleted = 0;
        if (!empty($syncedCardIds)) {
            $staleTasks = Task::whereNotNull('trello_card_id')
                ->whereNotIn('trello_card_id', $syncedCardIds)
                ->get();

            foreach ($staleTasks as $task) {
                $task->delete();
                $deleted++;
            }
        }

        return compact('created', 'updated', 'deleted');
    }

    private function findOrCreateProject(array $board, Team $team): Project
    {
        $project = Project::where('name', $board['name'])
            ->where('team_id', $team->id)
            ->first();

        if ($project === null) {
            $project = Project::create([
                'name' => $board['name'],
                'status' => ProjectStatus::Discovery->value,
                'team_id' => $team->id,
                'user_id' => $team->owner_id,
                'progress_percentage' => 0,
            ]);
        }

        return $project;
    }
}
