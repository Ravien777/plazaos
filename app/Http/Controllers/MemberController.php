<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    public function index(): Response|RedirectResponse
    {
        $user = auth()->user();
        $team = $user->team;

        if ($team === null) {
            return redirect()->route('team.create');
        }

        return Inertia::render('Team/Members', [
            'members' => $team->members()
                ->orderBy('created_at')
                ->get()
                ->map(fn ($member) => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'role' => $member->role,
                    'created_at' => $member->created_at->toDateString(),
                ]),
            'isOwner' => $user->isOwner(),
        ]);
    }

    public function destroy(User $member): RedirectResponse
    {
        $user = auth()->user();
        $team = $user->team;

        if ($team === null) {
            return redirect()->route('team.create');
        }

        Gate::authorize('removeMember', $team);

        if ($member->id === $user->id) {
            return redirect()->route('team.members')
                ->with('error', 'You cannot remove yourself.');
        }

        if ($member->team_id !== $team->id) {
            abort(403);
        }

        $member->update([
            'team_id' => null,
            'role' => 'member',
        ]);

        return redirect()->route('team.members')
            ->with('status', 'Member removed from the team.');
    }
}
