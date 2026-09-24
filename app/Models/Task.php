<?php

namespace App\Models;

use App\Models\Concerns\HasCrmAudit;
use App\Models\Concerns\HasCrmVisibility;
use Database\Factories\TaskFactory;
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
