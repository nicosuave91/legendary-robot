<?php

declare(strict_types=1);

return [
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
                    'isValid' => ['type' => 'boolean'],
                    'errors' => [
                        'type' => 'array',
                        'items' => ['$ref' => '#/components/schemas/WorkflowDraftValidationIssue'],
                    ],
                ],
                'required' => ['hasDraft', 'isValid', 'errors'],
            ],
            'WorkflowDetailResponse' => [
                'type' => 'object',
                'properties' => [
                    'workflow' => ['$ref' => '#/components/schemas/WorkflowListItem'],
                    'versions' => [
                        'type' => 'array',
                        'items' => ['$ref' => '#/components/schemas/WorkflowVersionDto'],
                    ],
                    'draftValidation' => ['$ref' => '#/components/schemas/WorkflowDraftValidationSummary'],
                    'meta' => [
                        'type' => 'object',
                        'additionalProperties' => true,
                    ],
                ],
                'required' => ['workflow', 'versions', 'draftValidation', 'meta'],
            ],
        ],
    ],
];
