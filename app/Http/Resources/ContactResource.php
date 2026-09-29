<?php

namespace App\Http\Resources;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Contact */
class ContactResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'salutation' => $this->salutation,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'title' => $this->title,
            'department' => $this->department,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'home_phone' => $this->home_phone,
            'other_phone' => $this->other_phone,
            'email' => $this->email,
            'fax' => $this->fax,
            'reports_to_id' => $this->reports_to_id,
            'assistant' => $this->assistant,
            'asst_phone' => $this->asst_phone,
            'mailing_street' => $this->mailing_street,
            'mailing_city' => $this->mailing_city,
            'mailing_state' => $this->mailing_state,
            'mailing_postal_code' => $this->mailing_postal_code,
            'mailing_country' => $this->mailing_country,
            'other_street' => $this->other_street,
            'other_city' => $this->other_city,
            'other_state' => $this->other_state,
            'other_postal_code' => $this->other_postal_code,
            'other_country' => $this->other_country,
            'lead_source' => $this->lead_source,
            'birthdate' => $this->birthdate?->toDateString(),
            'description' => $this->description,
            'account' => $this->whenLoaded('account', fn () => [
                'id' => $this->account?->id,
                'name' => $this->account?->name,
            ]),
            'owner' => $this->whenLoaded('owner', fn () => [
                'id' => $this->owner?->id,
                'name' => $this->owner?->name,
                'email' => $this->owner?->email,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
