<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SyncTrelloAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TrelloController extends Controller
{
    public function sync(Request $request, SyncTrelloAction $syncAction): RedirectResponse
    {
        $team = $request->user()->team;

        if ($team === null) {
            return redirect()->back()->with('error', 'You must be part of a team to sync Trello.');
        }

        $result = $syncAction->execute($team);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->back()->with(
            'success',
            "Trello synced! {$result['created']} created, {$result['updated']} updated, {$result['deleted']} removed."
        );
    }
}
