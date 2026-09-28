<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Contact;
use App\Models\CrmCase;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\SavedSearch;
use App\Support\AdvancedSearchQuery;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdvancedSearchController extends Controller
{
    public function __construct(private AdvancedSearchQuery $searchQuery) {}

    public function index(Request $request): Response
    {
        $object = $request->string('object')->toString() ?: 'leads';
        $logic = $request->string('logic')->toString() === 'or' ? 'or' : 'and';
        $conditions = $request->input('conditions', []);
        if (! is_array($conditions)) {
            $conditions = [];
        }

        $fields = $this->fieldsFor($object);
        $results = [];

        if ($conditions !== []) {
            $model = $this->modelFor($object);
            $query = $model::query()->visibleTo($request->user());
            $this->searchQuery->apply($query, (new $model)->getTable(), [
                'logic' => $logic,
                'conditions' => $conditions,
            ]);
            $results = $query->limit(100)->get()->map(fn ($row) => $this->mapRow($object, $row))->all();
        }

        return Inertia::render('Search/Advanced', [
            'object' => $object,
            'logic' => $logic,
            'conditions' => $conditions,
            'fields' => $fields,
            'results' => $results,
            'objects' => array_keys($this->fieldMap()),
            'operators' => ['contains', 'equals', 'not_equals', 'starts_with', 'ends_with', 'gt', 'lt'],
            'savedSearches' => SavedSearch::query()
                ->where('user_id', $request->user()->id)
                ->latest()
                ->limit(20)
                ->get(['id', 'name', 'object_type', 'criteria']),
        ]);
    }

    /**
     * @return array<string, list<string>>
     */
    private function fieldMap(): array
    {
        return [
            'leads' => ['last_name', 'first_name', 'company', 'email', 'lead_status', 'lead_source'],
            'accounts' => ['name', 'type', 'industry', 'phone', 'website'],
            'contacts' => ['last_name', 'first_name', 'email', 'title', 'phone'],
            'opportunities' => ['name', 'stage', 'amount', 'lead_source'],
            'cases' => ['case_number', 'subject', 'status', 'priority', 'origin'],
        ];
    }

    /**
     * @return list<string>
     */
    private function fieldsFor(string $object): array
    {
        return $this->fieldMap()[$object] ?? $this->fieldMap()['leads'];
    }

    private function modelFor(string $object): string
    {
        return match ($object) {
            'accounts' => Account::class,
            'contacts' => Contact::class,
            'opportunities' => Opportunity::class,
            'cases' => CrmCase::class,
            default => Lead::class,
        };
    }

    /**
     * @return array{id: int, label: string, subtitle: string|null, url: string}
     */
    private function mapRow(string $object, mixed $row): array
    {
        return match ($object) {
            'accounts' => [
                'id' => $row->id,
                'label' => $row->name,
                'subtitle' => $row->type,
                'url' => route('accounts.show', $row),
            ],
            'contacts' => [
                'id' => $row->id,
                'label' => trim(($row->first_name ?? '').' '.($row->last_name ?? '')),
                'subtitle' => $row->email,
                'url' => route('contacts.show', $row),
            ],
            'opportunities' => [
                'id' => $row->id,
                'label' => $row->name,
                'subtitle' => $row->stage,
                'url' => route('opportunities.show', $row),
            ],
            'cases' => [
                'id' => $row->id,
                'label' => $row->case_number,
                'subtitle' => $row->subject,
                'url' => route('cases.show', $row),
            ],
            default => [
                'id' => $row->id,
                'label' => trim(($row->first_name ?? '').' '.($row->last_name ?? '')),
                'subtitle' => $row->company,
                'url' => route('leads.show', $row),
            ],
        };
    }
}
