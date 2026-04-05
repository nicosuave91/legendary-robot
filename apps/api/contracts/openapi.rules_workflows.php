<?php

declare(strict_types=1);

return [
    'paths' => [
        '/api/v1/rules' => [
            'get' => [
                'operationId' => 'getRules',
                'summary' => 'List rules',
                'parameters' => [
                    ['name' => 'moduleScope', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                    ['name' => 'status', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Rules list response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/RuleListEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
            'post' => [
                'operationId' => 'postRules',
                'summary' => 'Create rule',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/CreateRuleRequest'],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Rule detail response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/RuleDetailEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/v1/rules/{ruleId}' => [
            'get' => [
                'operationId' => 'getRule',
                'summary' => 'Get rule detail',
                'parameters' => [
                    ['name' => 'ruleId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Rule detail response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/RuleDetailEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
            'patch' => [
                'operationId' => 'patchRule',
                'summary' => 'Update rule draft',
                'parameters' => [
                    ['name' => 'ruleId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateRuleDraftRequest'],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Rule detail response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/RuleDetailEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/v1/rules/{ruleId}/publish' => [
            'post' => [
                'operationId' => 'postRulePublish',
                'summary' => 'Publish rule',
                'parameters' => [
                    ['name' => 'ruleId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/PublishLifecycleRequest'],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Rule detail response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/RuleDetailEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/v1/rules/{ruleId}/execution-logs' => [
            'get' => [
                'operationId' => 'getRuleExecutionLogs',
                'summary' => 'List rule execution logs',
                'parameters' => [
                    ['name' => 'ruleId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Rule execution logs response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/RuleExecutionLogEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/v1/workflows' => [
            'get' => [
                'operationId' => 'getWorkflows',
                'summary' => 'List workflows',
                'parameters' => [
                    ['name' => 'status', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Workflow list response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/WorkflowListEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
            'post' => [
                'operationId' => 'postWorkflows',
                'summary' => 'Create workflow',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/CreateWorkflowRequest'],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Workflow detail response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/WorkflowDetailEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/v1/workflows/{workflowId}' => [
            'get' => [
                'operationId' => 'getWorkflow',
                'summary' => 'Get workflow detail',
                'parameters' => [
                    ['name' => 'workflowId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Workflow detail response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/WorkflowDetailEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
            'patch' => [
                'operationId' => 'patchWorkflow',
                'summary' => 'Update workflow draft',
                'parameters' => [
                    ['name' => 'workflowId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateWorkflowDraftRequest'],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Workflow detail response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/WorkflowDetailEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/v1/workflows/{workflowId}/publish' => [
            'post' => [
                'operationId' => 'postWorkflowPublish',
                'summary' => 'Publish workflow',
                'parameters' => [
                    ['name' => 'workflowId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/PublishLifecycleRequest'],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Workflow detail response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/WorkflowDetailEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/v1/workflows/{workflowId}/runs' => [
            'get' => [
                'operationId' => 'getWorkflowRuns',
                'summary' => 'List workflow runs',
                'parameters' => [
                    ['name' => 'workflowId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Workflow run list response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/WorkflowRunListEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/v1/workflows/{workflowId}/runs/{runId}' => [
            'get' => [
                'operationId' => 'getWorkflowRun',
                'summary' => 'Get workflow run detail',
                'parameters' => [
                    ['name' => 'workflowId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                    ['name' => 'runId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Workflow run detail response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/WorkflowRunDetailEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'components' => [
        'schemas' => [
            'PublishLifecycleRequest' => [
                'type' => 'object',
                'properties' => [
                    'publishNotes' => ['type' => 'string', 'nullable' => true],
                ],
            ],
            'RuleListItem' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'ruleKey' => ['type' => 'string'],
                    'name' => ['type' => 'string'],
                    'description' => ['type' => 'string', 'nullable' => true],
                    'moduleScope' => ['type' => 'string'],
                    'subjectType' => ['type' => 'string'],
                    'status' => ['type' => 'string'],
                    'latestPublishedVersionNumber' => ['type' => 'integer', 'nullable' => true],
                    'currentDraftVersionNumber' => ['type' => 'integer', 'nullable' => true],
                    'latestPublishedAt' => ['type' => 'string', 'nullable' => true],
                    'updatedAt' => ['type' => 'string', 'nullable' => true],
                ],
                'required' => ['id', 'ruleKey', 'name', 'description', 'moduleScope', 'subjectType', 'status', 'latestPublishedVersionNumber', 'currentDraftVersionNumber', 'latestPublishedAt', 'updatedAt'],
            ],
            'RuleVersionDto' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'versionNumber' => ['type' => 'integer'],
                    'lifecycleState' => ['type' => 'string'],
                    'triggerEvent' => ['type' => 'string'],
                    'severity' => ['type' => 'string'],
                    'conditionDefinition' => ['type' => 'object'],
                    'actionDefinition' => ['type' => 'object'],
                    'executionLabel' => ['type' => 'string', 'nullable' => true],
                    'noteTemplate' => ['type' => 'string', 'nullable' => true],
                    'checksum' => ['type' => 'string'],
                    'publishedAt' => ['type' => 'string', 'nullable' => true],
                    'publishedBy' => ['type' => 'string', 'nullable' => true],
                    'createdAt' => ['type' => 'string', 'nullable' => true],
                    'updatedAt' => ['type' => 'string', 'nullable' => true],
                ],
                'required' => ['id', 'versionNumber', 'lifecycleState', 'triggerEvent', 'severity', 'conditionDefinition', 'actionDefinition', 'executionLabel', 'noteTemplate', 'checksum', 'publishedAt', 'publishedBy', 'createdAt', 'updatedAt'],
            ],
            'RuleExecutionLogDto' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'ruleId' => ['type' => 'string'],
                    'ruleVersionId' => ['type' => 'string'],
                    'subjectType' => ['type' => 'string'],
                    'subjectId' => ['type' => 'string'],
                    'triggerEvent' => ['type' => 'string'],
                    'executionSource' => ['type' => 'string'],
                    'outcome' => ['type' => 'string'],
                    'correlationId' => ['type' => 'string', 'nullable' => true],
                    'actorUserId' => ['type' => 'string', 'nullable' => true],
                    'contextSnapshot' => ['type' => 'object'],
                    'outcomeSummary' => ['type' => 'object'],
                    'executedAt' => ['type' => 'string', 'nullable' => true],
                ],
                'required' => ['id', 'ruleId', 'ruleVersionId', 'subjectType', 'subjectId', 'triggerEvent', 'executionSource', 'outcome', 'correlationId', 'actorUserId', 'contextSnapshot', 'outcomeSummary', 'executedAt'],
            ],
            'RuleDetailResponse' => [
                'type' => 'object',
                'properties' => [
                    'rule' => ['$ref' => '#/components/schemas/RuleListItem'],
                    'versions' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/RuleVersionDto']],
                    'executionLogs' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/RuleExecutionLogDto']],
                ],
                'required' => ['rule', 'versions', 'executionLogs'],
            ],
            'RuleListResponse' => [
                'type' => 'object',
                'properties' => [
                    'items' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/RuleListItem']],
                    'meta' => [
                        'type' => 'object',
                        'properties' => [
                            'total' => ['type' => 'integer'],
                        ],
                        'required' => ['total'],
                    ],
                ],
                'required' => ['items', 'meta'],
            ],
            'RuleExecutionLogResponse' => [
                'type' => 'object',
                'properties' => [
                    'items' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/RuleExecutionLogDto']],
                    'meta' => [
                        'type' => 'object',
                        'properties' => [
                            'total' => ['type' => 'integer'],
                        ],
                        'required' => ['total'],
                    ],
                ],
                'required' => ['items', 'meta'],
            ],
            'CreateRuleRequest' => [
                'type' => 'object',
                'properties' => [
                    'ruleKey' => ['type' => 'string'],
                    'name' => ['type' => 'string'],
                    'description' => ['type' => 'string', 'nullable' => true],
                    'moduleScope' => ['type' => 'string'],
                    'subjectType' => ['type' => 'string'],
                    'triggerEvent' => ['type' => 'string'],
                    'severity' => ['type' => 'string'],
                    'industryScope' => ['type' => 'object', 'nullable' => true],
                    'conditionDefinition' => ['type' => 'object'],
                    'actionDefinition' => ['type' => 'object'],
                    'executionLabel' => ['type' => 'string', 'nullable' => true],
                    'noteTemplate' => ['type' => 'string', 'nullable' => true],
                ],
                'required' => ['ruleKey', 'name', 'moduleScope', 'subjectType', 'triggerEvent', 'severity', 'conditionDefinition', 'actionDefinition'],
            ],
            'UpdateRuleDraftRequest' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string'],
                    'description' => ['type' => 'string', 'nullable' => true],
                    'moduleScope' => ['type' => 'string'],
                    'subjectType' => ['type' => 'string'],
                    'triggerEvent' => ['type' => 'string'],
                    'severity' => ['type' => 'string'],
                    'industryScope' => ['type' => 'object', 'nullable' => true],
                    'conditionDefinition' => ['type' => 'object'],
                    'actionDefinition' => ['type' => 'object'],
                    'executionLabel' => ['type' => 'string', 'nullable' => true],
                    'noteTemplate' => ['type' => 'string', 'nullable' => true],
                ],
            ],
            'WorkflowListItem' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'workflowKey' => ['type' => 'string'],
                    'name' => ['type' => 'string'],
                    'description' => ['type' => 'string', 'nullable' => true],
                    'status' => ['type' => 'string'],
                    'triggerSummary' => ['type' => 'string'],
                    'latestPublishedVersionNumber' => ['type' => 'integer', 'nullable' => true],
                    'currentDraftVersionNumber' => ['type' => 'integer', 'nullable' => true],
                    'latestPublishedAt' => ['type' => 'string', 'nullable' => true],
                    'updatedAt' => ['type' => 'string', 'nullable' => true],
                ],
                'required' => ['id', 'workflowKey', 'name', 'description', 'status', 'triggerSummary', 'latestPublishedVersionNumber', 'currentDraftVersionNumber', 'latestPublishedAt', 'updatedAt'],
            ],
            'WorkflowVersionDto' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'versionNumber' => ['type' => 'integer'],
                    'lifecycleState' => ['type' => 'string'],
                    'triggerDefinition' => ['type' => 'object'],
                    'stepsDefinition' => ['type' => 'array', 'items' => ['type' => 'object']],
                    'checksum' => ['type' => 'string'],
                    'publishedAt' => ['type' => 'string', 'nullable' => true],
                    'publishedBy' => ['type' => 'string', 'nullable' => true],
                    'createdAt' => ['type' => 'string', 'nullable' => true],
                    'updatedAt' => ['type' => 'string', 'nullable' => true],
                ],
                'required' => ['id', 'versionNumber', 'lifecycleState', 'triggerDefinition', 'stepsDefinition', 'checksum', 'publishedAt', 'publishedBy', 'createdAt', 'updatedAt'],
            ],
            'WorkflowRunDto' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'workflowId' => ['type' => 'string'],
                    'workflowVersionId' => ['type' => 'string'],
                    'triggerEvent' => ['type' => 'string'],
                    'subjectType' => ['type' => 'string'],
                    'subjectId' => ['type' => 'string'],
                    'status' => ['type' => 'string'],
                    'currentStepIndex' => ['type' => 'integer', 'nullable' => true],
                    'correlationId' => ['type' => 'string', 'nullable' => true],
                    'queuedAt' => ['type' => 'string', 'nullable' => true],
                    'startedAt' => ['type' => 'string', 'nullable' => true],
                    'completedAt' => ['type' => 'string', 'nullable' => true],
                    'failedAt' => ['type' => 'string', 'nullable' => true],
                    'failureSummary' => ['type' => 'object'],
                ],
                'required' => ['id', 'workflowId', 'workflowVersionId', 'triggerEvent', 'subjectType', 'subjectId', 'status', 'currentStepIndex', 'correlationId', 'queuedAt', 'startedAt', 'completedAt', 'failedAt', 'failureSummary'],
            ],
            'WorkflowRunLogDto' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'workflowRunId' => ['type' => 'string'],
                    'workflowVersionId' => ['type' => 'string'],
                    'stepIndex' => ['type' => 'integer', 'nullable' => true],
                    'logType' => ['type' => 'string'],
                    'message' => ['type' => 'string'],
                    'payloadSnapshot' => ['type' => 'object'],
                    'occurredAt' => ['type' => 'string', 'nullable' => true],
                ],
                'required' => ['id', 'workflowRunId', 'workflowVersionId', 'stepIndex', 'logType', 'message', 'payloadSnapshot', 'occurredAt'],
            ],
            'WorkflowDetailResponse' => [
                'type' => 'object',
                'properties' => [
                    'workflow' => ['$ref' => '#/components/schemas/WorkflowListItem'],
                    'versions' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/WorkflowVersionDto']],
                    'meta' => ['type' => 'object'],
                ],
                'required' => ['workflow', 'versions', 'meta'],
            ],
            'WorkflowRunDetailResponse' => [
                'type' => 'object',
                'properties' => [
                    'run' => ['$ref' => '#/components/schemas/WorkflowRunDto'],
                    'logs' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/WorkflowRunLogDto']],
                ],
                'required' => ['run', 'logs'],
            ],
            'WorkflowListResponse' => [
                'type' => 'object',
                'properties' => [
                    'items' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/WorkflowListItem']],
                    'meta' => [
                        'type' => 'object',
                        'properties' => [
                            'total' => ['type' => 'integer'],
                        ],
                        'required' => ['total'],
                    ],
                ],
                'required' => ['items', 'meta'],
            ],
            'WorkflowRunListResponse' => [
                'type' => 'object',
                'properties' => [
                    'items' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/WorkflowRunDto']],
                    'meta' => [
                        'type' => 'object',
                        'properties' => [
                            'total' => ['type' => 'integer'],
                        ],
                        'required' => ['total'],
                    ],
                ],
                'required' => ['items', 'meta'],
            ],
            'CreateWorkflowRequest' => [
                'type' => 'object',
                'properties' => [
                    'workflowKey' => ['type' => 'string'],
                    'name' => ['type' => 'string'],
                    'description' => ['type' => 'string', 'nullable' => true],
                    'triggerDefinition' => ['type' => 'object'],
                    'stepsDefinition' => ['type' => 'array', 'items' => ['type' => 'object']],
                ],
                'required' => ['workflowKey', 'name', 'triggerDefinition', 'stepsDefinition'],
            ],
            'UpdateWorkflowDraftRequest' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string'],
                    'description' => ['type' => 'string', 'nullable' => true],
                    'triggerDefinition' => ['type' => 'object'],
                    'stepsDefinition' => ['type' => 'array', 'items' => ['type' => 'object']],
                ],
            ],
            'RuleListEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['$ref' => '#/components/schemas/RuleListResponse'],
                    'meta' => ['$ref' => '#/components/schemas/ResponseMeta'],
                ],
                'required' => ['data', 'meta'],
            ],
            'RuleDetailEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['$ref' => '#/components/schemas/RuleDetailResponse'],
                    'meta' => ['$ref' => '#/components/schemas/ResponseMeta'],
                ],
                'required' => ['data', 'meta'],
            ],
            'RuleExecutionLogEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['$ref' => '#/components/schemas/RuleExecutionLogResponse'],
                    'meta' => ['$ref' => '#/components/schemas/ResponseMeta'],
                ],
                'required' => ['data', 'meta'],
            ],
            'WorkflowListEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['$ref' => '#/components/schemas/WorkflowListResponse'],
                    'meta' => ['$ref' => '#/components/schemas/ResponseMeta'],
                ],
                'required' => ['data', 'meta'],
            ],
            'WorkflowDetailEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['$ref' => '#/components/schemas/WorkflowDetailResponse'],
                    'meta' => ['$ref' => '#/components/schemas/ResponseMeta'],
                ],
                'required' => ['data', 'meta'],
            ],
            'WorkflowRunListEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['$ref' => '#/components/schemas/WorkflowRunListResponse'],
                    'meta' => ['$ref' => '#/components/schemas/ResponseMeta'],
                ],
                'required' => ['data', 'meta'],
            ],
            'WorkflowRunDetailEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['$ref' => '#/components/schemas/WorkflowRunDetailResponse'],
                    'meta' => ['$ref' => '#/components/schemas/ResponseMeta'],
                ],
                'required' => ['data', 'meta'],
            ],
        ],
    ],
];
