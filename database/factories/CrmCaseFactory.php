<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\CrmCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CrmCase>
 */
class CrmCaseFactory extends Factory
{
    protected $model = CrmCase::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'case_number' => 'pending',
            'account_id' => Account::factory(),
            'subject' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(array_diff(config('crm.case_statuses'), ['Closed'])),
            'priority' => fake()->randomElement(config('crm.priorities')),
            'type' => fake()->randomElement(config('crm.case_types')),
            'origin' => fake()->randomElement(config('crm.case_origins')),
            'reason' => fake()->randomElement(config('crm.case_reasons')),
            'owner_id' => User::factory(),
            'created_by' => fn (array $attributes) => $attributes['owner_id'],
            'updated_by' => fn (array $attributes) => $attributes['owner_id'],
        ];
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'status' => 'Closed',
            'is_closed' => true,
            'closed_at' => now(),
        ]);
    }
}
