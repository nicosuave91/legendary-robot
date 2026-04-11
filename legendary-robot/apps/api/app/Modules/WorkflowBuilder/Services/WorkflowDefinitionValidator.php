<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Services;

final class WorkflowDefinitionValidator
{
    /**
     * @param array<string, mixed> $triggerDefinition
     * @param array<int, mixed> $stepsDefinition
     * @return array{isValid: bool, errors: list<array{code:string,path:string,message:string}>}
     */
    public function validate(array $triggerDefinition, array $stepsDefinition): array
    {
        $errors = [];

        $event = trim((string) ($triggerDefinition['event'] ?? ''));
        if ($event === '') {
            $errors[] = [
                'code' => 'missing_trigger_event',
                'path' => 'triggerDefinition.event',
                'message' => 'Workflow trigger definitions must include an event name.',
            ];
        }

        $subjectType = trim((string) ($triggerDefinition['subjectType'] ?? ''));
        if ($subjectType === '') {
            $errors[] = [
                'code' => 'missing_trigger_subject_type',
                'path' => 'triggerDefinition.subjectType',
                'message' => 'Workflow trigger definitions must include a subjectType.',
            ];
        }

        $filters = $triggerDefinition['filters'] ?? [];
        if ($filters !== [] && !is_array($filters)) {
            $errors[] = [
                'code' => 'invalid_trigger_filters_shape',
                'path' => 'triggerDefinition.filters',
                'message' => 'Workflow trigger filters must be an array when provided.',
            ];
        }

        if (is_array($filters)) {
            foreach ($filters as $index => $filter) {
                $filterPath = sprintf('triggerDefinition.filters.%d', $index);

                if (!is_array($filter)) {
                    $errors[] = [
                        'code' => 'invalid_trigger_filter_shape',
                        'path' => $filterPath,
                        'message' => 'Each trigger filter must be an object.',
                    ];

                    continue;
                }

                if (trim((string) ($filter['fact'] ?? '')) === '') {
                    $errors[] = [
                        'code' => 'missing_trigger_filter_fact',
                        'path' => $filterPath . '.fact',
                        'message' => 'Trigger filters must define a fact.',
                    ];
                }

                $operator = (string) ($filter['operator'] ?? '');
                if (!in_array($operator, ['eq', 'neq', 'gte', 'lte', 'contains', 'exists'], true)) {
                    $errors[] = [
                        'code' => 'invalid_trigger_filter_operator',
                        'path' => $filterPath . '.operator',
                        'message' => 'Trigger filters must use a supported operator.',
                    ];
                }

                if ($operator !== 'exists' && trim((string) ($filter['value'] ?? '')) === '') {
                    $errors[] = [
                        'code' => 'missing_trigger_filter_value',
                        'path' => $filterPath . '.value',
                        'message' => 'Trigger filters must define a value unless the operator is exists.',
                    ];
                }
            }
        }

        if ($stepsDefinition === []) {
            $errors[] = [
                'code' => 'missing_steps',
                'path' => 'stepsDefinition',
                'message' => 'Workflow drafts must include at least one executable step.',
            ];
        }

        foreach ($stepsDefinition as $index => $step) {
            $stepPath = sprintf('stepsDefinition.%d', $index);

            if (!is_array($step)) {
                $errors[] = [
                    'code' => 'invalid_step_shape',
                    'path' => $stepPath,
                    'message' => 'Workflow steps must be objects.',
                ];

                continue;
            }

            $type = trim((string) ($step['type'] ?? ''));
            $definition = is_array($step['definition'] ?? null) ? $step['definition'] : [];

            if ($type === '') {
                $errors[] = [
                    'code' => 'missing_step_type',
                    'path' => $stepPath . '.type',
                    'message' => 'Each workflow step must define a type.',
                ];

                continue;
            }

            if (!in_array($type, ['condition', 'wait', 'create_client_note', 'send_sms', 'send_email'], true)) {
                $errors[] = [
                    'code' => 'unsupported_step_type',
                    'path' => $stepPath . '.type',
                    'message' => sprintf('Workflow step type [%s] is not supported by the runtime executor.', $type),
                ];

                continue;
            }

            if ($type === 'condition') {
                if (trim((string) ($definition['fact'] ?? '')) === '') {
                    $errors[] = [
                        'code' => 'missing_condition_fact',
                        'path' => $stepPath . '.definition.fact',
                        'message' => 'Condition steps must define a fact to evaluate.',
                    ];
                }

                if (!in_array((string) ($definition['operator'] ?? ''), ['eq', 'neq', 'gte', 'lte', 'contains', 'exists'], true)) {
                    $errors[] = [
                        'code' => 'invalid_condition_operator',
                        'path' => $stepPath . '.definition.operator',
                        'message' => 'Condition steps must use a supported operator.',
                    ];
                }

                if ((string) ($definition['operator'] ?? '') !== 'exists' && trim((string) ($definition['value'] ?? '')) === '') {
                    $errors[] = [
                        'code' => 'missing_condition_value',
                        'path' => $stepPath . '.definition.value',
                        'message' => 'Condition steps must define a value unless the operator is exists.',
                    ];
                }
            }

            if ($type === 'wait' && (int) ($definition['durationMinutes'] ?? 0) < 1) {
                $errors[] = [
                    'code' => 'invalid_wait_duration',
                    'path' => $stepPath . '.definition.durationMinutes',
                    'message' => 'Wait steps must define a durationMinutes value greater than zero.',
                ];
            }

            if (in_array($type, ['create_client_note', 'send_sms', 'send_email'], true) && $subjectType !== '' && !in_array($subjectType, ['client', 'application'], true)) {
                $errors[] = [
                    'code' => 'unsupported_action_subject_type',
                    'path' => $stepPath . '.definition',
                    'message' => sprintf('Workflow step type [%s] currently supports only client or application subject types.', $type),
                ];
            }

            if ($type === 'create_client_note' && trim((string) ($definition['bodyTemplate'] ?? $definition['body'] ?? '')) === '') {
                $errors[] = [
                    'code' => 'missing_note_body',
                    'path' => $stepPath . '.definition.bodyTemplate',
                    'message' => 'Client-note steps must define a bodyTemplate or body.',
                ];
            }

            if ($type === 'send_sms' && trim((string) ($definition['bodyTemplate'] ?? $definition['body'] ?? '')) === '') {
                $errors[] = [
                    'code' => 'missing_sms_body',
                    'path' => $stepPath . '.definition.bodyTemplate',
                    'message' => 'SMS steps must define a bodyTemplate or body.',
                ];
            }

            if ($type === 'send_email') {
                if (trim((string) ($definition['subjectTemplate'] ?? $definition['subject'] ?? '')) === '') {
                    $errors[] = [
                        'code' => 'missing_email_subject',
                        'path' => $stepPath . '.definition.subjectTemplate',
                        'message' => 'Email steps must define a subjectTemplate or subject.',
                    ];
                }

                if (trim((string) ($definition['bodyTemplate'] ?? $definition['bodyText'] ?? '')) === '') {
                    $errors[] = [
                        'code' => 'missing_email_body',
                        'path' => $stepPath . '.definition.bodyTemplate',
                        'message' => 'Email steps must define a bodyTemplate or bodyText.',
                    ];
                }
            }
        }

        return [
            'isValid' => $errors === [],
            'errors' => $errors,
        ];
    }
}
