<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'salutation' => fake()->randomElement(config('crm.salutations')),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'company' => fake()->company(),
            'title' => fake()->jobTitle(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('###-###-####'),
            'mobile' => fake()->optional()->numerify('###-###-####'),
            'lead_status' => fake()->randomElement(array_diff(config('crm.lead_statuses'), ['Converted'])),
            'lead_source' => fake()->randomElement(config('crm.lead_sources')),
            'rating' => fake()->randomElement(config('crm.ratings')),
            'industry' => fake()->randomElement(config('crm.industries')),
            'annual_revenue' => fake()->optional()->randomFloat(2, 10000, 5000000),
            'number_of_employees' => fake()->optional()->numberBetween(1, 5000),
            'website' => fake()->optional()->url(),
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => 'United States',
            'description' => fake()->optional()->sentence(),
            'converted' => false,
            'owner_id' => User::factory(),
            'created_by' => fn (array $attributes) => $attributes['owner_id'],
            'updated_by' => fn (array $attributes) => $attributes['owner_id'],
        ];
    }

    public function converted(): static
    {
        return $this->state(fn () => [
            'converted' => true,
            'lead_status' => 'Converted',
        ]);
    }
}
