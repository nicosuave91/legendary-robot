<?php

declare(strict_types=1);

return [
    'paths' => [
        '/api/v1/imports' => [
            'get' => [
                'operationId' => 'getImports',
                'summary' => 'List import ledger entries',
                'parameters' => [
                    ['name' => 'status', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                    ['name' => 'importType', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Import ledger response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/ImportListEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
            'post' => [
                'operationId' => 'postImports',
                'summary' => 'Upload a governed import file for staging',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'multipart/form-data' => [
                            'schema' => ['$ref' => '#/components/schemas/CreateImportRequest'],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Created import detail',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/ImportDetailEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/v1/imports/{importId}' => [
            'get' => [
                'operationId' => 'getImport',
                'summary' => 'Return import detail, validation summary, preview rows, and preview errors',
                'parameters' => [
                    ['name' => 'importId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Import detail response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/ImportDetailEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/v1/imports/{importId}/errors' => [
            'get' => [
                'operationId' => 'getImportErrors',
                'summary' => 'Return full row-level import errors',
                'parameters' => [
                    ['name' => 'importId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                    ['name' => 'severity', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Import error response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/ImportErrorEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/v1/imports/{importId}/validate' => [
            'post' => [
                'operationId' => 'postImportValidate',
                'summary' => 'Queue validation for an uploaded import',
                'parameters' => [
                    ['name' => 'importId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Updated import detail response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/ImportDetailEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/v1/imports/{importId}/commit' => [
            'post' => [
                'operationId' => 'postImportCommit',
                'summary' => 'Queue commit for a validated import',
                'parameters' => [
                    ['name' => 'importId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Updated import detail response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/ImportDetailEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/v1/notifications' => [
            'get' => [
                'operationId' => 'getNotifications',
                'summary' => 'List persistent notifications for the current user',
                'parameters' => [
                    ['name' => 'includeDismissed', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'boolean']],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Notification list response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/NotificationListEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/v1/notifications/{notificationId}/dismiss' => [
            'post' => [
                'operationId' => 'postNotificationDismiss',
                'summary' => 'Dismiss a persistent notification on a given UI surface',
                'parameters' => [
                    ['name' => 'notificationId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'requestBody' => [
                    'required' => false,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/DismissNotificationRequest'],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Dismiss notification response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/NotificationDismissEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/v1/notifications/{notificationId}/read' => [
            'post' => [
                'operationId' => 'postNotificationRead',
                'summary' => 'Mark a notification as read',
                'parameters' => [
                    ['name' => 'notificationId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Read notification response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/NotificationReadEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/v1/audit' => [
            'get' => [
                'operationId' => 'getAudit',
                'summary' => 'Search append-only audit records for the current tenant',
                'parameters' => [
                    ['name' => 'action', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                    ['name' => 'subjectType', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                    ['name' => 'subjectId', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                    ['name' => 'actorId', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                    ['name' => 'correlationId', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                    ['name' => 'q', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                    ['name' => 'from', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                    ['name' => 'to', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Audit list response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/AuditListEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'components' => [
        'schemas' => [
            'ImportListItem' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'importType' => ['type' => 'string'],
                    'fileFormat' => ['type' => 'string'],
                    'originalFilename' => ['type' => 'string'],
                    'status' => ['type' => 'string'],
                    'rowCount' => ['type' => 'integer'],
                    'validRowCount' => ['type' => 'integer'],
                    'invalidRowCount' => ['type' => 'integer'],
                    'committedRowCount' => ['type' => 'integer'],
                    'uploadedByUserId' => ['type' => 'string', 'nullable' => true],
                    'validatedByUserId' => ['type' => 'string', 'nullable' => true],
                    'committedByUserId' => ['type' => 'string', 'nullable' => true],
                    'parserVersion' => ['type' => 'string', 'nullable' => true],
                    'canValidate' => ['type' => 'boolean'],
                    'canCommit' => ['type' => 'boolean'],
                    'uploadedAt' => ['type' => 'string', 'nullable' => true],
                    'validatedAt' => ['type' => 'string', 'nullable' => true],
                    'committedAt' => ['type' => 'string', 'nullable' => true],
                ],
                'required' => ['id', 'importType', 'fileFormat', 'originalFilename', 'status', 'rowCount', 'validRowCount', 'invalidRowCount', 'committedRowCount', 'uploadedByUserId', 'validatedByUserId', 'committedByUserId', 'parserVersion', 'canValidate', 'canCommit', 'uploadedAt', 'validatedAt', 'committedAt'],
            ],
            'ImportPreviewRow' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'rowNumber' => ['type' => 'integer'],
                    'rowStatus' => ['type' => 'string'],
                    'normalizedPayload' => ['type' => 'object', 'additionalProperties' => true],
                    'targetSubjectType' => ['type' => 'string', 'nullable' => true],
                    'targetSubjectId' => ['type' => 'string', 'nullable' => true],
                ],
                'required' => ['id', 'rowNumber', 'rowStatus', 'normalizedPayload', 'targetSubjectType', 'targetSubjectId'],
            ],
            'ImportErrorItem' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'rowNumber' => ['type' => 'integer'],
                    'fieldName' => ['type' => 'string', 'nullable' => true],
                    'errorCode' => ['type' => 'string'],
                    'severity' => ['type' => 'string'],
                    'message' => ['type' => 'string'],
                    'contextSnapshot' => ['type' => 'object', 'additionalProperties' => true],
                ],
                'required' => ['id', 'rowNumber', 'fieldName', 'errorCode', 'severity', 'message', 'contextSnapshot'],
            ],
            'ImportDetailSummary' => [
                'type' => 'object',
                'properties' => [
                    'blockingErrorCount' => ['type' => 'integer'],
                    'warningCount' => ['type' => 'integer'],
                    'createdTargetCount' => ['type' => 'integer'],
                ],
                'required' => ['blockingErrorCount', 'warningCount', 'createdTargetCount'],
            ],
            'ImportDetailResponse' => [
                'type' => 'object',
                'properties' => [
                    'import' => [
                        'allOf' => [
                            ['$ref' => '#/components/schemas/ImportListItem'],
                            [
                                'type' => 'object',
                                'properties' => [
                                    'summary' => ['$ref' => '#/components/schemas/ImportDetailSummary'],
                                    'previewRows' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ImportPreviewRow']],
                                    'previewErrors' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ImportErrorItem']],
                                    'latestFailureSummary' => ['type' => 'object', 'additionalProperties' => true, 'nullable' => true],
                                    'latestCorrelationId' => ['type' => 'string', 'nullable' => true],
                                    'storageReference' => ['type' => 'string'],
                                ],
                                'required' => ['summary', 'previewRows', 'previewErrors', 'latestFailureSummary', 'latestCorrelationId', 'storageReference'],
                            ],
                        ],
                    ],
                ],
                'required' => ['import'],
            ],
            'ImportListResponse' => [
                'type' => 'object',
                'properties' => [
                    'items' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ImportListItem']],
                    'meta' => ['type' => 'object', 'properties' => ['total' => ['type' => 'integer']], 'required' => ['total']],
                ],
                'required' => ['items', 'meta'],
            ],
            'ImportErrorResponse' => [
                'type' => 'object',
                'properties' => [
                    'items' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ImportErrorItem']],
                    'meta' => ['type' => 'object', 'properties' => ['total' => ['type' => 'integer']], 'required' => ['total']],
                ],
                'required' => ['items', 'meta'],
            ],
            'CreateImportRequest' => [
                'type' => 'object',
                'properties' => [
                    'importType' => ['type' => 'string'],
                    'file' => ['type' => 'string', 'format' => 'binary'],
                ],
                'required' => ['importType', 'file'],
            ],
            'NotificationListItem' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'category' => ['type' => 'string'],
                    'notificationType' => ['type' => 'string'],
                    'title' => ['type' => 'string'],
                    'body' => ['type' => 'string', 'nullable' => true],
                    'tone' => ['type' => 'string'],
                    'actionUrl' => ['type' => 'string', 'nullable' => true],
                    'sourceEventType' => ['type' => 'string'],
                    'sourceEventId' => ['type' => 'string', 'nullable' => true],
                    'isRead' => ['type' => 'boolean'],
                    'readAt' => ['type' => 'string', 'nullable' => true],
                    'isDismissed' => ['type' => 'boolean'],
                    'dismissedAt' => ['type' => 'string', 'nullable' => true],
                    'emittedAt' => ['type' => 'string', 'nullable' => true],
                    'payloadSnapshot' => ['type' => 'object', 'additionalProperties' => true],
                ],
                'required' => ['id', 'category', 'notificationType', 'title', 'body', 'tone', 'actionUrl', 'sourceEventType', 'sourceEventId', 'isRead', 'readAt', 'isDismissed', 'dismissedAt', 'emittedAt', 'payloadSnapshot'],
            ],
            'NotificationListResponse' => [
                'type' => 'object',
                'properties' => [
                    'items' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/NotificationListItem']],
                    'meta' => [
                        'type' => 'object',
                        'properties' => [
                            'total' => ['type' => 'integer'],
                            'unread' => ['type' => 'integer'],
                        ],
                        'required' => ['total', 'unread'],
                    ],
                ],
                'required' => ['items', 'meta'],
            ],
            'DismissNotificationRequest' => [
                'type' => 'object',
                'properties' => [
                    'surface' => ['type' => 'string', 'nullable' => true],
                ],
            ],
            'NotificationDismissResponse' => [
                'type' => 'object',
                'properties' => [
                    'notificationId' => ['type' => 'string'],
                    'dismissed' => ['type' => 'boolean'],
                    'dismissedAt' => ['type' => 'string', 'nullable' => true],
                    'surface' => ['type' => 'string'],
                ],
                'required' => ['notificationId', 'dismissed', 'dismissedAt', 'surface'],
            ],
            'NotificationReadResponse' => [
                'type' => 'object',
                'properties' => [
                    'notificationId' => ['type' => 'string'],
                    'read' => ['type' => 'boolean'],
                    'readAt' => ['type' => 'string', 'nullable' => true],
                ],
                'required' => ['notificationId', 'read', 'readAt'],
            ],
            'AuditListItem' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'action' => ['type' => 'string'],
                    'subjectType' => ['type' => 'string'],
                    'subjectId' => ['type' => 'string', 'nullable' => true],
                    'actorId' => ['type' => 'string', 'nullable' => true],
                    'actorDisplayName' => ['type' => 'string', 'nullable' => true],
                    'correlationId' => ['type' => 'string', 'nullable' => true],
                    'beforeSummary' => ['type' => 'object', 'additionalProperties' => true],
                    'afterSummary' => ['type' => 'object', 'additionalProperties' => true],
                    'occurredAt' => ['type' => 'string', 'nullable' => true],
                ],
                'required' => ['id', 'action', 'subjectType', 'subjectId', 'actorId', 'actorDisplayName', 'correlationId', 'beforeSummary', 'afterSummary', 'occurredAt'],
            ],
            'AuditListResponse' => [
                'type' => 'object',
                'properties' => [
                    'items' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/AuditListItem']],
                    'meta' => [
                        'type' => 'object',
                        'properties' => [
                            'total' => ['type' => 'integer'],
                            'page' => ['type' => 'integer'],
                            'perPage' => ['type' => 'integer'],
                        ],
                        'required' => ['total', 'page', 'perPage'],
                    ],
                ],
                'required' => ['items', 'meta'],
            ],
            'ImportListEnvelope' => ['type' => 'object', 'properties' => ['data' => ['$ref' => '#/components/schemas/ImportListResponse'], 'meta' => ['$ref' => '#/components/schemas/ResponseMeta']], 'required' => ['data', 'meta']],
            'ImportDetailEnvelope' => ['type' => 'object', 'properties' => ['data' => ['$ref' => '#/components/schemas/ImportDetailResponse'], 'meta' => ['$ref' => '#/components/schemas/ResponseMeta']], 'required' => ['data', 'meta']],
            'ImportErrorEnvelope' => ['type' => 'object', 'properties' => ['data' => ['$ref' => '#/components/schemas/ImportErrorResponse'], 'meta' => ['$ref' => '#/components/schemas/ResponseMeta']], 'required' => ['data', 'meta']],
            'NotificationListEnvelope' => ['type' => 'object', 'properties' => ['data' => ['$ref' => '#/components/schemas/NotificationListResponse'], 'meta' => ['$ref' => '#/components/schemas/ResponseMeta']], 'required' => ['data', 'meta']],
            'NotificationDismissEnvelope' => ['type' => 'object', 'properties' => ['data' => ['$ref' => '#/components/schemas/NotificationDismissResponse'], 'meta' => ['$ref' => '#/components/schemas/ResponseMeta']], 'required' => ['data', 'meta']],
            'NotificationReadEnvelope' => ['type' => 'object', 'properties' => ['data' => ['$ref' => '#/components/schemas/NotificationReadResponse'], 'meta' => ['$ref' => '#/components/schemas/ResponseMeta']], 'required' => ['data', 'meta']],
            'AuditListEnvelope' => ['type' => 'object', 'properties' => ['data' => ['$ref' => '#/components/schemas/AuditListResponse'], 'meta' => ['$ref' => '#/components/schemas/ResponseMeta']], 'required' => ['data', 'meta']],
        ],
    ],
];
