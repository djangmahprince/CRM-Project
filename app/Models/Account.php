<?php

namespace App\Models;

use App\Models\Concerns\HasCrmAudit;
use App\Models\Concerns\HasCrmVisibility;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasCrmAudit, HasCrmVisibility, HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'parent_account_id', 'phone', 'fax', 'website', 'type', 'industry', 'employees',
        'annual_revenue', 'billing_street', 'billing_city', 'billing_state', 'billing_postal_code',
        'billing_country', 'shipping_street', 'shipping_city', 'shipping_state', 'shipping_postal_code',
        'shipping_country', 'description', 'owner_id', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'annual_revenue' => 'decimal:2',
        ];
    }

    public function parentAccount(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_account_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_account_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
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

    public function hierarchyRollups(): array
    {
        $ids = $this->descendantIds();
        $ids[] = $this->id;

        return [
            'total_employees' => (int) self::query()->whereIn('id', $ids)->sum('employees'),
            'total_revenue' => (float) self::query()->whereIn('id', $ids)->sum('annual_revenue'),
            'account_count' => count($ids),
        ];
    }

    public function descendantIds(): array
    {
        $ids = [];
        $frontier = [$this->id];
        while ($frontier) {
            $children = self::query()->whereIn('parent_account_id', $frontier)->pluck('id')->all();
            $frontier = array_diff($children, $ids);
            $ids = array_merge($ids, $frontier);
        }

        return $ids;
    }
}
