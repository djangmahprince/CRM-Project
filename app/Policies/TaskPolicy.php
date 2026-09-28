<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tasks.view');
    }

    public function view(User $user, Task $task): bool
    {
        return $user->can('tasks.view') && $task->userCanAccess($user, 'read');
    }

    public function create(User $user): bool
    {
        return $user->can('tasks.create');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->can('tasks.update') && $task->userCanAccess($user, 'write');
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->can('tasks.delete') && $task->userCanAccess($user, 'write');
    }
}
