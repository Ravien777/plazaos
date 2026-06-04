<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class InviteTeamMemberAction
{
    public function execute(Team $team, string $email): TeamInvitation
    {
        $currentCount = $team->members()->count() + $team->invitations()->whereNull('accepted_at')->count();

        if ($currentCount >= 20) {
            throw new \RuntimeException('Team is full (max 20 members).');
        }

        if ($team->members()->where('email', $email)->exists()) {
            throw new \RuntimeException('This user is already a team member.');
        }

        if ($team->invitations()->where('email', $email)->whereNull('accepted_at')->exists()) {
            throw new \RuntimeException('An invitation has already been sent to this email.');
        }

        $existingUser = User::where('email', $email)->first();

        if ($existingUser && $existingUser->team_id !== null) {
            throw new \RuntimeException('This email already belongs to another team.');
        }

        $invitation = $team->invitations()->create([
            'email' => $email,
            'token' => Str::random(40),
            'expires_at' => now()->addHours(48),
        ]);

        Notification::route('mail', $email)
            ->notify(new TeamInvitationNotification($invitation));

        return $invitation;
    }
}
