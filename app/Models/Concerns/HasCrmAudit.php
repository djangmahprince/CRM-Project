<?php

namespace App\Models\Concerns;

use App\Models\RecentlyViewed;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait HasCrmAudit
{
    public static function bootHasCrmAudit(): void
    {
        static::creating(function ($model) {
            $userId = Auth::id();
            if ($userId) {
                if (empty($model->owner_id)) {
                    $model->owner_id = $userId;
                }
                $model->created_by ??= $userId;
                $model->updated_by = $userId;
            }
        });

        static::updating(function ($model) {
            if (Auth::id()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    public function recordView(?User $user = null): void
    {
        $user ??= Auth::user();
        if (! $user) {
            return;
        }

        RecentlyViewed::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'viewable_type' => static::class,
                'viewable_id' => $this->getKey(),
            ],
            ['viewed_at' => now()],
        );
    }
}
