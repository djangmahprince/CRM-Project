<?php

namespace App\Policies;

use App\Models\CrmCase;
use App\Models\User;

class CrmCasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('cases.view');
    }

    public function view(User $user, CrmCase $case): bool
    {
        return $user->can('cases.view') && $case->userCanAccess($user, 'read');
    }

    public function create(User $user): bool
    {
        return $user->can('cases.create');
    }

    public function update(User $user, CrmCase $case): bool
    {
        if ($case->is_closed) {
            return false;
        }

        return $user->can('cases.update') && $case->userCanAccess($user, 'write');
    }

    public function delete(User $user, CrmCase $case): bool
    {
        if ($case->is_closed) {
            return false;
        }

        return $user->can('cases.delete') && $case->userCanAccess($user, 'write');
    }

    public function reopen(User $user, CrmCase $case): bool
    {
        return $case->is_closed
            && $user->can('cases.update')
            && $case->userCanAccess($user, 'write');
    }

    public function close(User $user, CrmCase $case): bool
    {
        return ! $case->is_closed
            && $user->can('cases.update')
            && $case->userCanAccess($user, 'write');
    }
}
