<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'salutation' => fake()->randomElement(config('crm.salutations')),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'title' => fake()->jobTitle(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('###-###-####'),
            'lead_source' => fake()->randomElement(config('crm.lead_sources')),
            'mailing_city' => fake()->city(),
            'mailing_country' => 'United States',
            'owner_id' => User::factory(),
            'created_by' => fn (array $attributes) => $attributes['owner_id'],
            'updated_by' => fn (array $attributes) => $attributes['owner_id'],
        ];
    }
}
