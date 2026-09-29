<?php

namespace App\Support;

use App\Models\Account;
use App\Models\AssistantDismissal;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Support\Carbon;

class HomeAssistant
{
    /**
     * Rule-based Home insights (not ML).
     *
     * @return list<array{key: string, type: string, title: string, body: string, url: string}>
     */
    public function insightsFor(User $user, ?Carbon $now = null): array
    {
        $now ??= now();
        $dismissed = AssistantDismissal::query()
            ->where('user_id', $user->id)
            ->pluck('key')
            ->all();

        $insights = [
            ...$this->staleAccounts($user, $now),
            ...$this->staleClosingOpportunities($user, $now),
        ];

        return collect($insights)
            ->reject(fn (array $insight) => in_array($insight['key'], $dismissed, true))
            ->take(8)
            ->values()
            ->all();
    }

    /**
     * @return list<array{key: string, type: string, title: string, body: string, url: string}>
     */
    private function staleAccounts(User $user, Carbon $now): array
    {
        $cutoff = $now->copy()->subDays(30);

        return Account::query()
            ->visibleTo($user)
            ->where('updated_at', '<', $cutoff)
            ->orderBy('updated_at')
            ->limit(5)
            ->get(['id', 'name', 'updated_at'])
            ->map(fn (Account $account) => [
                'key' => 'account-stale-'.$account->id,
                'type' => 'stale_account',
                'title' => 'Quiet account: '.$account->name,
                'body' => 'No updates in 30+ days. Consider logging a call or task.',
                'url' => route('accounts.show', $account),
            ])
            ->all();
    }

    /**
     * @return list<array{key: string, type: string, title: string, body: string, url: string}>
     */
    private function staleClosingOpportunities(User $user, Carbon $now): array
    {
        $closeSoon = $now->copy()->addDays(7)->toDateString();
        $staleSince = $now->copy()->subDays(7);

        return Opportunity::query()
            ->visibleTo($user)
            ->where('is_closed', false)
            ->whereDate('close_date', '<=', $closeSoon)
            ->where('updated_at', '<', $staleSince)
            ->orderBy('close_date')
            ->limit(5)
            ->get(['id', 'name', 'close_date', 'stage', 'updated_at'])
            ->map(fn (Opportunity $opportunity) => [
                'key' => 'opportunity-stale-close-'.$opportunity->id,
                'type' => 'stale_opportunity',
                'title' => 'Closing soon with no updates: '.$opportunity->name,
                'body' => 'Close date '.$opportunity->close_date?->toDateString().' · stage '.$opportunity->stage.'.',
                'url' => route('opportunities.show', $opportunity),
            ])
            ->all();
    }
}
