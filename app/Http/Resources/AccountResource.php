<?php

namespace App\Http\Resources;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Account */
class AccountResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'parent_account_id' => $this->parent_account_id,
            'phone' => $this->phone,
            'fax' => $this->fax,
            'website' => $this->website,
            'type' => $this->type,
            'industry' => $this->industry,
            'employees' => $this->employees,
            'annual_revenue' => $this->annual_revenue,
            'billing_street' => $this->billing_street,
            'billing_city' => $this->billing_city,
            'billing_state' => $this->billing_state,
            'billing_postal_code' => $this->billing_postal_code,
            'billing_country' => $this->billing_country,
            'shipping_street' => $this->shipping_street,
            'shipping_city' => $this->shipping_city,
            'shipping_state' => $this->shipping_state,
            'shipping_postal_code' => $this->shipping_postal_code,
            'shipping_country' => $this->shipping_country,
            'description' => $this->description,
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
