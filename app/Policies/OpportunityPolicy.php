<?php

namespace App\Policies;

use App\Models\Opportunity;
use App\Models\User;

class OpportunityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('opportunities.view');
    }

    public function view(User $user, Opportunity $opportunity): bool
    {
        return $user->can('opportunities.view') && $opportunity->userCanAccess($user, 'read');
    }

    public function create(User $user): bool
    {
        return $user->can('opportunities.create');
    }

    public function update(User $user, Opportunity $opportunity): bool
    {
        return $user->can('opportunities.update') && $opportunity->userCanAccess($user, 'write');
    }

    public function delete(User $user, Opportunity $opportunity): bool
    {
        return $user->can('opportunities.delete') && $opportunity->userCanAccess($user, 'write');
    }
}
