<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'phone' => fake()->numerify('###-###-####'),
            'website' => fake()->optional()->url(),
            'type' => fake()->randomElement(config('crm.account_types')),
            'industry' => fake()->randomElement(config('crm.industries')),
            'employees' => fake()->optional()->numberBetween(1, 5000),
            'annual_revenue' => fake()->optional()->randomFloat(2, 10000, 5000000),
            'billing_city' => fake()->city(),
            'billing_state' => fake()->state(),
            'billing_country' => 'United States',
            'owner_id' => User::factory(),
            'created_by' => fn (array $attributes) => $attributes['owner_id'],
            'updated_by' => fn (array $attributes) => $attributes['owner_id'],
        ];
    }
}
