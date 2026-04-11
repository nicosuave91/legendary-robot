<?php

declare(strict_types=1);

return [
    'paths' => [
        '/api/v1/workflows' => [
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
                    '201' => [
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
    ],
    'components' => [
        'schemas' => [
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
                    'errors' => [
                        'type' => 'array',
                        'items' => ['$ref' => '#/components/schemas/WorkflowDraftValidationIssue'],
                    ],
                ],
                'required' => ['hasDraft', 'versionId', 'isValid', 'errors'],
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
                    'currentDraftVersionId' => ['type' => ['string', 'null']],
                    'latestPublishedVersionId' => ['type' => ['string', 'null']],
                    'latestPublishedVersionNumber' => ['type' => 'integer', 'nullable' => true],
                    'currentDraftVersionNumber' => ['type' => 'integer', 'nullable' => true],
                    'latestPublishedAt' => ['type' => 'string', 'nullable' => true],
                    'updatedAt' => ['type' => 'string', 'nullable' => true],
                ],
                'required' => [
                    'id',
                    'workflowKey',
                    'name',
                    'description',
                    'status',
                    'triggerSummary',
                    'currentDraftVersionId',
                    'latestPublishedVersionId',
                    'latestPublishedVersionNumber',
                    'currentDraftVersionNumber',
                    'latestPublishedAt',
                    'updatedAt',
                ],
            ],
            'WorkflowDetailResponse' => [
                'type' => 'object',
                'properties' => [
                    'workflow' => ['$ref' => '#/components/schemas/WorkflowListItem'],
                    'versions' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/WorkflowVersionDto']],
                    'draftValidation' => ['$ref' => '#/components/schemas/WorkflowDraftValidationSummary'],
                    'meta' => [
                        'type' => 'object',
                        'properties' => [
                            'versionCount' => ['type' => 'integer'],
                        ],
                        'required' => ['versionCount'],
                    ],
                ],
                'required' => ['workflow', 'versions', 'draftValidation', 'meta'],
            ],
        ],
    ],
];
