<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\InviteTeamMemberAction;
use App\Models\TeamInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class InvitationController extends Controller
{
    public function show(string $token): Response|RedirectResponse
    {
        $invitation = TeamInvitation::where('token', $token)->firstOrFail();

        if ($invitation->isAccepted()) {
            return redirect()->route('login')
                ->with('status', 'This invitation has already been accepted.');
        }

        if ($invitation->isExpired()) {
            return Inertia::render('Invite/Expired', [
                'team' => $invitation->team->only('name'),
            ]);
        }

        return Inertia::render('Invite/Show', [
            'token' => $token,
            'team' => $invitation->team->only('name'),
            'email' => $invitation->email,
        ]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = TeamInvitation::where('token', $token)->firstOrFail();

        if ($invitation->isAccepted()) {
            return redirect()->route('login')
                ->with('status', 'This invitation has already been accepted.');
        }

        if ($invitation->isExpired()) {
            return redirect()->route('login')
                ->with('status', 'This invitation has expired. Ask your team owner to send a new one.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $invitation->email,
            'password' => bcrypt($validated['password']),
            'team_id' => $invitation->team_id,
            'role' => 'member',
        ]);

        $invitation->update([
            'accepted_at' => now(),
        ]);

        auth()->login($user);

        return redirect()->route('dashboard');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $team = $user->team;

        if ($team === null) {
            return redirect()->route('team.create');
        }

        Gate::authorize('inviteMember', $team);

        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        try {
            app(InviteTeamMemberAction::class)->execute($team, $request->email);
        } catch (\RuntimeException $e) {
            return redirect()->route('team.edit')
                ->with('error', $e->getMessage());
        }

        return redirect()->route('team.edit')
            ->with('status', 'Invitation sent to ' . $request->email);
    }

    public function destroy(TeamInvitation $invitation): RedirectResponse
    {
        $user = auth()->user();
        $team = $user->team;

        if ($team === null) {
            return redirect()->route('team.create');
        }

        Gate::authorize('inviteMember', $team);

        if ($invitation->team_id !== $team->id) {
            abort(403);
        }

        $invitation->delete();

        return redirect()->route('team.edit')
            ->with('status', 'Invitation cancelled.');
    }
}
