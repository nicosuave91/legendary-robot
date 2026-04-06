<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Services;

use InvalidArgumentException;

trait WorkflowSupport
{
    /**
     * @param array<string, mixed>|null $payload
     */
    private function checksum(?array $payload): string
    {
        return hash('sha256', json_encode($payload ?? [], JSON_THROW_ON_ERROR));
    }

    /**
     * @param array<string, mixed> $triggerDefinition
     */
    private function assertTriggerDefinition(array $triggerDefinition): void
    {
        if (($triggerDefinition['event'] ?? null) === null || ($triggerDefinition['subjectType'] ?? null) === null) {
            throw new InvalidArgumentException('Workflow trigger definition is incomplete.');
        }
    }

    /**
     * @param array<int, mixed> $stepsDefinition
     */
    private function assertStepsDefinition(array $stepsDefinition): void
    {
        foreach ($stepsDefinition as $step) {
            if (!is_array($step) || ($step['type'] ?? null) === null) {
                throw new InvalidArgumentException('Workflow steps must each define a type.');
            }
        }
    }

    /**
     * @param array<string, mixed> $facts
     * @param array<string, mixed> $condition
     */
    private function evaluateCondition(array $facts, array $condition): bool
    {
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
            default => false,
        };
    }
}