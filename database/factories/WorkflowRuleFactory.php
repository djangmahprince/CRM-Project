<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkflowRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkflowRule>
 */
class WorkflowRuleFactory extends Factory
{
    protected $model = WorkflowRule::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Qualify follow-up',
            'object_type' => 'lead',
            'field' => 'lead_status',
            'operator' => 'equals',
            'value' => 'Qualified',
            'action_type' => 'create_task',
            'action_config' => [
                'subject_template' => 'Follow up with {{name}}',
                'assign_to' => 'owner',
            ],
            'enabled' => true,
            'created_by' => User::factory(),
            'updated_by' => fn (array $attributes) => $attributes['created_by'],
        ];
    }

    public function disabled(): static
    {
        return $this->state(fn () => ['enabled' => false]);
    }
}
