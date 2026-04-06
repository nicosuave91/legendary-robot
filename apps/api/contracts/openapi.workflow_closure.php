<?php

declare(strict_types=1);

return [
    'paths' => [],
    'components' => [
        'schemas' => [
            'WorkflowValidationIssueDto' => [
                'type' => 'object',
                'properties' => [
                    'code' => ['type' => 'string'],
                    'path' => ['type' => 'string'],
                    'message' => ['type' => 'string'],
                ],
                'required' => ['code', 'path', 'message'],
            ],
            'WorkflowDraftValidationDto' => [
                'type' => 'object',
                'properties' => [
                    'hasDraft' => ['type' => 'boolean'],
                    'versionId' => ['type' => 'string', 'nullable' => true],
                    'isValid' => ['type' => 'boolean'],
                    'errors' => [
                        'type' => 'array',
                        'items' => ['$ref' => '#/components/schemas/WorkflowValidationIssueDto'],
                    ],
                ],
                'required' => ['hasDraft', 'versionId', 'isValid', 'errors'],
            ],
            'WorkflowDetailResponse' => [
                'type' => 'object',
                'properties' => [
                    'workflow' => ['$ref' => '#/components/schemas/WorkflowListItem'],
                    'versions' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/WorkflowVersionDto']],
                    'draftValidation' => ['$ref' => '#/components/schemas/WorkflowDraftValidationDto'],
                    'meta' => ['type' => 'object'],
                ],
                'required' => ['workflow', 'versions', 'draftValidation', 'meta'],
            ],
        ],
    ],
];
