<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Notifications\TeamInvitation as TeamInvitationNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class InviteTeamMemberAction
{
    public function execute(Team $team, string $email): TeamInvitation
    {
        $existing = $team->members()->where('email', $email)->exists();

        if ($existing) {
            throw new \RuntimeException('This user is already a team member.');
        }

        $pending = $team->invitations()->where('email', $email)->whereNull('accepted_at')->exists();

        if ($pending) {
            throw new \RuntimeException('An invitation has already been sent to this email.');
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
