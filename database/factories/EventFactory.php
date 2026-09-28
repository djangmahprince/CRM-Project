<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+2 weeks');

        return [
            'subject' => fake()->sentence(3),
            'assigned_to_id' => User::factory(),
            'starts_at' => $start,
            'ends_at' => (clone $start)->modify('+1 hour'),
            'all_day' => false,
            'location' => fake()->optional()->city(),
            'show_as' => fake()->randomElement(config('crm.show_as')),
            'is_private' => false,
            'description' => fake()->optional()->paragraph(),
            'owner_id' => fn (array $attributes) => $attributes['assigned_to_id'],
            'created_by' => fn (array $attributes) => $attributes['assigned_to_id'],
            'updated_by' => fn (array $attributes) => $attributes['assigned_to_id'],
        ];
    }
}
