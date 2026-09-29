<?php

namespace App\Http\Resources;

use App\Models\Opportunity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Opportunity */
class OpportunityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'account_id' => $this->account_id,
            'amount' => $this->amount,
            'close_date' => $this->close_date?->toDateString(),
            'stage' => $this->stage,
            'probability' => $this->probability,
            'expected_revenue' => $this->expected_revenue,
            'type' => $this->type,
            'lead_source' => $this->lead_source,
            'next_step' => $this->next_step,
            'description' => $this->description,
            'is_closed' => (bool) $this->is_closed,
            'is_won' => (bool) $this->is_won,
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
