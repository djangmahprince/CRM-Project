<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'description', 'folder', 'report_type', 'definition', 'is_system', 'owner_id', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'definition' => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(ReportSubscription::class);
    }
}
