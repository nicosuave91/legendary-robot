<?php

declare(strict_types=1);

return [
    'paths' => [
        '/api/v1/calendar/day' => [
            'get' => [
                'operationId' => 'getCalendarDay',
                'summary' => 'Return selected-day calendar data with event and task summaries',
                'parameters' => [
                    [
                        'name' => 'date',
                        'in' => 'query',
                        'required' => true,
                        'schema' => [
                            'type' => 'string'
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Calendar day response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/CalendarDayEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/events' => [
            'get' => [
                'operationId' => 'getEvents',
                'summary' => 'Return date-range event summaries',
                'parameters' => [
                    [
                        'name' => 'startDate',
                        'in' => 'query',
                        'required' => true,
                        'schema' => [
                            'type' => 'string'
                        ]
                    ],
                    [
                        'name' => 'endDate',
                        'in' => 'query',
                        'required' => true,
                        'schema' => [
                            'type' => 'string'
                        ]
                    ],
                    [
                        'name' => 'clientId',
                        'in' => 'query',
                        'required' => false,
                        'schema' => [
                            'type' => 'string'
                        ]
                    ],
                    [
                        'name' => 'ownerUserId',
                        'in' => 'query',
                        'required' => false,
                        'schema' => [
                            'type' => 'string'
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Event list response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/EventListEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'post' => [
                'operationId' => 'postEvents',
                'summary' => 'Create an event with optional tasks',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/CreateEventRequest'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Created event detail response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/EventDetailEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/events/{eventId}' => [
            'get' => [
                'operationId' => 'getEvent',
                'summary' => 'Return event detail with tasks and task history',
                'parameters' => [
                    [
                        'name' => 'eventId',
                        'in' => 'path',
                        'required' => true,
                        'schema' => [
                            'type' => 'string'
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Event detail response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/EventDetailEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'patch' => [
                'operationId' => 'patchEvent',
                'summary' => 'Update governed event metadata',
                'parameters' => [
                    [
                        'name' => 'eventId',
                        'in' => 'path',
                        'required' => true,
                        'schema' => [
                            'type' => 'string'
                        ]
                    ]
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/UpdateEventRequest'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Updated event detail response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/EventDetailEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/tasks/{taskId}/status' => [
            'patch' => [
                'operationId' => 'patchTaskStatus',
                'summary' => 'Update task status and append durable history',
                'parameters' => [
                    [
                        'name' => 'taskId',
                        'in' => 'path',
                        'required' => true,
                        'schema' => [
                            'type' => 'string'
                        ]
                    ]
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/UpdateTaskStatusRequest'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Task status transition response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/TaskStatusTransitionEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/clients/{clientId}/events' => [
            'get' => [
                'operationId' => 'getClientEvents',
                'summary' => 'Return canonical client-linked events',
                'parameters' => [
                    [
                        'name' => 'clientId',
                        'in' => 'path',
                        'required' => true,
                        'schema' => [
                            'type' => 'string'
                        ]
                    ],
                    [
                        'name' => 'startDate',
                        'in' => 'query',
                        'required' => false,
                        'schema' => [
                            'type' => 'string'
                        ]
                    ],
                    [
                        'name' => 'endDate',
                        'in' => 'query',
                        'required' => false,
                        'schema' => [
                            'type' => 'string'
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Client-linked event list response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ClientEventListEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'components' => [
        'schemas' => [
            'CalendarSummaryCounts' => [
                'type' => 'object',
                'properties' => [
                    'eventCount' => [
                        'type' => 'integer'
                    ],
                    'openTaskCount' => [
                        'type' => 'integer'
                    ],
                    'completedTaskCount' => [
                        'type' => 'integer'
                    ],
                    'blockedTaskCount' => [
                        'type' => 'integer'
                    ],
                    'skippedTaskCount' => [
                        'type' => 'integer'
                    ]
                ],
                'required' => [
                    'eventCount',
                    'openTaskCount',
                    'completedTaskCount',
                    'blockedTaskCount',
                    'skippedTaskCount'
                ]
            ],
            'CalendarLinkedRecordSummary' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string'
                    ],
                    'displayName' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'id',
                    'displayName'
                ]
            ],
            'EventTaskSummary' => [
                'type' => 'object',
                'properties' => [
                    'total' => [
                        'type' => 'integer'
                    ],
                    'open' => [
                        'type' => 'integer'
                    ],
                    'completed' => [
                        'type' => 'integer'
                    ],
                    'blocked' => [
                        'type' => 'integer'
                    ],
                    'skipped' => [
                        'type' => 'integer'
                    ]
                ],
                'required' => [
                    'total',
                    'open',
                    'completed',
                    'blocked',
                    'skipped'
                ]
            ],
            'CalendarEventSummary' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string'
                    ],
                    'title' => [
                        'type' => 'string'
                    ],
                    'description' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'eventType' => [
                        'type' => 'string',
                        'enum' => [
                            'appointment',
                            'follow_up',
                            'document_review',
                            'call',
                            'deadline',
                            'task_batch'
                        ]
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => [
                            'scheduled',
                            'completed',
                            'cancelled'
                        ]
                    ],
                    'startsAt' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'endsAt' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'isAllDay' => [
                        'type' => 'boolean'
                    ],
                    'location' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'client' => [
                        '$ref' => '#/components/schemas/CalendarLinkedRecordSummary',
                        'nullable' => true
                    ],
                    'owner' => [
                        '$ref' => '#/components/schemas/CalendarLinkedRecordSummary',
                        'nullable' => true
                    ],
                    'taskSummary' => [
                        '$ref' => '#/components/schemas/EventTaskSummary'
                    ]
                ],
                'required' => [
                    'id',
                    'title',
                    'description',
                    'eventType',
                    'status',
                    'startsAt',
                    'endsAt',
                    'isAllDay',
                    'location',
                    'client',
                    'owner',
                    'taskSummary'
                ]
            ],
            'EventTaskHistoryItem' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string'
                    ],
                    'fromStatus' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'toStatus' => [
                        'type' => 'string',
                        'enum' => [
                            'open',
                            'completed',
                            'skipped',
                            'blocked'
                        ]
                    ],
                    'reason' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'occurredAt' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'actorDisplayName' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'id',
                    'fromStatus',
                    'toStatus',
                    'reason',
                    'occurredAt',
                    'actorDisplayName'
                ]
            ],
            'EventTaskDetail' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string'
                    ],
                    'title' => [
                        'type' => 'string'
                    ],
                    'description' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => [
                            'open',
                            'completed',
                            'skipped',
                            'blocked'
                        ]
                    ],
                    'isRequired' => [
                        'type' => 'boolean'
                    ],
                    'sortOrder' => [
                        'type' => 'integer'
                    ],
                    'dueAt' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'completedAt' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'blockedReason' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'assignedUser' => [
                        '$ref' => '#/components/schemas/CalendarLinkedRecordSummary',
                        'nullable' => true
                    ],
                    'availableActions' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string'
                        ]
                    ],
                    'history' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/components/schemas/EventTaskHistoryItem'
                        ]
                    ]
                ],
                'required' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'isRequired',
                    'sortOrder',
                    'dueAt',
                    'completedAt',
                    'blockedReason',
                    'assignedUser',
                    'availableActions',
                    'history'
                ]
            ],
            'CalendarDayResponse' => [
                'type' => 'object',
                'properties' => [
                    'selectedDate' => [
                        'type' => 'string'
                    ],
                    'isToday' => [
                        'type' => 'boolean'
                    ],
                    'summary' => [
                        '$ref' => '#/components/schemas/CalendarSummaryCounts'
                    ],
                    'events' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/components/schemas/CalendarEventSummary'
                        ]
                    ]
                ],
                'required' => [
                    'selectedDate',
                    'isToday',
                    'summary',
                    'events'
                ]
            ],
            'CalendarDayEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        '$ref' => '#/components/schemas/CalendarDayResponse'
                    ],
                    'meta' => [
                        '$ref' => '#/components/schemas/ResponseMeta'
                    ]
                ],
                'required' => [
                    'data',
                    'meta'
                ]
            ],
            'EventListResponse' => [
                'type' => 'object',
                'properties' => [
                    'items' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/components/schemas/CalendarEventSummary'
                        ]
                    ],
                    'range' => [
                        'type' => 'object',
                        'properties' => [
                            'startDate' => [
                                'type' => 'string'
                            ],
                            'endDate' => [
                                'type' => 'string'
                            ]
                        ],
                        'required' => [
                            'startDate',
                            'endDate'
                        ]
                    ]
                ],
                'required' => [
                    'items',
                    'range'
                ]
            ],
            'EventListEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        '$ref' => '#/components/schemas/EventListResponse'
                    ],
                    'meta' => [
                        '$ref' => '#/components/schemas/ResponseMeta'
                    ]
                ],
                'required' => [
                    'data',
                    'meta'
                ]
            ],
            'EventDetailResponse' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string'
                    ],
                    'title' => [
                        'type' => 'string'
                    ],
                    'description' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'eventType' => [
                        'type' => 'string',
                        'enum' => [
                            'appointment',
                            'follow_up',
                            'document_review',
                            'call',
                            'deadline',
                            'task_batch'
                        ]
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => [
                            'scheduled',
                            'completed',
                            'cancelled'
                        ]
                    ],
                    'startsAt' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'endsAt' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'isAllDay' => [
                        'type' => 'boolean'
                    ],
                    'location' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'client' => [
                        '$ref' => '#/components/schemas/CalendarLinkedRecordSummary',
                        'nullable' => true
                    ],
                    'owner' => [
                        '$ref' => '#/components/schemas/CalendarLinkedRecordSummary',
                        'nullable' => true
                    ],
                    'taskSummary' => [
                        '$ref' => '#/components/schemas/EventTaskSummary'
                    ],
                    'tasks' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/components/schemas/EventTaskDetail'
                        ]
                    ]
                ],
                'required' => [
                    'id',
                    'title',
                    'description',
                    'eventType',
                    'status',
                    'startsAt',
                    'endsAt',
                    'isAllDay',
                    'location',
                    'client',
                    'owner',
                    'taskSummary',
                    'tasks'
                ]
            ],
            'EventDetailEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        '$ref' => '#/components/schemas/EventDetailResponse'
                    ],
                    'meta' => [
                        '$ref' => '#/components/schemas/ResponseMeta'
                    ]
                ],
                'required' => [
                    'data',
                    'meta'
                ]
            ],
            'CreateEventTaskRequest' => [
                'type' => 'object',
                'properties' => [
                    'title' => [
                        'type' => 'string'
                    ],
                    'description' => [
                        'type' => 'string'
                    ],
                    'assignedUserId' => [
                        'type' => 'string'
                    ],
                    'isRequired' => [
                        'type' => 'boolean'
                    ],
                    'sortOrder' => [
                        'type' => 'integer'
                    ],
                    'dueAt' => [
                        'type' => 'string'
                    ],
                    'metadata' => [
                        'type' => 'object',
                        'additionalProperties' => true
                    ]
                ],
                'required' => [
                    'title'
                ]
            ],
            'CreateEventRequest' => [
                'type' => 'object',
                'properties' => [
                    'title' => [
                        'type' => 'string'
                    ],
                    'description' => [
                        'type' => 'string'
                    ],
                    'eventType' => [
                        'type' => 'string',
                        'enum' => [
                            'appointment',
                            'follow_up',
                            'document_review',
                            'call',
                            'deadline',
                            'task_batch'
                        ]
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => [
                            'scheduled',
                            'completed',
                            'cancelled'
                        ]
                    ],
                    'startsAt' => [
                        'type' => 'string'
                    ],
                    'endsAt' => [
                        'type' => 'string'
                    ],
                    'isAllDay' => [
                        'type' => 'boolean'
                    ],
                    'location' => [
                        'type' => 'string'
                    ],
                    'clientId' => [
                        'type' => 'string'
                    ],
                    'ownerUserId' => [
                        'type' => 'string'
                    ],
                    'metadata' => [
                        'type' => 'object',
                        'additionalProperties' => true
                    ],
                    'tasks' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/components/schemas/CreateEventTaskRequest'
                        ]
                    ]
                ],
                'required' => [
                    'title',
                    'eventType',
                    'startsAt'
                ]
            ],
            'UpdateEventRequest' => [
                'type' => 'object',
                'properties' => [
                    'title' => [
                        'type' => 'string'
                    ],
                    'description' => [
                        'type' => 'string'
                    ],
                    'eventType' => [
                        'type' => 'string',
                        'enum' => [
                            'appointment',
                            'follow_up',
                            'document_review',
                            'call',
                            'deadline',
                            'task_batch'
                        ]
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => [
                            'scheduled',
                            'completed',
                            'cancelled'
                        ]
                    ],
                    'startsAt' => [
                        'type' => 'string'
                    ],
                    'endsAt' => [
                        'type' => 'string'
                    ],
                    'isAllDay' => [
                        'type' => 'boolean'
                    ],
                    'location' => [
                        'type' => 'string'
                    ],
                    'clientId' => [
                        'type' => 'string'
                    ],
                    'ownerUserId' => [
                        'type' => 'string'
                    ],
                    'metadata' => [
                        'type' => 'object',
                        'additionalProperties' => true
                    ]
                ]
            ],
            'UpdateTaskStatusRequest' => [
                'type' => 'object',
                'properties' => [
                    'targetStatus' => [
                        'type' => 'string',
                        'enum' => [
                            'open',
                            'completed',
                            'skipped',
                            'blocked'
                        ]
                    ],
                    'reason' => [
                        'type' => 'string'
                    ],
                    'blockedReason' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'targetStatus'
                ]
            ],
            'TaskStatusTransitionResponse' => [
                'type' => 'object',
                'properties' => [
                    'result' => [
                        'type' => 'string'
                    ],
                    'mutatedTaskId' => [
                        'type' => 'string'
                    ],
                    'event' => [
                        '$ref' => '#/components/schemas/EventDetailResponse'
                    ]
                ],
                'required' => [
                    'result',
                    'mutatedTaskId',
                    'event'
                ]
            ],
            'TaskStatusTransitionEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        '$ref' => '#/components/schemas/TaskStatusTransitionResponse'
                    ],
                    'meta' => [
                        '$ref' => '#/components/schemas/ResponseMeta'
                    ]
                ],
                'required' => [
                    'data',
                    'meta'
                ]
            ],
            'ClientEventListResponse' => [
                'type' => 'object',
                'properties' => [
                    'items' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/components/schemas/CalendarEventSummary'
                        ]
                    ]
                ],
                'required' => [
                    'items'
                ]
            ],
            'ClientEventListEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        '$ref' => '#/components/schemas/ClientEventListResponse'
                    ],
                    'meta' => [
                        '$ref' => '#/components/schemas/ResponseMeta'
                    ]
                ],
                'required' => [
                    'data',
                    'meta'
                ]
            ]
        ]
    ]
];
