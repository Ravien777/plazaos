<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Email;
use App\Models\User;

class EmailPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Email $email): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Email $email): bool
    {
        return true;
    }

    public function delete(User $user, Email $email): bool
    {
        return $user->canDelete();
    }
}
