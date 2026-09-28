<?php

namespace App\Support;

use App\Models\PasswordHistory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordHistoryService
{
    /**
     * Persist the user's current password hash before it is replaced.
     */
    public function rememberCurrent(User $user): void
    {
        PasswordHistory::query()->create([
            'user_id' => $user->id,
            'password' => $user->password,
        ]);

        $keep = (int) config('crm.password_history_count', 5);

        $ids = PasswordHistory::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->pluck('id');

        $outdated = $ids->slice($keep);

        if ($outdated->isNotEmpty()) {
            PasswordHistory::query()->whereIn('id', $outdated->all())->delete();
        }
    }

    public function wasUsedRecently(User $user, string $plainPassword): bool
    {
        if (Hash::check($plainPassword, $user->password)) {
            return true;
        }

        $histories = PasswordHistory::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit((int) config('crm.password_history_count', 5))
            ->get();

        foreach ($histories as $history) {
            if (Hash::check($plainPassword, $history->password)) {
                return true;
            }
        }

        return false;
    }
}
