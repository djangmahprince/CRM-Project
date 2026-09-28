<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject' => fake()->sentence(3),
            'assigned_to_id' => User::factory(),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+2 weeks')?->format('Y-m-d'),
            'status' => fake()->randomElement(config('crm.task_statuses')),
            'priority' => fake()->randomElement(config('crm.task_priorities')),
            'comments' => fake()->optional()->sentence(),
            'reminder_set' => false,
            'owner_id' => fn (array $attributes) => $attributes['assigned_to_id'],
            'created_by' => fn (array $attributes) => $attributes['assigned_to_id'],
            'updated_by' => fn (array $attributes) => $attributes['assigned_to_id'],
        ];
    }
}
