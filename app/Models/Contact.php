<?php

namespace App\Models;

use App\Models\Concerns\HasCrmAudit;
use App\Models\Concerns\HasCrmVisibility;
use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use HasCrmAudit, HasCrmVisibility, HasFactory, SoftDeletes;

    protected $fillable = [
        'account_id', 'salutation', 'first_name', 'middle_name', 'last_name', 'title', 'department',
        'phone', 'mobile', 'home_phone', 'other_phone', 'email', 'fax', 'reports_to_id', 'assistant',
        'asst_phone', 'mailing_street', 'mailing_city', 'mailing_state', 'mailing_postal_code',
        'mailing_country', 'other_street', 'other_city', 'other_state', 'other_postal_code',
        'other_country', 'lead_source', 'birthdate', 'description', 'owner_id', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
        ];
    }

    public function getNameAttribute(): string
    {
        return trim(implode(' ', array_filter([$this->first_name, $this->middle_name, $this->last_name])));
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function reportsTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reports_to_id');
    }

    public function cases(): HasMany
    {
        return $this->hasMany(CrmCase::class);
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'related');
    }

    public function events(): MorphMany
    {
        return $this->morphMany(Event::class, 'related');
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
