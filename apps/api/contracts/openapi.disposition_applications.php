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
        '/api/v1/clients' => [
            'get' => [
                'operationId' => 'getClients',
                'summary' => 'List clients with Sprint 7 disposition-aware statuses',
                'parameters' => [
                    ['name' => 'search', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                    ['name' => 'status', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string', 'enum' => ['lead', 'qualified', 'applied', 'active', 'inactive']]],
                    ['name' => 'sort', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string', 'enum' => ['display_name', 'created_at', 'updated_at', 'last_activity_at']]],
                    ['name' => 'direction', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string', 'enum' => ['asc', 'desc']]],
                    ['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer']],
                    ['name' => 'perPage', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer']],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Client list response',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ClientListEnvelope']]],
                    ],
                ],
            ],
            'post' => [
                'operationId' => 'postClients',
                'summary' => 'Create client record with server-owned initial lifecycle state',
                'requestBody' => [
                    'required' => true,
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/CreateClientRequest']]],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Created client response',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ClientEnvelope']]],
                    ],
                ],
            ],
        ],
        '/api/v1/clients/{clientId}/disposition-transitions' => [
            'post' => [
                'operationId' => 'postClientDispositionTransitions',
                'summary' => 'Attempt a governed client disposition transition',
                'parameters' => [
                    ['name' => 'clientId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/DispositionTransitionRequest']]],
                ],
                'responses' => [
                    '201' => ['description' => 'Successful transition response', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/DispositionTransitionEnvelope']]]],
                    '409' => ['description' => 'Warning acknowledgement required', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/DispositionTransitionEnvelope']]]],
                    '422' => ['description' => 'Blocked transition response', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/DispositionTransitionEnvelope']]]],
                ],
            ],
        ],
        '/api/v1/clients/{clientId}/applications' => [
            'get' => [
                'operationId' => 'getClientApplications',
                'summary' => 'List applications for a client workspace',
                'parameters' => [
                    ['name' => 'clientId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'responses' => [
                    '200' => ['description' => 'Applications list response', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ClientApplicationsListEnvelope']]]],
                ],
            ],
            'post' => [
                'operationId' => 'postClientApplications',
                'summary' => 'Create a client-linked application',
                'parameters' => [
                    ['name' => 'clientId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/CreateApplicationRequest']]],
                ],
                'responses' => [
                    '201' => ['description' => 'Application detail response', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ApplicationDetailEnvelope']]]],
                ],
            ],
        ],
        '/api/v1/clients/{clientId}/applications/{applicationId}' => [
            'get' => [
                'operationId' => 'getClientApplication',
                'summary' => 'Return one client application detail response',
                'parameters' => [
                    ['name' => 'clientId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                    ['name' => 'applicationId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'responses' => [
                    '200' => ['description' => 'Application detail response', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ApplicationDetailEnvelope']]]],
                ],
            ],
        ],
        '/api/v1/clients/{clientId}/applications/{applicationId}/status-transitions' => [
            'post' => [
                'operationId' => 'postClientApplicationStatusTransitions',
                'summary' => 'Attempt a governed application status transition',
                'parameters' => [
                    ['name' => 'clientId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                    ['name' => 'applicationId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/TransitionApplicationStatusRequest']]],
                ],
                'responses' => [
                    '200' => ['description' => 'Application transition response', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ApplicationTransitionEnvelope']]]],
                    '422' => ['description' => 'Blocked application transition response', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ApplicationTransitionEnvelope']]]],
                ],
            ],
        ],
    ],
    'components' => [
        'schemas' => [
            'ClientDetail' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'displayName' => ['type' => 'string'],
                    'firstName' => ['type' => ['string', 'null']],
                    'lastName' => ['type' => ['string', 'null']],
                    'companyName' => ['type' => ['string', 'null']],
                    'status' => ['type' => 'string', 'enum' => ['lead', 'qualified', 'applied', 'active', 'inactive']],
                    'primaryEmail' => ['type' => ['string', 'null']],
                    'primaryPhone' => ['type' => ['string', 'null']],
                    'preferredContactChannel' => ['type' => ['string', 'null'], 'enum' => ['email', 'sms', 'phone', null]],
                    'dateOfBirth' => ['type' => ['string', 'null'], 'format' => 'date'],
                    'ownerUserId' => ['type' => ['string', 'null']],
                    'ownerDisplayName' => ['type' => ['string', 'null']],
                    'address' => ['oneOf' => [['$ref' => '#/components/schemas/ClientAddressSummary'], ['type' => 'null']]],
                    'createdAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                    'updatedAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                ],
                'required' => ['id', 'displayName', 'firstName', 'lastName', 'companyName', 'status', 'primaryEmail', 'primaryPhone', 'preferredContactChannel', 'dateOfBirth', 'ownerUserId', 'ownerDisplayName', 'address', 'createdAt', 'updatedAt'],
            ],
            'ClientListItem' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'displayName' => ['type' => 'string'],
                    'status' => ['type' => 'string', 'enum' => ['lead', 'qualified', 'applied', 'active', 'inactive']],
                    'primaryEmail' => ['type' => ['string', 'null']],
                    'primaryPhone' => ['type' => ['string', 'null']],
                    'city' => ['type' => ['string', 'null']],
                    'stateCode' => ['type' => ['string', 'null']],
                    'ownerDisplayName' => ['type' => ['string', 'null']],
                    'notesCount' => ['type' => 'integer'],
                    'documentsCount' => ['type' => 'integer'],
                    'lastActivityAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                    'createdAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                    'updatedAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                ],
                'required' => ['id', 'displayName', 'status', 'primaryEmail', 'primaryPhone', 'city', 'stateCode', 'ownerDisplayName', 'notesCount', 'documentsCount', 'lastActivityAt', 'createdAt', 'updatedAt'],
            ],
            'ClientWorkspaceSummary' => [
                'type' => 'object',
                'properties' => [
                    'notesCount' => ['type' => 'integer'],
                    'documentsCount' => ['type' => 'integer'],
                    'eventsCount' => ['type' => 'integer'],
                    'applicationsCount' => ['type' => 'integer'],
                    'lastActivityAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                ],
                'required' => ['notesCount', 'documentsCount', 'eventsCount', 'applicationsCount', 'lastActivityAt'],
            ],
            'DispositionProjection' => [
                'type' => 'object',
                'properties' => [
                    'code' => ['type' => 'string'],
                    'label' => ['type' => 'string'],
                    'tone' => ['type' => 'string', 'enum' => ['neutral', 'success', 'warning', 'danger', 'info']],
                    'isTerminal' => ['type' => 'boolean'],
                    'changedAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
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
                    'occurredAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
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
            'DispositionTransitionEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['$ref' => '#/components/schemas/DispositionTransitionResponse'],
                    'meta' => ['$ref' => '#/components/schemas/ResponseMeta'],
                ],
                'required' => ['data', 'meta'],
            ],
            'ClientWorkspaceResponse' => [
                'type' => 'object',
                'properties' => [
                    'client' => ['$ref' => '#/components/schemas/ClientDetail'],
                    'currentDisposition' => ['$ref' => '#/components/schemas/DispositionProjection'],
                    'availableDispositionTransitions' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/DispositionTransitionOption']],
                    'dispositionHistory' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/DispositionHistoryItem']],
                    'summary' => ['$ref' => '#/components/schemas/ClientWorkspaceSummary'],
                    'recentNotes' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ClientNoteSummary']],
                    'recentDocuments' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ClientDocumentSummary']],
                    'recentAudit' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ClientAuditSummary']],
                    'tabs' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ClientWorkspaceTab']],
                ],
                'required' => ['client', 'currentDisposition', 'availableDispositionTransitions', 'dispositionHistory', 'summary', 'recentNotes', 'recentDocuments', 'recentAudit', 'tabs'],
            ],
            'CreateClientRequest' => [
                'type' => 'object',
                'properties' => [
                    'displayName' => ['type' => 'string'],
                    'firstName' => ['type' => ['string', 'null']],
                    'lastName' => ['type' => ['string', 'null']],
                    'companyName' => ['type' => ['string', 'null']],
                    'primaryEmail' => ['type' => ['string', 'null'], 'format' => 'email'],
                    'primaryPhone' => ['type' => ['string', 'null']],
                    'preferredContactChannel' => ['type' => ['string', 'null'], 'enum' => ['email', 'sms', 'phone', null]],
                    'dateOfBirth' => ['type' => ['string', 'null'], 'format' => 'date'],
                    'ownerUserId' => ['type' => ['string', 'null']],
                    'addressLine1' => ['type' => ['string', 'null']],
                    'addressLine2' => ['type' => ['string', 'null']],
                    'city' => ['type' => ['string', 'null']],
                    'stateCode' => ['type' => ['string', 'null']],
                    'postalCode' => ['type' => ['string', 'null']],
                ],
                'required' => ['displayName'],
            ],
            'UpdateClientRequest' => [
                'type' => 'object',
                'properties' => [
                    'displayName' => ['type' => 'string'],
                    'firstName' => ['type' => ['string', 'null']],
                    'lastName' => ['type' => ['string', 'null']],
                    'companyName' => ['type' => ['string', 'null']],
                    'primaryEmail' => ['type' => ['string', 'null'], 'format' => 'email'],
                    'primaryPhone' => ['type' => ['string', 'null']],
                    'preferredContactChannel' => ['type' => ['string', 'null'], 'enum' => ['email', 'sms', 'phone', null]],
                    'dateOfBirth' => ['type' => ['string', 'null'], 'format' => 'date'],
                    'ownerUserId' => ['type' => ['string', 'null']],
                    'addressLine1' => ['type' => ['string', 'null']],
                    'addressLine2' => ['type' => ['string', 'null']],
                    'city' => ['type' => ['string', 'null']],
                    'stateCode' => ['type' => ['string', 'null']],
                    'postalCode' => ['type' => ['string', 'null']],
                ],
                'required' => ['displayName'],
            ],
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
                    'changedAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                ],
                'required' => ['code', 'label', 'tone', 'changedAt'],
            ],
            'ApplicationRuleSummary' => [
                'type' => 'object',
                'properties' => [
                    'infoCount' => ['type' => 'integer'],
                    'warningCount' => ['type' => 'integer'],
                    'blockingCount' => ['type' => 'integer'],
                    'lastAppliedAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
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
                    'createdAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                    'updatedAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                ],
                'required' => ['id', 'applicationNumber', 'productType', 'ownerDisplayName', 'currentStatus', 'ruleSummary', 'createdAt', 'updatedAt'],
            ],
            'ClientApplicationsListResponse' => [
                'type' => 'object',
                'properties' => [
                    'items' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ApplicationSummary']],
                    'meta' => ['type' => 'object', 'properties' => ['total' => ['type' => 'integer']], 'required' => ['total']],
                ],
                'required' => ['items', 'meta'],
            ],
            'ClientApplicationsListEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['$ref' => '#/components/schemas/ClientApplicationsListResponse'],
                    'meta' => ['$ref' => '#/components/schemas/ResponseMeta'],
                ],
                'required' => ['data', 'meta'],
            ],
            'ApplicationStatusHistoryItem' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'fromStatus' => ['type' => ['string', 'null']],
                    'toStatus' => ['type' => 'string'],
                    'reason' => ['type' => ['string', 'null']],
                    'occurredAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
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
                    'appliedAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
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
                    'createdAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                    'updatedAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                    'externalReference' => ['type' => ['string', 'null']],
                    'amountRequested' => ['type' => ['string', 'null']],
                    'submittedAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
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
            'ApplicationDetailEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['$ref' => '#/components/schemas/ApplicationDetailResponse'],
                    'meta' => ['$ref' => '#/components/schemas/ResponseMeta'],
                ],
                'required' => ['data', 'meta'],
            ],
            'CreateApplicationRequest' => [
                'type' => 'object',
                'properties' => [
                    'productType' => ['type' => 'string'],
                    'ownerUserId' => ['type' => ['string', 'null']],
                    'externalReference' => ['type' => ['string', 'null']],
                    'amountRequested' => ['type' => ['number', 'null']],
                    'submittedAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                    'metadata' => ['type' => ['object', 'null']],
                ],
                'required' => ['productType'],
            ],
            'TransitionApplicationStatusRequest' => [
                'type' => 'object',
                'properties' => [
                    'targetStatus' => ['type' => 'string', 'enum' => ['draft', 'submitted', 'in_review', 'approved', 'declined', 'withdrawn']],
                    'submittedAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
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
            'ApplicationTransitionEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['$ref' => '#/components/schemas/ApplicationTransitionResponse'],
                    'meta' => ['$ref' => '#/components/schemas/ResponseMeta'],
                ],
                'required' => ['data', 'meta'],
            ],
        ],
    ],
];
