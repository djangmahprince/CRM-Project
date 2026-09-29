<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('viewAny', Account::class), 403);

        $accounts = Account::query()
            ->visibleTo($request->user())
            ->with('owner:id,name,email')
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 25), 100));

        return AccountResource::collection($accounts);
    }

    public function store(Request $request): AccountResource
    {
        abort_unless($request->user()->can('create', Account::class), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'phone' => ['nullable', 'string', 'max:40'],
            'fax' => ['nullable', 'string', 'max:40'],
            'website' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', Rule::in(config('crm.account_types'))],
            'industry' => ['nullable', 'string', Rule::in(config('crm.industries'))],
            'employees' => ['nullable', 'integer', 'min:0'],
            'annual_revenue' => ['nullable', 'numeric', 'min:0'],
            'billing_street' => ['nullable', 'string', 'max:255'],
            'billing_city' => ['nullable', 'string', 'max:80'],
            'billing_state' => ['nullable', 'string', 'max:80'],
            'billing_postal_code' => ['nullable', 'string', 'max:20'],
            'billing_country' => ['nullable', 'string', 'max:80'],
            'shipping_street' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['nullable', 'string', 'max:80'],
            'shipping_state' => ['nullable', 'string', 'max:80'],
            'shipping_postal_code' => ['nullable', 'string', 'max:20'],
            'shipping_country' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $account = Account::query()->create($data);

        return new AccountResource($account->load('owner:id,name,email'));
    }

    public function show(Request $request, Account $account): AccountResource
    {
        abort_unless($request->user()->can('view', $account), 403);

        return new AccountResource($account->load('owner:id,name,email'));
    }

    public function update(Request $request, Account $account): AccountResource
    {
        abort_unless($request->user()->can('update', $account), 403);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'parent_account_id' => ['nullable', 'integer', 'exists:accounts,id', Rule::notIn([(int) $account->id])],
            'phone' => ['nullable', 'string', 'max:40'],
            'fax' => ['nullable', 'string', 'max:40'],
            'website' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', Rule::in(config('crm.account_types'))],
            'industry' => ['nullable', 'string', Rule::in(config('crm.industries'))],
            'employees' => ['nullable', 'integer', 'min:0'],
            'annual_revenue' => ['nullable', 'numeric', 'min:0'],
            'billing_street' => ['nullable', 'string', 'max:255'],
            'billing_city' => ['nullable', 'string', 'max:80'],
            'billing_state' => ['nullable', 'string', 'max:80'],
            'billing_postal_code' => ['nullable', 'string', 'max:20'],
            'billing_country' => ['nullable', 'string', 'max:80'],
            'shipping_street' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['nullable', 'string', 'max:80'],
            'shipping_state' => ['nullable', 'string', 'max:80'],
            'shipping_postal_code' => ['nullable', 'string', 'max:20'],
            'shipping_country' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $account->update($data);

        return new AccountResource($account->fresh()->load('owner:id,name,email'));
    }

    public function destroy(Request $request, Account $account): Response
    {
        abort_unless($request->user()->can('delete', $account), 403);
        $account->delete();

        return response()->noContent();
    }
}
