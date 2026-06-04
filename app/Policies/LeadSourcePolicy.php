<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LeadSource;
use App\Models\User;

class LeadSourcePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LeadSource $leadSource): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, LeadSource $leadSource): bool
    {
        return true;
    }

    public function delete(User $user, LeadSource $leadSource): bool
    {
        return true;
    }
}
