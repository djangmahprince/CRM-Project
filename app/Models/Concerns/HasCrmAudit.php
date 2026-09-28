<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use App\Models\RecentlyViewed;
use App\Models\User;
use App\Notifications\OwnershipChangedNotification;
use App\Support\CrmRegistry;
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

            if ($model->isDirty('owner_id')) {
                $previousOwnerId = $model->getOriginal('owner_id');
                $newOwnerId = $model->owner_id;
                $type = CrmRegistry::keyFor($model::class) ?? class_basename($model);

                AuditLog::query()->create([
                    'user_id' => Auth::id(),
                    'action' => 'ownership_changed',
                    'auditable_type' => $type,
                    'auditable_id' => $model->getKey(),
                    'properties' => [
                        'from_owner_id' => $previousOwnerId,
                        'to_owner_id' => $newOwnerId,
                    ],
                    'ip_address' => request()?->ip(),
                ]);

                $name = trim(($model->first_name ?? '').' '.($model->last_name ?? ''));
                $label = $model->name
                    ?? ($name !== '' ? $name : null)
                    ?? $model->subject
                    ?? $model->case_number
                    ?? '#'.$model->getKey();

                $url = match ($type) {
                    'lead' => route('leads.show', $model->getKey()),
                    'account' => route('accounts.show', $model->getKey()),
                    'contact' => route('contacts.show', $model->getKey()),
                    'opportunity' => route('opportunities.show', $model->getKey()),
                    'case' => route('cases.show', $model->getKey()),
                    'task' => route('tasks.show', $model->getKey()),
                    'event' => route('events.show', $model->getKey()),
                    default => url('/'),
                };

                if ($previousOwnerId && (int) $previousOwnerId !== (int) $newOwnerId) {
                    User::query()->find($previousOwnerId)?->notify(
                        new OwnershipChangedNotification($type, (string) $label, $url, 'previous')
                    );
                }

                if ($newOwnerId && (int) $newOwnerId !== (int) Auth::id()) {
                    User::query()->find($newOwnerId)?->notify(
                        new OwnershipChangedNotification($type, (string) $label, $url, 'new')
                    );
                }
            }
        });
    }

    public function recordView(?User $user = null): void
    {
        $user ??= Auth::user();
        if (! $user) {
            return;
        }

        $type = CrmRegistry::keyFor(static::class) ?? static::class;

        RecentlyViewed::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'viewable_type' => $type,
                'viewable_id' => $this->getKey(),
            ],
            ['viewed_at' => now()],
        );
    }
}
