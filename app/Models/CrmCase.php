<?php

namespace App\Models;

use App\Models\Concerns\HasCrmAudit;
use App\Models\Concerns\HasCrmVisibility;
use Database\Factories\CrmCaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmCase extends Model
{
    /** @use HasFactory<CrmCaseFactory> */
    use HasCrmAudit, HasCrmVisibility, HasFactory, SoftDeletes;

    protected $table = 'cases';

    protected $fillable = [
        'case_number', 'contact_id', 'account_id', 'subject', 'description', 'status', 'priority',
        'type', 'origin', 'reason', 'internal_comments', 'web_email', 'web_name', 'web_company',
        'web_phone', 'is_closed', 'closed_at', 'owner_id', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_closed' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CrmCase $case) {
            if (! $case->case_number) {
                $case->case_number = 'pending';
            }
        });

        static::created(function (CrmCase $case) {
            $case->case_number = config('crm.case_number_prefix').str_pad((string) $case->id, 6, '0', STR_PAD_LEFT);
            $case->saveQuietly();
        });

        static::saving(function (CrmCase $case) {
            $case->is_closed = $case->status === 'Closed';
            if ($case->is_closed && ! $case->closed_at) {
                $case->closed_at = now();
            }
            if (! $case->is_closed) {
                $case->closed_at = null;
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'related');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
