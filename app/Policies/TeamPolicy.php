<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Team $team): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->team_id === null;
    }

    public function update(User $user, Team $team): bool
    {
        return $user->isOwner() && $user->team_id === $team->id;
    }

    public function delete(User $user, Team $team): bool
    {
        return $user->isOwner() && $user->team_id === $team->id;
    }

    public function inviteMember(User $user, Team $team): bool
    {
        return $user->isOwner() && $user->team_id === $team->id;
    }

    public function removeMember(User $user, Team $team): bool
    {
        return $user->isOwner() && $user->team_id === $team->id;
    }
}
