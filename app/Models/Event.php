<?php

namespace App\Models;

use App\Models\Concerns\HasCrmAudit;
use App\Models\Concerns\HasCrmVisibility;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasCrmAudit, HasCrmVisibility, HasFactory, SoftDeletes;

    protected $fillable = [
        'subject', 'assigned_to_id', 'related_type', 'related_id', 'contact_id', 'starts_at', 'ends_at',
        'all_day', 'location', 'show_as', 'is_private', 'recurrence_rule', 'description', 'owner_id',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'all_day' => 'boolean',
            'is_private' => 'boolean',
        ];
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $query = $this->visibilityBase($query, $user);

        return $query->where(function (Builder $inner) use ($user) {
            $inner->where('is_private', false)->orWhere('owner_id', $user->id);
        });
    }

    protected function visibilityBase(Builder $query, User $user): Builder
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
