<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('leads.view');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->can('leads.view') && $lead->userCanAccess($user, 'read');
    }

    public function create(User $user): bool
    {
        return $user->can('leads.create');
    }

    public function update(User $user, Lead $lead): bool
    {
        if ($lead->converted) {
            return false;
        }

        return $user->can('leads.update') && $lead->userCanAccess($user, 'write');
    }

    public function delete(User $user, Lead $lead): bool
    {
        if ($lead->converted) {
            return false;
        }

        return $user->can('leads.delete') && $lead->userCanAccess($user, 'write');
    }
}
