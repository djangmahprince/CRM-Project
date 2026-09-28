<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class AdvancedSearchQuery
{
    /**
     * @param  array{logic?: string, conditions?: list<array{field?: string, operator?: string, value?: mixed}>}  $criteria
     */
    public function apply(Builder $query, string $table, array $criteria): Builder
    {
        $logic = strtolower((string) ($criteria['logic'] ?? 'and')) === 'or' ? 'or' : 'and';
        $conditions = $criteria['conditions'] ?? [];

        if ($conditions === []) {
            return $query;
        }

        $query->where(function (Builder $builder) use ($conditions, $logic, $table): void {
            foreach ($conditions as $condition) {
                $field = (string) ($condition['field'] ?? '');
                $operator = (string) ($condition['operator'] ?? 'contains');
                $value = $condition['value'] ?? null;

                if ($field === '' || $value === null || $value === '') {
                    continue;
                }

                if (! Schema::hasColumn($table, $field)) {
                    throw new InvalidArgumentException("Unknown field [{$field}] for {$table}.");
                }

                $method = $logic === 'or' ? 'orWhere' : 'where';

                match ($operator) {
                    'equals' => $builder->{$method}($field, '=', $value),
                    'not_equals' => $builder->{$method}($field, '!=', $value),
                    'starts_with' => $builder->{$method}($field, 'like', $value.'%'),
                    'ends_with' => $builder->{$method}($field, 'like', '%'.$value),
                    'gt' => $builder->{$method}($field, '>', $value),
                    'lt' => $builder->{$method}($field, '<', $value),
                    default => $builder->{$method}($field, 'like', '%'.$value.'%'),
                };
            }
        });

        return $query;
    }
}
