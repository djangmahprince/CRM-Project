<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class AccountHierarchyController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Account::class);

        $user = $request->user();
        $roots = Account::query()
            ->visibleTo($user)
            ->whereNull('parent_account_id')
            ->with(['children' => fn ($q) => $q->visibleTo($user)->orderBy('name')])
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'industry', 'employees', 'annual_revenue', 'parent_account_id']);

        $tree = $roots->map(fn (Account $account) => $this->mapNode($account, $user))->values();

        return Inertia::render('Accounts/Hierarchy', [
            'tree' => $tree,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapNode(Account $account, $user): array
    {
        $children = $account->children
            ->filter(fn (Account $child) => $user->can('view', $child))
            ->map(fn (Account $child) => $this->mapNode(
                $child->loadMissing(['children' => fn ($q) => $q->visibleTo($user)->orderBy('name')]),
                $user
            ))
            ->values()
            ->all();

        return [
            'id' => $account->id,
            'name' => $account->name,
            'type' => $account->type,
            'industry' => $account->industry,
            'employees' => $account->employees,
            'annual_revenue' => $account->annual_revenue,
            'rollups' => $account->hierarchyRollups(),
            'children' => $children,
        ];
    }
}
