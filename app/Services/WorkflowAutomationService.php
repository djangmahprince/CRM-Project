<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Task;
use App\Models\WorkflowRule;
use App\Support\CrmRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class WorkflowAutomationService
{
    private static bool $running = false;

    public function handleUpdated(Model $model): void
    {
        if (self::$running) {
            return;
        }

        $objectType = CrmRegistry::keyFor($model::class);
        if (! in_array($objectType, ['lead', 'opportunity'], true)) {
            return;
        }

        $rules = WorkflowRule::query()
            ->enabled()
            ->forObject($objectType)
            ->where('action_type', 'create_task')
            ->get();

        if ($rules->isEmpty()) {
            return;
        }

        self::$running = true;

        try {
            foreach ($rules as $rule) {
                if (! $model->wasChanged($rule->field)) {
                    continue;
                }

                if (! $this->conditionMatches($model, $rule)) {
                    continue;
                }

                $this->createTask($model, $rule);
            }
        } finally {
            self::$running = false;
        }
    }

    public function conditionMatches(Model $model, WorkflowRule $rule): bool
    {
        $actual = data_get($model, $rule->field);
        $expected = $rule->value;

        return match ($rule->operator) {
            'equals' => (string) $actual === (string) $expected,
            'not_equals' => (string) $actual !== (string) $expected,
            default => false,
        };
    }

    private function createTask(Model $model, WorkflowRule $rule): void
    {
        $config = $rule->action_config ?? [];
        $assignTo = $config['assign_to'] ?? 'owner';
        $assigneeId = $assignTo === 'owner'
            ? (int) $model->getAttribute('owner_id')
            : (int) ($config['assigned_to_id'] ?? $model->getAttribute('owner_id'));

        if ($assigneeId < 1) {
            Log::warning('Workflow rule skipped: missing assignee', ['rule_id' => $rule->id]);

            return;
        }

        $subject = $this->renderSubject(
            (string) ($config['subject_template'] ?? 'Workflow follow-up'),
            $model,
        );

        Task::query()->create([
            'subject' => $subject,
            'assigned_to_id' => $assigneeId,
            'related_type' => CrmRegistry::keyFor($model::class),
            'related_id' => $model->getKey(),
            'status' => 'Not Started',
            'priority' => 'Normal',
            'owner_id' => $assigneeId,
            'created_by' => $rule->created_by,
            'updated_by' => $rule->created_by,
            'comments' => 'Created by workflow rule: '.$rule->name,
        ]);
    }

    private function renderSubject(string $template, Model $model): string
    {
        $replacements = [
            '{{name}}' => (string) ($model->getAttribute('name') ?? ''),
            '{{company}}' => (string) ($model->getAttribute('company') ?? ''),
            '{{stage}}' => (string) ($model->getAttribute('stage') ?? ''),
            '{{lead_status}}' => (string) ($model->getAttribute('lead_status') ?? ''),
        ];

        if ($model instanceof Lead) {
            $replacements['{{name}}'] = $model->name !== '' ? $model->name : (string) $model->last_name;
        }

        if ($model instanceof Opportunity && $replacements['{{name}}'] === '') {
            $replacements['{{name}}'] = (string) $model->name;
        }

        $subject = strtr($template, $replacements);

        return mb_substr(trim($subject) !== '' ? trim($subject) : 'Workflow follow-up', 0, 255);
    }
}
