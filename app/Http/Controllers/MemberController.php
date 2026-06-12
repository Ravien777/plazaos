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

    public function destroy(User $user): RedirectResponse
    {
        $authUser = auth()->user();
        $team = $authUser->team;

        if ($team === null) {
            return redirect()->route('team.create');
        }

        Gate::authorize('removeMember', $team);

        if ($user->id === $authUser->id) {
            return redirect()->route('team.members')
                ->with('error', 'You cannot remove yourself.');
        }

        if ($user->team_id !== $team->id) {
            abort(403);
        }

        $user->update([
            'team_id' => null,
            'role' => 'member',
        ]);

        return redirect()->route('team.members')
            ->with('status', 'Member removed from the team.');
    }

    public function leave(): RedirectResponse
    {
        $user = auth()->user();
        $team = $user->team;

        if ($team === null) {
            return redirect()->route('team.create');
        }

        if ($user->isOwner()) {
            return redirect()->route('team.members')
                ->with('error', 'Owners cannot leave the team. Transfer ownership or delete the workspace instead.');
        }

        $user->update([
            'team_id' => null,
            'role' => 'member',
        ]);

        return redirect()->route('team.create')
            ->with('status', 'You have left the team.');
    }
}
