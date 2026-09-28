<?php

namespace App\Http\Controllers\Concerns;

use App\Models\RecentlyViewed;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ListsCrmRecords
{
    protected function perPage(Request $request): int
    {
        return min(
            max((int) $request->integer('per_page', config('crm.list_page_size')), 1),
            (int) config('crm.list_max_page_size'),
        );
    }

    /**
     * @param  array<string, string>  $sortable
     */
    protected function sortColumn(Request $request, array $sortable, string $default = 'created_at'): string
    {
        return $sortable[$request->string('sort')->toString()] ?? $default;
    }

    protected function sortDirection(Request $request): string
    {
        return $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
    }

    protected function applyRecentOrdering(Builder $query, User $user, string $morphKey, Request $request): bool
    {
        $recentIds = RecentlyViewed::query()
            ->where('user_id', $user->id)
            ->where('viewable_type', $morphKey)
            ->latest('viewed_at')
            ->limit(5)
            ->pluck('viewable_id');

        if ($request->boolean('recent') && $recentIds->isNotEmpty() && ! $request->filled('q')) {
            $query->whereIn('id', $recentIds);
            $cases = $recentIds->values()->map(
                fn ($id, $index) => 'WHEN '.(int) $id.' THEN '.$index
            )->implode(' ');
            $query->orderByRaw("CASE id {$cases} END");

            return true;
        }

        return false;
    }

    /**
     * @param  array<string, string>  $sortable
     * @return array{q: string, sort: string, direction: string, per_page: int, recent: bool}
     */
    protected function listFilters(Request $request, array $sortable, string $defaultSort = 'created_at'): array
    {
        return [
            'q' => $request->string('q')->toString(),
            'sort' => $this->sortColumn($request, $sortable, $defaultSort),
            'direction' => $this->sortDirection($request),
            'per_page' => $this->perPage($request),
            'recent' => $request->boolean('recent'),
        ];
    }
}
