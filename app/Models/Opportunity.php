<?php

namespace App\Models;

use App\Models\Concerns\HasCrmAudit;
use App\Models\Concerns\HasCrmVisibility;
use Database\Factories\OpportunityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opportunity extends Model
{
    /** @use HasFactory<OpportunityFactory> */
    use HasCrmAudit, HasCrmVisibility, HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'account_id', 'amount', 'close_date', 'stage', 'probability', 'type', 'lead_source',
        'next_step', 'description', 'is_closed', 'is_won', 'owner_id', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'close_date' => 'date',
            'is_closed' => 'boolean',
            'is_won' => 'boolean',
            'probability' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Opportunity $opportunity) {
            $stages = config('crm.opportunity_stages');
            if (isset($stages[$opportunity->stage])) {
                $opportunity->probability = $stages[$opportunity->stage];
            }
            $opportunity->is_closed = in_array($opportunity->stage, ['Closed Won', 'Closed Lost'], true);
            $opportunity->is_won = $opportunity->stage === 'Closed Won';
        });

        static::updating(function (Opportunity $opportunity) {
            if ($opportunity->isDirty('stage')) {
                OpportunityStageHistory::query()->create([
                    'opportunity_id' => $opportunity->id,
                    'from_stage' => $opportunity->getOriginal('stage'),
                    'to_stage' => $opportunity->stage,
                    'changed_by' => auth()->id(),
                ]);
            }
        });
    }

    public function getExpectedRevenueAttribute(): float
    {
        return round(((float) $this->amount) * ((int) $this->probability) / 100, 2);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function stageHistory(): HasMany
    {
        return $this->hasMany(OpportunityStageHistory::class);
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
