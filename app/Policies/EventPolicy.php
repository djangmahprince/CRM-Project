<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('events.view');
    }

    public function view(User $user, Event $event): bool
    {
        if ($event->is_private && (int) $event->owner_id !== (int) $user->id && ! $user->can('records.view-all')) {
            return false;
        }

        return $user->can('events.view') && $event->userCanAccess($user, 'read');
    }

    public function create(User $user): bool
    {
        return $user->can('events.create');
    }

    public function update(User $user, Event $event): bool
    {
        return $user->can('events.update') && $event->userCanAccess($user, 'write');
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->can('events.delete') && $event->userCanAccess($user, 'write');
    }
}
