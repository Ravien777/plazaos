<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function create(): Response|RedirectResponse
    {
        if (auth()->user()->team_id !== null) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Onboard/CreateTeam');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user->team_id !== null) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $team = Team::create([
            'name' => $validated['name'],
            'owner_id' => $user->id,
        ]);

        $user->update([
            'team_id' => $team->id,
            'role' => 'owner',
        ]);

        $user->completeOnboardingStep('team');

        return redirect()->route('dashboard');
    }

    public function edit(): Response|RedirectResponse
    {
        $user = auth()->user();
        $team = $user->team;

        if ($team === null) {
            return redirect()->route('team.create');
        }

        Gate::authorize('update', $team);

        return Inertia::render('Team/Settings', [
            'team' => $team->only('id', 'name'),
            'invitations' => $team->invitations()
                ->whereNull('accepted_at')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn ($inv) => [
                    'id' => $inv->id,
                    'email' => $inv->email,
                    'expires_at' => $inv->expires_at->toDateTimeString(),
                    'expired' => $inv->isExpired(),
                ]),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $team = $user->team;

        if ($team === null) {
            return redirect()->route('team.create');
        }

        Gate::authorize('update', $team);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $team->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('team.edit');
    }
}
