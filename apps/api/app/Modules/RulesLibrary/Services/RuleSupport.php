<?php

declare(strict_types=1);

namespace App\Modules\RulesLibrary\Services;

use InvalidArgumentException;

trait RuleSupport
{
    /**
     * @param array<string, mixed>|null $payload
     */
    private function checksum(?array $payload): string
    {
        return hash('sha256', json_encode($payload ?? [], JSON_THROW_ON_ERROR));
    }

    /**
     * @param array<string, mixed> $ruleVersion
     */
    private function assertRuleVersionPublishable(array $ruleVersion): void
    {
        if (($ruleVersion['trigger_event'] ?? null) === null || !is_array($ruleVersion['condition_definition'] ?? null) || !is_array($ruleVersion['action_definition'] ?? null)) {
            throw new InvalidArgumentException('Draft rule is missing execution-ready definitions.');
        }
    }

    /**
     * @param array<string, mixed> $facts
     * @param array<string, mixed> $condition
     */
    private function evaluateConditionGroup(array $facts, array $condition): bool
    {
        if (isset($condition['all']) && is_array($condition['all'])) {
            foreach ($condition['all'] as $child) {
                if (!is_array($child) || !$this->evaluateConditionGroup($facts, $child)) {
                    return false;
                }
            }

            return true;
        }

        if (isset($condition['any']) && is_array($condition['any'])) {
            foreach ($condition['any'] as $child) {
                if (is_array($child) && $this->evaluateConditionGroup($facts, $child)) {
                    return true;
                }
            }

            return false;
        }

        $fact = (string) ($condition['fact'] ?? '');
        $operator = (string) ($condition['operator'] ?? 'eq');
        $expected = $condition['value'] ?? null;
        $actual = $facts[$fact] ?? null;

        return match ($operator) {
            'eq' => $actual === $expected,
            'neq' => $actual !== $expected,
            'gte' => is_numeric($actual) && is_numeric($expected) && (float) $actual >= (float) $expected,
            'lte' => is_numeric($actual) && is_numeric($expected) && (float) $actual <= (float) $expected,
            'contains' => is_string($actual) && is_string($expected) && str_contains(mb_strtolower($actual), mb_strtolower($expected)),
            'exists' => $actual !== null,
            'not_empty' => !empty($actual),
            default => false,
        };
    }
}