<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TicketReply;
use App\Models\User;

class TicketReplyPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TicketReply $reply): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TicketReply $reply): bool
    {
        return $user->canDelete();
    }

    public function delete(User $user, TicketReply $reply): bool
    {
        return $user->canDelete();
    }
}
