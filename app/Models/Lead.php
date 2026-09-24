<?php

namespace App\Models;

use App\Models\Concerns\HasCrmAudit;
use App\Models\Concerns\HasCrmVisibility;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasCrmAudit, HasCrmVisibility, HasFactory, SoftDeletes;

    protected $fillable = [
        'salutation', 'first_name', 'last_name', 'company', 'title', 'email', 'phone', 'mobile',
        'lead_status', 'lead_source', 'rating', 'industry', 'annual_revenue', 'number_of_employees',
        'website', 'street', 'city', 'state', 'postal_code', 'country', 'description', 'converted',
        'converted_account_id', 'converted_contact_id', 'converted_opportunity_id', 'owner_id',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'converted' => 'boolean',
            'annual_revenue' => 'decimal:2',
        ];
    }

    public function getNameAttribute(): string
    {
        return trim(($this->first_name ? $this->first_name.' ' : '').$this->last_name);
    }

    public function convertedAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'converted_account_id');
    }

    public function convertedContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'converted_contact_id');
    }

    public function convertedOpportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class, 'converted_opportunity_id');
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
