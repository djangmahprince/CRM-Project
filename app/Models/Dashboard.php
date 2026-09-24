<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dashboard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'description', 'folder', 'auto_refresh_minutes', 'owner_id', 'created_by',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(DashboardWidget::class);
    }
}
