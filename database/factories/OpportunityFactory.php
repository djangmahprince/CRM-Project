<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Opportunity>
 */
class OpportunityFactory extends Factory
{
    protected $model = Opportunity::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stage = fake()->randomElement(array_keys(config('crm.opportunity_stages')));

        return [
            'name' => fake()->catchPhrase(),
            'account_id' => Account::factory(),
            'amount' => fake()->randomFloat(2, 1000, 250000),
            'close_date' => fake()->dateTimeBetween('+1 week', '+6 months')->format('Y-m-d'),
            'stage' => $stage,
            'probability' => config('crm.opportunity_stages')[$stage],
            'type' => fake()->randomElement(config('crm.opportunity_types')),
            'lead_source' => fake()->randomElement(config('crm.lead_sources')),
            'next_step' => fake()->optional()->sentence(3),
            'owner_id' => User::factory(),
            'created_by' => fn (array $attributes) => $attributes['owner_id'],
            'updated_by' => fn (array $attributes) => $attributes['owner_id'],
        ];
    }
}
