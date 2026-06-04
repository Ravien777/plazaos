<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\IntakeForm;
use App\Models\User;

class IntakeFormPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, IntakeForm $intakeForm): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, IntakeForm $intakeForm): bool
    {
        return true;
    }

    public function delete(User $user, IntakeForm $intakeForm): bool
    {
        return $user->canDelete();
    }
}
