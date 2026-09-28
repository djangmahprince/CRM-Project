<?php

namespace App\Models;

use App\Models\Concerns\HasCrmAudit;
use App\Models\Concerns\HasCrmVisibility;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasCrmAudit, HasCrmVisibility, HasFactory, SoftDeletes;

    protected $fillable = [
        'subject', 'assigned_to_id', 'related_type', 'related_id', 'contact_id', 'due_date', 'status',
        'priority', 'comments', 'reminder_set', 'reminder_at', 'reminder_sent_at', 'owner_id',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'reminder_set' => 'boolean',
            'reminder_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
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
                ->orWhere('assigned_to_id', $user->id)
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
        if ((int) $this->assigned_to_id === (int) $user->id) {
            return $mode === 'read' || $user->can('tasks.update');
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

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
