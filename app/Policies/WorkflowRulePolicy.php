<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkflowRule;

class WorkflowRulePolicy
{
    private function canManage(User $user): bool
    {
        return $user->hasAnyRole(['System Administrator', 'Sales Manager']);
    }

    public function viewAny(User $user): bool
    {
        return $this->canManage($user);
    }

    public function view(User $user, WorkflowRule $workflowRule): bool
    {
        return $this->canManage($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, WorkflowRule $workflowRule): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, WorkflowRule $workflowRule): bool
    {
        return $this->canManage($user);
    }
}
