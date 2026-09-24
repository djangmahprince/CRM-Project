<?php

namespace App\Models\Concerns;

use App\Models\OrgSetting;
use App\Models\RecordShare;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait HasCrmVisibility
{
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function shares()
    {
        return $this->morphMany(RecordShare::class, 'shareable');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->can('records.view-all')) {
            return $query;
        }

        $sharing = OrgSetting::valueOf('default_sharing', config('crm.default_sharing'));

        if (in_array($sharing, ['public_read', 'public_read_write'], true)) {
            return $query;
        }

        return $query->where(function (Builder $inner) use ($user) {
            $inner->where('owner_id', $user->id)
                ->orWhereHas('shares', fn (Builder $shares) => $shares->where('user_id', $user->id));
        });
    }

    public function userCanAccess(User $user, string $mode = 'read'): bool
    {
        if ($user->can('records.view-all') && $mode === 'read') {
            return true;
        }
        if ($user->can('records.manage-all')) {
            return true;
        }
        if ((int) $this->owner_id === (int) $user->id) {
            return true;
        }

        $sharing = OrgSetting::valueOf('default_sharing', config('crm.default_sharing'));
        if ($sharing === 'public_read' && $mode === 'read') {
            return true;
        }
        if ($sharing === 'public_read_write') {
            return true;
        }

        $share = $this->shares()->where('user_id', $user->id)->first();
        if (! $share) {
            return false;
        }

        return $mode === 'read' || $share->access === 'write';
    }
}
