<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\SyncTrelloAction;
use App\Models\Team;
use Illuminate\Console\Command;

class SyncTrello extends Command
{
    protected $signature = 'trello:sync {--team-id= : Sync for a specific team}';
    protected $description = 'Sync Trello boards and cards into PlazaOS tasks';

    public function handle(SyncTrelloAction $syncAction): int
    {
        $teamId = $this->option('team-id');

        if ($teamId) {
            $team = Team::find($teamId);
            if ($team === null) {
                $this->error("Team {$teamId} not found.");
                return self::FAILURE;
            }
            $result = $syncAction->execute($team);
            $this->outputResult($result);
            return self::SUCCESS;
        }

        $teams = Team::all();

        if ($teams->isEmpty()) {
            $this->warn('No teams found to sync.');
            return self::SUCCESS;
        }

        foreach ($teams as $team) {
            $this->info("Syncing team: {$team->name} ({$team->id})");
            $result = $syncAction->execute($team);
            $this->outputResult($result);
        }

        return self::SUCCESS;
    }

    private function outputResult(array $result): void
    {
        if (isset($result['error'])) {
            $this->warn("  {$result['error']}");
            return;
        }
        $this->info("  Created: {$result['created']}, Updated: {$result['updated']}, Removed: {$result['deleted']}");
    }
}
