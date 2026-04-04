<?php

declare(strict_types=1);

return [
    'paths' => [
        '/api/v1/communications/attachments/{attachmentId}/scan-status' => [
            'patch' => [
                'operationId' => 'patchCommunicationAttachmentScanStatus',
                'summary' => 'Update communication attachment security scan status',
                'parameters' => [
                    ['name' => 'attachmentId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                ],
                'requestBody' => ['required' => true, 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/UpdateCommunicationAttachmentScanStatusRequest']]]],
                'responses' => ['200' => ['description' => 'Updated attachment governance summary', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/CommunicationAttachmentGovernanceEnvelope']]]]],
            ],
        ],
        '/api/v1/clients/{clientId}/communications' => [
            'get' => [
                'operationId' => 'getClientCommunications',
                'summary' => 'Return normalized client communications timeline',
                'parameters' => [
                    ['name' => 'clientId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                    ['name' => 'channel', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string', 'enum' => ['all', 'sms', 'email', 'voice']]],
                    ['name' => 'status', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string', 'enum' => ['all', 'pending', 'failed']]],
                    ['name' => 'limit', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer']],
                ],
                'responses' => ['200' => ['description' => 'Communications timeline response', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ClientCommunicationsEnvelope']]]]],
            ],
        ],
        '/api/v1/clients/{clientId}/communications/sms' => [
            'post' => [
                'operationId' => 'postClientCommunicationsSms',
                'summary' => 'Queue outbound SMS/MMS command',
                'parameters' => [['name' => 'clientId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']]],
                'requestBody' => ['required' => true, 'content' => ['multipart/form-data' => ['schema' => ['$ref' => '#/components/schemas/SendSmsRequest']]]],
                'responses' => ['201' => ['description' => 'Queued SMS response', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/CommunicationTimelineItemEnvelope']]]]],
            ],
        ],
        '/api/v1/clients/{clientId}/communications/email' => [
            'post' => [
                'operationId' => 'postClientCommunicationsEmail',
                'summary' => 'Queue outbound email command',
                'parameters' => [['name' => 'clientId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']]],
                'requestBody' => ['required' => true, 'content' => ['multipart/form-data' => ['schema' => ['$ref' => '#/components/schemas/SendEmailRequest']]]],
                'responses' => ['201' => ['description' => 'Queued email response', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/CommunicationTimelineItemEnvelope']]]]],
            ],
        ],
        '/api/v1/clients/{clientId}/communications/call' => [
            'post' => [
                'operationId' => 'postClientCommunicationsCall',
                'summary' => 'Queue outbound call initiation',
                'parameters' => [['name' => 'clientId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']]],
                'requestBody' => ['required' => true, 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/StartCallRequest']]]],
                'responses' => ['201' => ['description' => 'Queued call response', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/CommunicationTimelineItemEnvelope']]]]],
            ],
        ],
        '/webhooks/twilio/messaging' => ['post' => ['operationId' => 'postWebhookTwilioMessaging', 'summary' => 'Receive Twilio messaging status and inbound callbacks', 'responses' => ['200' => ['description' => 'Accepted']]]],
        '/webhooks/twilio/voice' => ['post' => ['operationId' => 'postWebhookTwilioVoice', 'summary' => 'Receive Twilio voice lifecycle callbacks', 'responses' => ['200' => ['description' => 'Accepted']]]],
        '/webhooks/sendgrid/inbound' => ['post' => ['operationId' => 'postWebhookSendgridInbound', 'summary' => 'Receive SendGrid inbound parse replies', 'responses' => ['200' => ['description' => 'Accepted']]]],
        '/webhooks/sendgrid/events' => ['post' => ['operationId' => 'postWebhookSendgridEvents', 'summary' => 'Receive SendGrid event webhook delivery callbacks', 'responses' => ['200' => ['description' => 'Accepted']]]],
    ],
    'components' => ['schemas' => [
        'UpdateCommunicationAttachmentScanStatusRequest' => ['type' => 'object', 'properties' => ['status' => ['type' => 'string', 'enum' => ['pending', 'clean', 'rejected', 'quarantined']], 'engine' => ['type' => 'string', 'nullable' => true], 'detail' => ['type' => 'string', 'nullable' => true], 'quarantineReason' => ['type' => 'string', 'nullable' => true]], 'required' => ['status']],
        'CommunicationAttachmentGovernanceSummary' => ['type' => 'object', 'properties' => ['id' => ['type' => 'string'], 'originalFilename' => ['type' => 'string'], 'mimeType' => ['type' => 'string'], 'sizeBytes' => ['type' => 'integer'], 'scanStatus' => ['type' => 'string'], 'scanRequestedAt' => ['type' => 'string', 'nullable' => true], 'scannedAt' => ['type' => 'string', 'nullable' => true], 'scanEngine' => ['type' => 'string', 'nullable' => true], 'scanResultDetail' => ['type' => 'string', 'nullable' => true], 'quarantineReason' => ['type' => 'string', 'nullable' => true]], 'required' => ['id','originalFilename','mimeType','sizeBytes','scanStatus','scanRequestedAt','scannedAt','scanEngine','scanResultDetail','quarantineReason']],
        'CommunicationAttachmentGovernanceEnvelope' => ['type' => 'object', 'properties' => ['data' => ['$ref' => '#/components/schemas/CommunicationAttachmentGovernanceSummary'], 'meta' => ['$ref' => '#/components/schemas/ResponseMeta']], 'required' => ['data','meta']],
        'SendSmsRequest' => ['type' => 'object', 'properties' => ['body' => ['type' => 'string', 'nullable' => true], 'toPhone' => ['type' => 'string', 'nullable' => true], 'idempotencyKey' => ['type' => 'string', 'nullable' => true], 'retryOfMessageId' => ['type' => 'string', 'nullable' => true], 'attachments' => ['type' => 'array', 'items' => ['type' => 'string', 'format' => 'binary']]]],
        'SendEmailRequest' => ['type' => 'object', 'properties' => ['to' => ['type' => 'array', 'items' => ['type' => 'string']], 'cc' => ['type' => 'array', 'items' => ['type' => 'string']], 'bcc' => ['type' => 'array', 'items' => ['type' => 'string']], 'subject' => ['type' => 'string'], 'bodyText' => ['type' => 'string', 'nullable' => true], 'bodyHtml' => ['type' => 'string', 'nullable' => true], 'idempotencyKey' => ['type' => 'string', 'nullable' => true], 'retryOfMessageId' => ['type' => 'string', 'nullable' => true], 'attachments' => ['type' => 'array', 'items' => ['type' => 'string', 'format' => 'binary']]], 'required' => ['to', 'subject']],
        'StartCallRequest' => ['type' => 'object', 'properties' => ['toPhone' => ['type' => 'string', 'nullable' => true], 'purposeNote' => ['type' => 'string', 'nullable' => true], 'idempotencyKey' => ['type' => 'string', 'nullable' => true], 'retryOfCallLogId' => ['type' => 'string', 'nullable' => true]]],
        'CommunicationAttachmentSummary' => ['type' => 'object', 'properties' => ['id' => ['type' => 'string'], 'originalFilename' => ['type' => 'string'], 'mimeType' => ['type' => 'string'], 'sizeBytes' => ['type' => 'integer'], 'provenance' => ['type' => 'string'], 'storageReference' => ['type' => 'string'], 'scanStatus' => ['type' => 'string']], 'required' => ['id','originalFilename','mimeType','sizeBytes','provenance','storageReference','scanStatus']],
        'DeliveryStatusProjection' => ['type' => 'object', 'properties' => ['lifecycle' => ['type' => 'string'], 'providerStatus' => ['type' => 'string', 'nullable' => true], 'displayLabel' => ['type' => 'string'], 'tone' => ['type' => 'string', 'enum' => ['neutral', 'success', 'warning', 'danger']], 'isTerminal' => ['type' => 'boolean'], 'updatedAt' => ['type' => 'string', 'nullable' => true], 'reasonCode' => ['type' => 'string', 'nullable' => true], 'reasonMessage' => ['type' => 'string', 'nullable' => true], 'source' => ['type' => 'string', 'enum' => ['internal', 'provider_submit', 'provider_callback']]], 'required' => ['lifecycle','providerStatus','displayLabel','tone','isTerminal','updatedAt','reasonCode','reasonMessage','source']],
        'CommunicationTimelineCounterpart' => ['type' => 'object', 'properties' => ['name' => ['type' => 'string', 'nullable' => true], 'address' => ['type' => 'string', 'nullable' => true]], 'required' => ['name','address']],
        'CommunicationTimelineContent' => ['type' => 'object', 'properties' => ['subject' => ['type' => 'string', 'nullable' => true], 'bodyText' => ['type' => 'string', 'nullable' => true], 'preview' => ['type' => 'string', 'nullable' => true]], 'required' => ['subject','bodyText','preview']],
        'CommunicationTimelineEvidence' => ['type' => 'object', 'properties' => ['source' => ['type' => 'string', 'enum' => ['internal', 'provider_submit', 'provider_callback']], 'lastEventAt' => ['type' => 'string', 'nullable' => true], 'lastEventType' => ['type' => 'string', 'nullable' => true], 'eventCount' => ['type' => 'integer']], 'required' => ['source','lastEventAt','lastEventType','eventCount']],
        'CommunicationTimelineCall' => ['type' => 'object', 'properties' => ['durationSeconds' => ['type' => 'integer', 'nullable' => true]], 'required' => ['durationSeconds']],
        'CommunicationTimelineActions' => ['type' => 'object', 'properties' => ['canRetry' => ['type' => 'boolean']], 'required' => ['canRetry']],
        'CommunicationTimelineItem' => ['type' => 'object', 'properties' => ['id' => ['type' => 'string'], 'kind' => ['type' => 'string', 'enum' => ['message', 'call', 'system_event']], 'channel' => ['type' => 'string', 'enum' => ['sms', 'mms', 'email', 'voice']], 'direction' => ['type' => 'string', 'enum' => ['inbound', 'outbound', 'system']], 'occurredAt' => ['type' => 'string', 'nullable' => true], 'counterpart' => ['$ref' => '#/components/schemas/CommunicationTimelineCounterpart'], 'content' => ['$ref' => '#/components/schemas/CommunicationTimelineContent'], 'attachments' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/CommunicationAttachmentSummary']], 'status' => ['$ref' => '#/components/schemas/DeliveryStatusProjection'], 'evidence' => ['$ref' => '#/components/schemas/CommunicationTimelineEvidence'], 'call' => ['oneOf' => [['$ref' => '#/components/schemas/CommunicationTimelineCall'], ['type' => 'null']]], 'actions' => ['$ref' => '#/components/schemas/CommunicationTimelineActions']], 'required' => ['id','kind','channel','direction','occurredAt','counterpart','content','attachments','status','evidence','call','actions']],
        'ClientCommunicationsResponse' => ['type' => 'object', 'properties' => ['clientId' => ['type' => 'string'], 'items' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/CommunicationTimelineItem']], 'paging' => ['type' => 'object', 'properties' => ['nextCursor' => ['type' => 'string', 'nullable' => true], 'hasMore' => ['type' => 'boolean']], 'required' => ['nextCursor','hasMore']], 'filters' => ['type' => 'object', 'properties' => ['channel' => ['type' => 'string'], 'status' => ['type' => 'string']], 'required' => ['channel','status']], 'refresh' => ['type' => 'object', 'properties' => ['hasPendingRecentItems' => ['type' => 'boolean'], 'recommendedPollSeconds' => ['type' => 'integer', 'nullable' => true]], 'required' => ['hasPendingRecentItems','recommendedPollSeconds']]], 'required' => ['clientId','items','paging','filters','refresh']],
        'ClientCommunicationsEnvelope' => ['type' => 'object', 'properties' => ['data' => ['$ref' => '#/components/schemas/ClientCommunicationsResponse'], 'meta' => ['$ref' => '#/components/schemas/ResponseMeta']], 'required' => ['data','meta']],
        'CommunicationTimelineItemEnvelope' => ['type' => 'object', 'properties' => ['data' => ['$ref' => '#/components/schemas/CommunicationTimelineItem'], 'meta' => ['$ref' => '#/components/schemas/ResponseMeta']], 'required' => ['data','meta']],
    ]],
];
