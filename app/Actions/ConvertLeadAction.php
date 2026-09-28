<?php

namespace App\Actions;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ConvertLeadAction
{
    /**
     * @param  array{
     *     account_mode: string,
     *     account_id?: int|null,
     *     account_name?: string|null,
     *     contact_mode: string,
     *     contact_id?: int|null,
     *     create_opportunity?: bool,
     *     opportunity_name?: string|null,
     *     opportunity_amount?: float|null,
     *     opportunity_close_date?: string|null,
     *     opportunity_stage?: string|null
     * }  $data
     */
    public function handle(Lead $lead, User $actor, array $data): Lead
    {
        if ($lead->converted) {
            throw new InvalidArgumentException('Lead is already converted.');
        }

        return DB::transaction(function () use ($lead, $actor, $data) {
            $account = $this->resolveAccount($lead, $actor, $data);
            $contact = $this->resolveContact($lead, $actor, $account, $data);

            $opportunity = null;
            if (! empty($data['create_opportunity'])) {
                $opportunity = Opportunity::query()->create([
                    'name' => $data['opportunity_name'] ?: ($lead->company.' - Opportunity'),
                    'account_id' => $account->id,
                    'amount' => $data['opportunity_amount'] ?? null,
                    'close_date' => $data['opportunity_close_date'] ?? now()->addMonth()->toDateString(),
                    'stage' => $data['opportunity_stage'] ?? 'Qualification',
                    'lead_source' => $lead->lead_source,
                    'owner_id' => $lead->owner_id,
                    'created_by' => $actor->id,
                    'updated_by' => $actor->id,
                ]);
            }

            $this->transferActivities($lead, $account, $contact, $opportunity);

            $lead->forceFill([
                'converted' => true,
                'lead_status' => 'Converted',
                'converted_account_id' => $account->id,
                'converted_contact_id' => $contact->id,
                'converted_opportunity_id' => $opportunity?->id,
                'updated_by' => $actor->id,
            ])->save();

            return $lead->fresh(['convertedAccount', 'convertedContact', 'convertedOpportunity']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveAccount(Lead $lead, User $actor, array $data): Account
    {
        if (($data['account_mode'] ?? 'create') === 'existing') {
            return Account::query()->visibleTo($actor)->findOrFail($data['account_id']);
        }

        return Account::query()->create([
            'name' => $data['account_name'] ?: $lead->company,
            'phone' => $lead->phone,
            'website' => $lead->website,
            'industry' => $lead->industry,
            'annual_revenue' => $lead->annual_revenue,
            'employees' => $lead->number_of_employees,
            'billing_street' => $lead->street,
            'billing_city' => $lead->city,
            'billing_state' => $lead->state,
            'billing_postal_code' => $lead->postal_code,
            'billing_country' => $lead->country,
            'description' => $lead->description,
            'owner_id' => $lead->owner_id,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveContact(Lead $lead, User $actor, Account $account, array $data): Contact
    {
        if (($data['contact_mode'] ?? 'create') === 'existing') {
            return Contact::query()->visibleTo($actor)->findOrFail($data['contact_id']);
        }

        return Contact::query()->create([
            'account_id' => $account->id,
            'salutation' => $lead->salutation,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'title' => $lead->title,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'mobile' => $lead->mobile,
            'lead_source' => $lead->lead_source,
            'mailing_street' => $lead->street,
            'mailing_city' => $lead->city,
            'mailing_state' => $lead->state,
            'mailing_postal_code' => $lead->postal_code,
            'mailing_country' => $lead->country,
            'description' => $lead->description,
            'owner_id' => $lead->owner_id,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);
    }

    private function transferActivities(Lead $lead, Account $account, Contact $contact, ?Opportunity $opportunity): void
    {
        $target = $opportunity ?? $account;

        Task::query()
            ->where('related_type', 'lead')
            ->where('related_id', $lead->id)
            ->where('status', '!=', 'Completed')
            ->update([
                'related_type' => $opportunity ? 'opportunity' : 'account',
                'related_id' => $target->id,
                'contact_id' => $contact->id,
            ]);

        Event::query()
            ->where('related_type', 'lead')
            ->where('related_id', $lead->id)
            ->where('ends_at', '>=', now())
            ->update([
                'related_type' => $opportunity ? 'opportunity' : 'account',
                'related_id' => $target->id,
                'contact_id' => $contact->id,
            ]);
    }
}
