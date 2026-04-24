<?php

declare(strict_types=1);

$metaEnvelope = static fn (string $schema): array => [
    'type' => 'object',
    'properties' => [
        'data' => ['$ref' => "#/components/schemas/{$schema}"],
        'meta' => ['$ref' => '#/components/schemas/ResponseMeta'],
    ],
    'required' => ['data', 'meta'],
];

$listMeta = [
    'type' => 'object',
    'properties' => [
        'total' => ['type' => 'integer'],
    ],
    'required' => ['total'],
];

$openObject = ['type' => 'object', 'additionalProperties' => true];

return [
    'paths' => [
        '/api/v1/clients/{clientId}/disposition-transitions' => [
            'post' => [
                'operationId' => 'postClientDispositionTransitions',
                'summary' => 'Transition client disposition',
                'parameters' => [
                    ['name' => 'clientId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/DispositionTransitionRequest']]],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Disposition transition response',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/DispositionTransitionEnvelope']]],
                    ],
                ],
            ],
        ],
        '/api/v1/clients/{clientId}/applications' => [
            'get' => [
                'operationId' => 'getClientApplications',
                'summary' => 'List client applications',
                'parameters' => [
                    ['name' => 'clientId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Client applications response',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ClientApplicationsListEnvelope']]],
                    ],
                ],
            ],
            'post' => [
                'operationId' => 'postClientApplications',
                'summary' => 'Create client application',
                'parameters' => [
                    ['name' => 'clientId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/CreateApplicationRequest']]],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Application detail response',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ApplicationDetailEnvelope']]],
                    ],
                ],
            ],
        ],
        '/api/v1/clients/{clientId}/applications/{applicationId}' => [
            'get' => [
                'operationId' => 'getClientApplication',
                'summary' => 'Get client application',
                'parameters' => [
                    ['name' => 'clientId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                    ['name' => 'applicationId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Application detail response',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ApplicationDetailEnvelope']]],
                    ],
                ],
            ],
        ],
        '/api/v1/clients/{clientId}/applications/{applicationId}/status-transitions' => [
            'post' => [
                'operationId' => 'postClientApplicationStatusTransitions',
                'summary' => 'Transition application status',
                'parameters' => [
                    ['name' => 'clientId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                    ['name' => 'applicationId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/TransitionApplicationStatusRequest']]],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Application transition response',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ApplicationTransitionEnvelope']]],
                    ],
                ],
            ],
        ],
        '/api/v1/rules' => [
            'get' => [
                'operationId' => 'getRules',
                'summary' => 'List rules',
                'parameters' => [
                    ['name' => 'status', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                    ['name' => 'moduleScope', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Rule list response',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/RuleListEnvelope']]],
                    ],
                ],
            ],
            'post' => [
                'operationId' => 'postRules',
                'summary' => 'Create rule',
                'requestBody' => [
                    'required' => true,
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/CreateRuleRequest']]],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Rule detail response',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/RuleDetailEnvelope']]],
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
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/RuleDetailEnvelope']]],
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
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/UpdateRuleDraftRequest']]],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Rule detail response',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/RuleDetailEnvelope']]],
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
                    'required' => false,
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/PublishLifecycleRequest']]],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Rule detail response',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/RuleDetailEnvelope']]],
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
                        'description' => 'Rule execution log response',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/RuleExecutionLogEnvelope']]],
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
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/WorkflowListEnvelope']]],
                    ],
                ],
            ],
            'post' => [
                'operationId' => 'postWorkflows',
                'summary' => 'Create workflow',
                'requestBody' => [
                    'required' => true,
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/CreateWorkflowRequest']]],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Workflow detail response',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/WorkflowDetailEnvelope']]],
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
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/WorkflowDetailEnvelope']]],
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
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/UpdateWorkflowDraftRequest']]],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Workflow detail response',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/WorkflowDetailEnvelope']]],
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
                    'required' => false,
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/PublishLifecycleRequest']]],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Workflow detail response',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/WorkflowDetailEnvelope']]],
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
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/WorkflowRunListEnvelope']]],
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
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/WorkflowRunDetailEnvelope']]],
                    ],
                ],
            ],
        ],
    ],
    'components' => [
        'schemas' => [
            'CreateClientRequest' => ['$ref' => '#/components/schemas/CreateOrUpdateClientRequest'],
            'UpdateClientRequest' => ['$ref' => '#/components/schemas/CreateOrUpdateClientRequest'],
            'DispositionProjection' => [
                'type' => 'object',
                'properties' => [
                    'code' => ['type' => 'string'],
                    'label' => ['type' => 'string'],
                    'tone' => ['type' => 'string', 'enum' => ['neutral', 'success', 'warning', 'danger', 'info']],
                    'isTerminal' => ['type' => 'boolean'],
                    'changedAt' => ['type' => ['string', 'null']],
                    'changedByDisplayName' => ['type' => ['string', 'null']],
                ],
                'required' => ['code', 'label', 'tone', 'isTerminal', 'changedAt', 'changedByDisplayName'],
            ],
            'DispositionTransitionOption' => [
                'type' => 'object',
                'properties' => [
                    'code' => ['type' => 'string'],
                    'label' => ['type' => 'string'],
                    'tone' => ['type' => 'string', 'enum' => ['neutral', 'success', 'warning', 'danger', 'info']],
                ],
                'required' => ['code', 'label', 'tone'],
            ],
            'DispositionHistoryItem' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'fromDispositionCode' => ['type' => ['string', 'null']],
                    'toDispositionCode' => ['type' => 'string'],
                    'reason' => ['type' => ['string', 'null']],
                    'occurredAt' => ['type' => ['string', 'null']],
                    'actorDisplayName' => ['type' => ['string', 'null']],
                ],
                'required' => ['id', 'fromDispositionCode', 'toDispositionCode', 'reason', 'occurredAt', 'actorDisplayName'],
            ],
            'TransitionIssue' => [
                'type' => 'object',
                'properties' => [
                    'code' => ['type' => 'string'],
                    'message' => ['type' => 'string'],
                    'severity' => ['type' => 'string', 'enum' => ['warning', 'blocking']],
                ],
                'required' => ['code', 'message', 'severity'],
            ],
            'DispositionTransitionRequest' => [
                'type' => 'object',
                'properties' => [
                    'targetDispositionCode' => ['type' => 'string'],
                    'reason' => ['type' => ['string', 'null']],
                    'acknowledgeWarnings' => ['type' => 'boolean'],
                ],
                'required' => ['targetDispositionCode'],
            ],
            'DispositionTransitionResponse' => [
                'type' => 'object',
                'properties' => [
                    'result' => ['type' => 'string', 'enum' => ['transitioned', 'blocked', 'warning_confirmation_required']],
                    'currentDisposition' => ['$ref' => '#/components/schemas/DispositionProjection'],
                    'availableTransitions' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/DispositionTransitionOption']],
                    'warnings' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/TransitionIssue']],
                    'blockingIssues' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/TransitionIssue']],
                    'historyEntry' => ['oneOf' => [['$ref' => '#/components/schemas/DispositionHistoryItem'], ['type' => 'null']]],
                ],
                'required' => ['result', 'currentDisposition', 'availableTransitions', 'warnings', 'blockingIssues', 'historyEntry'],
            ],
            'DispositionTransitionEnvelope' => $metaEnvelope('DispositionTransitionResponse'),
            'ApplicationStatusTransitionOption' => [
                'type' => 'object',
                'properties' => [
                    'code' => ['type' => 'string'],
                    'label' => ['type' => 'string'],
                    'tone' => ['type' => 'string', 'enum' => ['neutral', 'success', 'warning', 'danger', 'info']],
                ],
                'required' => ['code', 'label', 'tone'],
            ],
            'ApplicationStatusSummary' => [
                'type' => 'object',
                'properties' => [
                    'code' => ['type' => 'string'],
                    'label' => ['type' => 'string'],
                    'tone' => ['type' => 'string', 'enum' => ['neutral', 'success', 'warning', 'danger', 'info']],
                    'changedAt' => ['type' => ['string', 'null']],
                ],
                'required' => ['code', 'label', 'tone', 'changedAt'],
            ],
            'ApplicationRuleSummary' => [
                'type' => 'object',
                'properties' => [
                    'infoCount' => ['type' => 'integer'],
                    'warningCount' => ['type' => 'integer'],
                    'blockingCount' => ['type' => 'integer'],
                    'lastAppliedAt' => ['type' => ['string', 'null']],
                ],
                'required' => ['infoCount', 'warningCount', 'blockingCount', 'lastAppliedAt'],
            ],
            'ApplicationSummary' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'applicationNumber' => ['type' => 'string'],
                    'productType' => ['type' => 'string'],
                    'ownerDisplayName' => ['type' => ['string', 'null']],
                    'currentStatus' => ['$ref' => '#/components/schemas/ApplicationStatusSummary'],
                    'ruleSummary' => ['$ref' => '#/components/schemas/ApplicationRuleSummary'],
                    'createdAt' => ['type' => ['string', 'null']],
                    'updatedAt' => ['type' => ['string', 'null']],
                ],
                'required' => ['id', 'applicationNumber', 'productType', 'ownerDisplayName', 'currentStatus', 'ruleSummary', 'createdAt', 'updatedAt'],
            ],
            'ClientApplicationsListResponse' => [
                'type' => 'object',
                'properties' => [
                    'items' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ApplicationSummary']],
                    'meta' => $listMeta,
                ],
                'required' => ['items', 'meta'],
            ],
            'ClientApplicationsListEnvelope' => $metaEnvelope('ClientApplicationsListResponse'),
            'ApplicationStatusHistoryItem' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'fromStatus' => ['type' => ['string', 'null']],
                    'toStatus' => ['type' => 'string'],
                    'reason' => ['type' => ['string', 'null']],
                    'occurredAt' => ['type' => ['string', 'null']],
                    'actorDisplayName' => ['type' => ['string', 'null']],
                ],
                'required' => ['id', 'fromStatus', 'toStatus', 'reason', 'occurredAt', 'actorDisplayName'],
            ],
            'ApplicationRuleNote' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'ruleKey' => ['type' => 'string'],
                    'ruleVersion' => ['type' => 'string'],
                    'outcome' => ['type' => 'string', 'enum' => ['info', 'warning', 'blocking']],
                    'title' => ['type' => 'string'],
                    'body' => ['type' => 'string'],
                    'appliedAt' => ['type' => ['string', 'null']],
                    'isViewOnly' => ['type' => 'boolean'],
                ],
                'required' => ['id', 'ruleKey', 'ruleVersion', 'outcome', 'title', 'body', 'appliedAt', 'isViewOnly'],
            ],
            'ApplicationDetailApplication' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'applicationNumber' => ['type' => 'string'],
                    'productType' => ['type' => 'string'],
                    'ownerDisplayName' => ['type' => ['string', 'null']],
                    'currentStatus' => ['$ref' => '#/components/schemas/ApplicationStatusSummary'],
                    'ruleSummary' => ['$ref' => '#/components/schemas/ApplicationRuleSummary'],
                    'createdAt' => ['type' => ['string', 'null']],
                    'updatedAt' => ['type' => ['string', 'null']],
                    'externalReference' => ['type' => ['string', 'null']],
                    'amountRequested' => ['type' => ['string', 'null']],
                    'submittedAt' => ['type' => ['string', 'null']],
                    'availableStatusTransitions' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ApplicationStatusTransitionOption']],
                ],
                'required' => ['id', 'applicationNumber', 'productType', 'ownerDisplayName', 'currentStatus', 'ruleSummary', 'createdAt', 'updatedAt', 'externalReference', 'amountRequested', 'submittedAt', 'availableStatusTransitions'],
            ],
            'ApplicationDetailResponse' => [
                'type' => 'object',
                'properties' => [
                    'application' => ['$ref' => '#/components/schemas/ApplicationDetailApplication'],
                    'statusHistory' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ApplicationStatusHistoryItem']],
                    'ruleNotes' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ApplicationRuleNote']],
                ],
                'required' => ['application', 'statusHistory', 'ruleNotes'],
            ],
            'ApplicationDetailEnvelope' => $metaEnvelope('ApplicationDetailResponse'),
            'CreateApplicationRequest' => [
                'type' => 'object',
                'properties' => [
                    'productType' => ['type' => 'string'],
                    'ownerUserId' => ['type' => ['string', 'null']],
                    'externalReference' => ['type' => ['string', 'null']],
                    'amountRequested' => ['type' => ['number', 'null']],
                    'submittedAt' => ['type' => ['string', 'null']],
                    'metadata' => ['type' => ['object', 'null'], 'additionalProperties' => true],
                ],
                'required' => ['productType'],
            ],
            'TransitionApplicationStatusRequest' => [
                'type' => 'object',
                'properties' => [
                    'targetStatus' => ['type' => 'string', 'enum' => ['draft', 'submitted', 'in_review', 'approved', 'declined', 'withdrawn']],
                    'submittedAt' => ['type' => ['string', 'null']],
                    'reason' => ['type' => ['string', 'null']],
                ],
                'required' => ['targetStatus'],
            ],
            'ApplicationTransitionResponse' => [
                'type' => 'object',
                'properties' => [
                    'result' => ['type' => 'string', 'enum' => ['transitioned', 'blocked']],
                    'blockingIssues' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/TransitionIssue']],
                    'warnings' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/TransitionIssue']],
                    'application' => ['$ref' => '#/components/schemas/ApplicationDetailResponse'],
                ],
                'required' => ['result', 'blockingIssues', 'warnings', 'application'],
            ],
            'ApplicationTransitionEnvelope' => $metaEnvelope('ApplicationTransitionResponse'),
            'PublishLifecycleRequest' => [
                'type' => 'object',
                'properties' => [
                    'publishNotes' => ['type' => ['string', 'null']],
                ],
            ],
            'RuleListItem' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'ruleKey' => ['type' => 'string'],
                    'name' => ['type' => 'string'],
                    'description' => ['type' => ['string', 'null']],
                    'moduleScope' => ['type' => 'string'],
                    'subjectType' => ['type' => 'string'],
                    'status' => ['type' => 'string'],
                    'latestPublishedVersionNumber' => ['type' => ['integer', 'null']],
                    'currentDraftVersionNumber' => ['type' => ['integer', 'null']],
                    'latestPublishedAt' => ['type' => ['string', 'null']],
                    'updatedAt' => ['type' => ['string', 'null']],
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
                    'conditionDefinition' => $openObject,
                    'actionDefinition' => $openObject,
                    'executionLabel' => ['type' => ['string', 'null']],
                    'noteTemplate' => ['type' => ['string', 'null']],
                    'checksum' => ['type' => 'string'],
                    'publishedAt' => ['type' => ['string', 'null']],
                    'publishedBy' => ['type' => ['string', 'null']],
                    'createdAt' => ['type' => ['string', 'null']],
                    'updatedAt' => ['type' => ['string', 'null']],
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
                    'correlationId' => ['type' => ['string', 'null']],
                    'actorUserId' => ['type' => ['string', 'null']],
                    'contextSnapshot' => $openObject,
                    'outcomeSummary' => $openObject,
                    'executedAt' => ['type' => ['string', 'null']],
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
                    'meta' => $listMeta,
                ],
                'required' => ['items', 'meta'],
            ],
            'RuleExecutionLogResponse' => [
                'type' => 'object',
                'properties' => [
                    'items' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/RuleExecutionLogDto']],
                    'meta' => $listMeta,
                ],
                'required' => ['items', 'meta'],
            ],
            'RuleListEnvelope' => $metaEnvelope('RuleListResponse'),
            'RuleDetailEnvelope' => $metaEnvelope('RuleDetailResponse'),
            'RuleExecutionLogEnvelope' => $metaEnvelope('RuleExecutionLogResponse'),
            'CreateRuleRequest' => [
                'type' => 'object',
                'properties' => [
                    'ruleKey' => ['type' => 'string'],
                    'name' => ['type' => 'string'],
                    'description' => ['type' => ['string', 'null']],
                    'moduleScope' => ['type' => 'string'],
                    'subjectType' => ['type' => 'string'],
                    'triggerEvent' => ['type' => 'string'],
                    'severity' => ['type' => 'string'],
                    'industryScope' => ['type' => ['object', 'null'], 'additionalProperties' => true],
                    'conditionDefinition' => $openObject,
                    'actionDefinition' => $openObject,
                    'executionLabel' => ['type' => ['string', 'null']],
                    'noteTemplate' => ['type' => ['string', 'null']],
                ],
                'required' => ['ruleKey', 'name', 'moduleScope', 'subjectType', 'triggerEvent', 'severity', 'conditionDefinition', 'actionDefinition'],
            ],
            'UpdateRuleDraftRequest' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string'],
                    'description' => ['type' => ['string', 'null']],
                    'moduleScope' => ['type' => 'string'],
                    'subjectType' => ['type' => 'string'],
                    'triggerEvent' => ['type' => 'string'],
                    'severity' => ['type' => 'string'],
                    'industryScope' => ['type' => ['object', 'null'], 'additionalProperties' => true],
                    'conditionDefinition' => $openObject,
                    'actionDefinition' => $openObject,
                    'executionLabel' => ['type' => ['string', 'null']],
                    'noteTemplate' => ['type' => ['string', 'null']],
                ],
            ],
            'WorkflowDraftValidationIssue' => [
                'type' => 'object',
                'properties' => [
                    'code' => ['type' => 'string'],
                    'path' => ['type' => 'string'],
                    'message' => ['type' => 'string'],
                ],
                'required' => ['code', 'path', 'message'],
            ],
            'WorkflowDraftValidationSummary' => [
                'type' => 'object',
                'properties' => [
                    'hasDraft' => ['type' => 'boolean'],
                    'versionId' => ['type' => ['string', 'null']],
                    'isValid' => ['type' => 'boolean'],
                    'errors' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/WorkflowDraftValidationIssue']],
                ],
                'required' => ['hasDraft', 'versionId', 'isValid', 'errors'],
            ],
            'WorkflowListItem' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'workflowKey' => ['type' => 'string'],
                    'name' => ['type' => 'string'],
                    'description' => ['type' => ['string', 'null']],
                    'status' => ['type' => 'string'],
                    'triggerSummary' => ['type' => 'string'],
                    'latestPublishedVersionNumber' => ['type' => ['integer', 'null']],
                    'currentDraftVersionNumber' => ['type' => ['integer', 'null']],
                    'latestPublishedAt' => ['type' => ['string', 'null']],
                    'updatedAt' => ['type' => ['string', 'null']],
                ],
                'required' => ['id', 'workflowKey', 'name', 'description', 'status', 'triggerSummary', 'latestPublishedVersionNumber', 'currentDraftVersionNumber', 'latestPublishedAt', 'updatedAt'],
            ],
            'WorkflowVersionDto' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'versionNumber' => ['type' => 'integer'],
                    'lifecycleState' => ['type' => 'string'],
                    'triggerDefinition' => $openObject,
                    'stepsDefinition' => ['type' => 'array', 'items' => $openObject],
                    'checksum' => ['type' => 'string'],
                    'publishedAt' => ['type' => ['string', 'null']],
                    'publishedBy' => ['type' => ['string', 'null']],
                    'createdAt' => ['type' => ['string', 'null']],
                    'updatedAt' => ['type' => ['string', 'null']],
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
                    'currentStepIndex' => ['type' => ['integer', 'null']],
                    'correlationId' => ['type' => ['string', 'null']],
                    'queuedAt' => ['type' => ['string', 'null']],
                    'startedAt' => ['type' => ['string', 'null']],
                    'completedAt' => ['type' => ['string', 'null']],
                    'failedAt' => ['type' => ['string', 'null']],
                    'failureSummary' => $openObject,
                ],
                'required' => ['id', 'workflowId', 'workflowVersionId', 'triggerEvent', 'subjectType', 'subjectId', 'status', 'currentStepIndex', 'correlationId', 'queuedAt', 'startedAt', 'completedAt', 'failedAt', 'failureSummary'],
            ],
            'WorkflowRunLogDto' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'workflowRunId' => ['type' => 'string'],
                    'workflowVersionId' => ['type' => 'string'],
                    'stepIndex' => ['type' => ['integer', 'null']],
                    'logType' => ['type' => 'string'],
                    'message' => ['type' => 'string'],
                    'payloadSnapshot' => $openObject,
                    'occurredAt' => ['type' => ['string', 'null']],
                ],
                'required' => ['id', 'workflowRunId', 'workflowVersionId', 'stepIndex', 'logType', 'message', 'payloadSnapshot', 'occurredAt'],
            ],
            'WorkflowDetailResponse' => [
                'type' => 'object',
                'properties' => [
                    'workflow' => ['$ref' => '#/components/schemas/WorkflowListItem'],
                    'versions' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/WorkflowVersionDto']],
                    'draftValidation' => ['$ref' => '#/components/schemas/WorkflowDraftValidationSummary'],
                    'meta' => ['type' => 'object', 'additionalProperties' => true],
                ],
                'required' => ['workflow', 'versions', 'draftValidation', 'meta'],
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
                    'meta' => $listMeta,
                ],
                'required' => ['items', 'meta'],
            ],
            'WorkflowRunListResponse' => [
                'type' => 'object',
                'properties' => [
                    'items' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/WorkflowRunDto']],
                    'meta' => $listMeta,
                ],
                'required' => ['items', 'meta'],
            ],
            'WorkflowListEnvelope' => $metaEnvelope('WorkflowListResponse'),
            'WorkflowDetailEnvelope' => $metaEnvelope('WorkflowDetailResponse'),
            'WorkflowRunListEnvelope' => $metaEnvelope('WorkflowRunListResponse'),
            'WorkflowRunDetailEnvelope' => $metaEnvelope('WorkflowRunDetailResponse'),
            'CreateWorkflowRequest' => [
                'type' => 'object',
                'properties' => [
                    'workflowKey' => ['type' => 'string'],
                    'name' => ['type' => 'string'],
                    'description' => ['type' => ['string', 'null']],
                    'triggerDefinition' => $openObject,
                    'stepsDefinition' => ['type' => 'array', 'items' => $openObject],
                ],
                'required' => ['workflowKey', 'name', 'triggerDefinition', 'stepsDefinition'],
            ],
            'UpdateWorkflowDraftRequest' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string'],
                    'description' => ['type' => ['string', 'null']],
                    'triggerDefinition' => $openObject,
                    'stepsDefinition' => ['type' => 'array', 'items' => $openObject],
                ],
            ],
        ],
    ],
];
