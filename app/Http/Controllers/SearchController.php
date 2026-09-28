<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Contact;
use App\Models\CrmCase;
use App\Models\Lead;
use App\Models\Opportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function index(Request $request): Response
    {
        $q = trim($request->string('q')->toString());
        $object = $request->string('object')->toString();
        $objects = ['leads', 'accounts', 'contacts', 'opportunities', 'cases'];

        $results = [];
        if (strlen($q) >= 2) {
            foreach ($objects as $key) {
                if ($object !== '' && $object !== $key) {
                    continue;
                }
                $results[$key] = $this->searchObject($request, $key, $q, 25);
            }
        }

        return Inertia::render('Search/Index', [
            'q' => $q,
            'object' => $object,
            'results' => $results,
            'objects' => $objects,
        ]);
    }

    public function suggest(Request $request): JsonResponse
    {
        $q = trim($request->string('q')->toString());
        if (strlen($q) < 2) {
            return response()->json(['suggestions' => []]);
        }

        $suggestions = [];
        foreach (['leads', 'accounts', 'contacts', 'opportunities', 'cases'] as $key) {
            foreach ($this->searchObject($request, $key, $q, 5) as $row) {
                $suggestions[] = $row;
            }
        }

        return response()->json(['suggestions' => $suggestions]);
    }

    /**
     * @return list<array{id: int, object: string, label: string, subtitle: string|null, url: string}>
     */
    private function searchObject(Request $request, string $object, string $q, int $limit): array
    {
        $user = $request->user();
        $term = '%'.$q.'%';

        return match ($object) {
            'leads' => Lead::query()->visibleTo($user)
                ->where(function ($inner) use ($term): void {
                    $inner->where('last_name', 'like', $term)
                        ->orWhere('first_name', 'like', $term)
                        ->orWhere('company', 'like', $term)
                        ->orWhere('email', 'like', $term);
                })
                ->limit($limit)
                ->get()
                ->map(fn (Lead $lead) => [
                    'id' => $lead->id,
                    'object' => 'leads',
                    'label' => trim($lead->first_name.' '.$lead->last_name),
                    'subtitle' => $lead->company,
                    'url' => route('leads.show', $lead),
                ])->all(),
            'accounts' => Account::query()->visibleTo($user)
                ->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)->orWhere('phone', 'like', $term);
                })
                ->limit($limit)
                ->get()
                ->map(fn (Account $account) => [
                    'id' => $account->id,
                    'object' => 'accounts',
                    'label' => $account->name,
                    'subtitle' => $account->type,
                    'url' => route('accounts.show', $account),
                ])->all(),
            'contacts' => Contact::query()->visibleTo($user)
                ->with('account:id,name')
                ->where(function ($inner) use ($term): void {
                    $inner->where('last_name', 'like', $term)
                        ->orWhere('first_name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                })
                ->limit($limit)
                ->get()
                ->map(fn (Contact $contact) => [
                    'id' => $contact->id,
                    'object' => 'contacts',
                    'label' => $contact->name,
                    'subtitle' => $contact->account?->name,
                    'url' => route('contacts.show', $contact),
                ])->all(),
            'opportunities' => Opportunity::query()->visibleTo($user)
                ->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)->orWhere('stage', 'like', $term);
                })
                ->limit($limit)
                ->get()
                ->map(fn (Opportunity $opportunity) => [
                    'id' => $opportunity->id,
                    'object' => 'opportunities',
                    'label' => $opportunity->name,
                    'subtitle' => $opportunity->stage,
                    'url' => route('opportunities.show', $opportunity),
                ])->all(),
            'cases' => CrmCase::query()->visibleTo($user)
                ->where(function ($inner) use ($term): void {
                    $inner->where('case_number', 'like', $term)
                        ->orWhere('subject', 'like', $term);
                })
                ->limit($limit)
                ->get()
                ->map(fn (CrmCase $case) => [
                    'id' => $case->id,
                    'object' => 'cases',
                    'label' => $case->case_number,
                    'subtitle' => $case->subject,
                    'url' => route('cases.show', $case),
                ])->all(),
            default => [],
        };
    }
}
