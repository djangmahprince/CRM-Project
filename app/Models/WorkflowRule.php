<?php

namespace App\Models;

use Database\Factories\WorkflowRuleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowRule extends Model
{
    /** @use HasFactory<WorkflowRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'object_type',
        'field',
        'operator',
        'value',
        'action_type',
        'action_config',
        'enabled',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'action_config' => 'array',
            'enabled' => 'boolean',
        ];
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    public function scopeForObject(Builder $query, string $objectType): Builder
    {
        return $query->where('object_type', $objectType);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
