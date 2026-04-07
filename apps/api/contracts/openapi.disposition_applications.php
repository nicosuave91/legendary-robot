<?php

declare(strict_types=1);

return [
    'paths' => [
        '/api/v1/clients/{clientId}' => [
            'patch' => [
                'operationId' => 'patchClient',
                'summary' => 'Update editable client profile fields without lifecycle mutation',
                'parameters' => [
                    ['name' => 'clientId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/UpdateClientRequest']]],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Updated client response',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ClientEnvelope']]],
                    ],
                ],
            ],
        ],
    ],
    'components' => [
        'schemas' => [
            'ClientWorkspaceRecommendedAction' => [
                'type' => 'object',
                'properties' => [
                    'code' => ['type' => 'string'],
                    'title' => ['type' => 'string'],
                    'description' => ['type' => 'string'],
                    'tone' => ['type' => 'string', 'enum' => ['neutral', 'info', 'success', 'warning', 'danger']],
                    'ctaLabel' => ['type' => ['string', 'null']],
                    'ctaHref' => ['type' => ['string', 'null']],
                ],
                'required' => ['code', 'title', 'description', 'tone', 'ctaLabel', 'ctaHref'],
            ],
            'ClientWorkspaceLatestCommunication' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'channel' => ['type' => 'string', 'enum' => ['sms', 'mms', 'email', 'voice']],
                    'direction' => ['type' => 'string', 'enum' => ['inbound', 'outbound', 'system']],
                    'occurredAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                    'preview' => ['type' => ['string', 'null']],
                    'status' => ['$ref' => '#/components/schemas/DeliveryStatusProjection'],
                ],
                'required' => ['id', 'channel', 'direction', 'occurredAt', 'preview', 'status'],
            ],
            'ClientWorkspaceNextEvent' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'title' => ['type' => 'string'],
                    'eventType' => ['type' => 'string', 'enum' => ['appointment', 'follow_up', 'document_review', 'call', 'deadline', 'task_batch']],
                    'startsAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                    'endsAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                    'taskSummary' => ['$ref' => '#/components/schemas/EventTaskSummary'],
                ],
                'required' => ['id', 'title', 'eventType', 'startsAt', 'endsAt', 'taskSummary'],
            ],
            'ClientWorkspaceLeadApplication' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'applicationNumber' => ['type' => 'string'],
                    'productType' => ['type' => 'string'],
                    'currentStatus' => ['$ref' => '#/components/schemas/ApplicationStatusSummary'],
                    'ruleSummary' => ['$ref' => '#/components/schemas/ApplicationRuleSummary'],
                ],
                'required' => ['id', 'applicationNumber', 'productType', 'currentStatus', 'ruleSummary'],
            ],
            'ClientWorkspaceRecentNote' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'body' => ['type' => 'string'],
                    'authorDisplayName' => ['type' => 'string'],
                    'createdAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                ],
                'required' => ['id', 'body', 'authorDisplayName', 'createdAt'],
            ],
            'ClientWorkspaceOverview' => [
                'type' => 'object',
                'properties' => [
                    'recommendedAction' => ['$ref' => '#/components/schemas/ClientWorkspaceRecommendedAction'],
                    'latestCommunication' => [
                        'oneOf' => [
                            ['$ref' => '#/components/schemas/ClientWorkspaceLatestCommunication'],
                            ['type' => 'null'],
                        ],
                    ],
                    'nextEvent' => [
                        'oneOf' => [
                            ['$ref' => '#/components/schemas/ClientWorkspaceNextEvent'],
                            ['type' => 'null'],
                        ],
                    ],
                    'leadApplication' => [
                        'oneOf' => [
                            ['$ref' => '#/components/schemas/ClientWorkspaceLeadApplication'],
                            ['type' => 'null'],
                        ],
                    ],
                    'recentNote' => [
                        'oneOf' => [
                            ['$ref' => '#/components/schemas/ClientWorkspaceRecentNote'],
                            ['type' => 'null'],
                        ],
                    ],
                ],
                'required' => ['recommendedAction', 'latestCommunication', 'nextEvent', 'leadApplication', 'recentNote'],
            ],
            'ClientWorkspaceResponse' => [
                'type' => 'object',
                'properties' => [
                    'client' => ['$ref' => '#/components/schemas/ClientDetail'],
                    'currentDisposition' => ['$ref' => '#/components/schemas/DispositionProjection'],
                    'availableDispositionTransitions' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/DispositionTransitionOption']],
                    'dispositionHistory' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/DispositionHistoryItem']],
                    'summary' => ['$ref' => '#/components/schemas/ClientWorkspaceSummary'],
                    'overview' => ['$ref' => '#/components/schemas/ClientWorkspaceOverview'],
                    'recentNotes' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ClientNoteSummary']],
                    'recentDocuments' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ClientDocumentSummary']],
                    'recentAudit' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ClientAuditSummary']],
                    'tabs' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ClientWorkspaceTab']],
                ],
                'required' => [
                    'client',
                    'currentDisposition',
                    'availableDispositionTransitions',
                    'dispositionHistory',
                    'summary',
                    'overview',
                    'recentNotes',
                    'recentDocuments',
                    'recentAudit',
                    'tabs',
                ],
            ],
        ],
    ],
];
