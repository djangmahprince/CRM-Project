<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('accounts.view');
    }

    public function view(User $user, Account $account): bool
    {
        return $user->can('accounts.view') && $account->userCanAccess($user, 'read');
    }

    public function create(User $user): bool
    {
        return $user->can('accounts.create');
    }

    public function update(User $user, Account $account): bool
    {
        return $user->can('accounts.update') && $account->userCanAccess($user, 'write');
    }

    public function delete(User $user, Account $account): bool
    {
        return $user->can('accounts.delete') && $account->userCanAccess($user, 'write');
    }
}
