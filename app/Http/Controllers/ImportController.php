<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportCsvRequest;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ImportController extends Controller
{
    public function create(Request $request): Response
    {
        $object = $request->string('object')->toString() ?: 'leads';

        return Inertia::render('Import/Create', [
            'object' => $object,
            'fields' => $this->fieldsFor($object),
        ]);
    }

    public function store(ImportCsvRequest $request): RedirectResponse
    {
        $object = $request->string('object')->toString();
        $this->authorizeObject($object);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        abort_unless($handle !== false, 422, 'Unable to read CSV.');

        $headers = fgetcsv($handle) ?: [];
        $mapping = $request->input('mapping', []);
        $created = 0;
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $payload = [];
            foreach ($mapping as $csvIndex => $field) {
                if (! $field) {
                    continue;
                }
                $payload[$field] = $row[(int) $csvIndex] ?? null;
            }

            try {
                $this->importRow($object, $payload, $request->user()->id);
                $created++;
            } catch (\Throwable $e) {
                $errors[] = ['row' => $rowNumber, 'message' => $e->getMessage()];
            }
        }

        fclose($handle);

        return redirect()
            ->route('import.create', ['object' => $object])
            ->with('success', "Imported {$created} rows.")
            ->with('import_errors', $errors);
    }

    /**
     * @return list<string>
     */
    private function fieldsFor(string $object): array
    {
        return match ($object) {
            'accounts' => ['name', 'phone', 'website', 'type', 'industry'],
            'contacts' => ['account_id', 'first_name', 'last_name', 'email', 'phone', 'title'],
            default => ['first_name', 'last_name', 'company', 'email', 'phone', 'lead_status', 'lead_source'],
        };
    }

    private function authorizeObject(string $object): void
    {
        match ($object) {
            'accounts' => Gate::authorize('create', Account::class),
            'contacts' => Gate::authorize('create', Contact::class),
            default => Gate::authorize('create', Lead::class),
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function importRow(string $object, array $payload, int $userId): void
    {
        $payload['owner_id'] = $userId;
        $payload['created_by'] = $userId;
        $payload['updated_by'] = $userId;

        match ($object) {
            'accounts' => Account::query()->create([
                'name' => $payload['name'] ?? throw new \InvalidArgumentException('name required'),
                'phone' => $payload['phone'] ?? null,
                'website' => $payload['website'] ?? null,
                'type' => $payload['type'] ?? null,
                'industry' => $payload['industry'] ?? null,
                'owner_id' => $userId,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]),
            'contacts' => Contact::query()->create([
                'account_id' => $payload['account_id'] ?? throw new \InvalidArgumentException('account_id required'),
                'first_name' => $payload['first_name'] ?? null,
                'last_name' => $payload['last_name'] ?? throw new \InvalidArgumentException('last_name required'),
                'email' => $payload['email'] ?? null,
                'phone' => $payload['phone'] ?? null,
                'title' => $payload['title'] ?? null,
                'owner_id' => $userId,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]),
            default => Lead::query()->create([
                'first_name' => $payload['first_name'] ?? null,
                'last_name' => $payload['last_name'] ?? throw new \InvalidArgumentException('last_name required'),
                'company' => $payload['company'] ?? throw new \InvalidArgumentException('company required'),
                'email' => $payload['email'] ?? null,
                'phone' => $payload['phone'] ?? null,
                'lead_status' => $payload['lead_status'] ?? 'New',
                'lead_source' => $payload['lead_source'] ?? null,
                'owner_id' => $userId,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]),
        };
    }
}
