<?php

namespace App\Http\Resources;

use App\Models\CrmCase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CrmCase */
class CaseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'case_number' => $this->case_number,
            'contact_id' => $this->contact_id,
            'account_id' => $this->account_id,
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'type' => $this->type,
            'origin' => $this->origin,
            'reason' => $this->reason,
            'internal_comments' => $this->internal_comments,
            'web_email' => $this->web_email,
            'web_name' => $this->web_name,
            'web_company' => $this->web_company,
            'web_phone' => $this->web_phone,
            'is_closed' => (bool) $this->is_closed,
            'closed_at' => $this->closed_at,
            'account' => $this->whenLoaded('account', fn () => [
                'id' => $this->account?->id,
                'name' => $this->account?->name,
            ]),
            'contact' => $this->whenLoaded('contact', fn () => [
                'id' => $this->contact?->id,
                'first_name' => $this->contact?->first_name,
                'last_name' => $this->contact?->last_name,
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
