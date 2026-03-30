<?php

declare(strict_types=1);

return [
    'openapi' => '3.1.0',
    'info' => [
        'title' => 'Snowball CRM API',
        'version' => '4.0.0',
        'description' => 'Sprint 4 homepage analytics and client workspace contracts.'
    ],
    'paths' => [
        '/api/v1/auth/sign-in' => [
            'post' => [
                'operationId' => 'postAuthSignIn',
                'summary' => 'Authenticate and return auth bootstrap context',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/SignInRequest'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Authentication response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/AuthContextEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/auth/sign-out' => [
            'post' => [
                'operationId' => 'postAuthSignOut',
                'summary' => 'Invalidate the current session',
                'responses' => [
                    '200' => [
                        'description' => 'Sign out response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/MessageResponse'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/auth/me' => [
            'get' => [
                'operationId' => 'getAuthMe',
                'summary' => 'Return current authenticated session context',
                'responses' => [
                    '200' => [
                        'description' => 'Authenticated context',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/AuthContextEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/onboarding/state' => [
            'get' => [
                'operationId' => 'getOnboardingState',
                'summary' => 'Return current onboarding state and profile snapshot',
                'responses' => [
                    '200' => [
                        'description' => 'Onboarding state response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/OnboardingStateEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/onboarding/profile-confirmation' => [
            'patch' => [
                'operationId' => 'patchOnboardingProfileConfirmation',
                'summary' => 'Confirm the current user profile during onboarding',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/ProfileConfirmationRequest'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Updated onboarding state response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/OnboardingStateEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/onboarding/industry-selection' => [
            'patch' => [
                'operationId' => 'patchOnboardingIndustrySelection',
                'summary' => 'Persist the selected industry for onboarding',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/IndustrySelectionRequest'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Updated onboarding state response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/OnboardingStateEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/onboarding/complete' => [
            'post' => [
                'operationId' => 'postOnboardingComplete',
                'summary' => 'Complete the one-time onboarding flow',
                'responses' => [
                    '200' => [
                        'description' => 'Updated onboarding state response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/OnboardingStateEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/settings/profile' => [
            'get' => [
                'operationId' => 'getSettingsProfile',
                'summary' => 'Return current profile settings',
                'responses' => [
                    '200' => [
                        'description' => 'Profile response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ProfileEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'patch' => [
                'operationId' => 'patchSettingsProfile',
                'summary' => 'Update current profile settings',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/UpdateProfileRequest'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Updated profile response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ProfileEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/settings/accounts' => [
            'get' => [
                'operationId' => 'getSettingsAccounts',
                'summary' => 'List tenant accounts visible to the requester',
                'responses' => [
                    '200' => [
                        'description' => 'Accounts response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/AccountListEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'post' => [
                'operationId' => 'postSettingsAccounts',
                'summary' => 'Create a tenant account that defaults to onboarding required',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/CreateAccountRequest'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Created account response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/AccountEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/settings/accounts/{userId}' => [
            'patch' => [
                'operationId' => 'patchSettingsAccount',
                'summary' => 'Update a tenant account',
                'parameters' => [
                    [
                        'name' => 'userId',
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
                                '$ref' => '#/components/schemas/UpdateAccountRequest'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Updated account response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/AccountEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'delete' => [
                'operationId' => 'deleteSettingsAccount',
                'summary' => 'Decommission a tenant account',
                'parameters' => [
                    [
                        'name' => 'userId',
                        'in' => 'path',
                        'required' => true,
                        'schema' => [
                            'type' => 'string'
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Decommissioned account response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/MessageResponse'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/settings/theme' => [
            'get' => [
                'operationId' => 'getSettingsTheme',
                'summary' => 'Return current tenant theme settings',
                'responses' => [
                    '200' => [
                        'description' => 'Theme response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ThemeEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'patch' => [
                'operationId' => 'patchSettingsTheme',
                'summary' => 'Update current tenant theme settings',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/ThemeSummary'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Updated theme response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ThemeEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/settings/industry-configurations' => [
            'get' => [
                'operationId' => 'getSettingsIndustryConfigurations',
                'summary' => 'List tenant industry configuration versions',
                'responses' => [
                    '200' => [
                        'description' => 'Industry configurations response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/IndustryConfigurationListEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'post' => [
                'operationId' => 'postSettingsIndustryConfigurations',
                'summary' => 'Create a tenant industry configuration version',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/CreateIndustryConfigurationRequest'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Created industry configuration response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/IndustryConfigurationEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/dashboard/summary' => [
            'get' => [
                'operationId' => 'getDashboardSummary',
                'summary' => 'Return homepage hero data and KPI summaries',
                'responses' => [
                    '200' => [
                        'description' => 'Dashboard summary response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/DashboardSummaryEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/dashboard/production' => [
            'get' => [
                'operationId' => 'getDashboardProduction',
                'summary' => 'Return chart-ready homepage production metrics',
                'parameters' => [
                    [
                        'name' => 'window',
                        'in' => 'query',
                        'required' => false,
                        'schema' => [
                            'type' => 'string',
                            'enum' => [
                                '7d',
                                '30d',
                                '90d'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Dashboard production response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/DashboardProductionEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/clients' => [
            'get' => [
                'operationId' => 'getClients',
                'summary' => 'List visible clients with filtering, sorting, pagination, and search',
                'parameters' => [
                    [
                        'name' => 'search',
                        'in' => 'query',
                        'required' => false,
                        'schema' => [
                            'type' => 'string'
                        ]
                    ],
                    [
                        'name' => 'status',
                        'in' => 'query',
                        'required' => false,
                        'schema' => [
                            'type' => 'string',
                            'enum' => [
                                'lead',
                                'active',
                                'inactive'
                            ]
                        ]
                    ],
                    [
                        'name' => 'sort',
                        'in' => 'query',
                        'required' => false,
                        'schema' => [
                            'type' => 'string',
                            'enum' => [
                                'display_name',
                                'created_at',
                                'updated_at',
                                'last_activity_at'
                            ]
                        ]
                    ],
                    [
                        'name' => 'direction',
                        'in' => 'query',
                        'required' => false,
                        'schema' => [
                            'type' => 'string',
                            'enum' => [
                                'asc',
                                'desc'
                            ]
                        ]
                    ],
                    [
                        'name' => 'page',
                        'in' => 'query',
                        'required' => false,
                        'schema' => [
                            'type' => 'integer'
                        ]
                    ],
                    [
                        'name' => 'perPage',
                        'in' => 'query',
                        'required' => false,
                        'schema' => [
                            'type' => 'integer'
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Client list response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ClientListEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'post' => [
                'operationId' => 'postClients',
                'summary' => 'Create a client record',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/CreateOrUpdateClientRequest'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Created client response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ClientEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/clients/{clientId}' => [
            'get' => [
                'operationId' => 'getClient',
                'summary' => 'Return client workspace summary',
                'parameters' => [
                    [
                        'name' => 'clientId',
                        'in' => 'path',
                        'required' => true,
                        'schema' => [
                            'type' => 'string'
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Client workspace response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ClientWorkspaceEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'patch' => [
                'operationId' => 'patchClient',
                'summary' => 'Update editable client profile fields',
                'parameters' => [
                    [
                        'name' => 'clientId',
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
                                '$ref' => '#/components/schemas/CreateOrUpdateClientRequest'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Updated client response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ClientEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/clients/{clientId}/notes' => [
            'post' => [
                'operationId' => 'postClientNotes',
                'summary' => 'Create a user-authored client note',
                'parameters' => [
                    [
                        'name' => 'clientId',
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
                                '$ref' => '#/components/schemas/CreateClientNoteRequest'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Created client note response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ClientNoteEnvelope'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/api/v1/clients/{clientId}/documents' => [
            'post' => [
                'operationId' => 'postClientDocuments',
                'summary' => 'Upload and attach client document metadata',
                'parameters' => [
                    [
                        'name' => 'clientId',
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
                        'multipart/form-data' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/CreateClientDocumentRequest'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Created client document response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ClientDocumentEnvelope'
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
            'MessageResponse' => [
                'type' => 'object',
                'properties' => [
                    'message' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'message'
                ]
            ],
            'ResponseMeta' => [
                'type' => 'object',
                'properties' => [
                    'apiVersion' => [
                        'type' => 'string',
                        'enum' => [
                            'v1'
                        ]
                    ],
                    'correlationId' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'apiVersion',
                    'correlationId'
                ]
            ],
            'SignInRequest' => [
                'type' => 'object',
                'properties' => [
                    'email' => [
                        'type' => 'string'
                    ],
                    'password' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'email',
                    'password'
                ]
            ],
            'AuthContextEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        '$ref' => '#/components/schemas/AuthContextResponse'
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
            'AuthContextResponse' => [
                'type' => 'object',
                'properties' => [
                    'isAuthenticated' => [
                        'type' => 'boolean'
                    ],
                    'user' => [
                        '$ref' => '#/components/schemas/UserSummary'
                    ],
                    'tenant' => [
                        '$ref' => '#/components/schemas/TenantSummary'
                    ],
                    'roles' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string'
                        ]
                    ],
                    'permissions' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string'
                        ]
                    ],
                    'onboardingState' => [
                        'type' => 'string',
                        'enum' => [
                            'not_applicable',
                            'required',
                            'in_progress',
                            'completed'
                        ]
                    ],
                    'onboardingStep' => [
                        'type' => 'string',
                        'enum' => [
                            'profile_confirmation',
                            'industry_selection',
                            'completion'
                        ],
                        'nullable' => true
                    ],
                    'theme' => [
                        '$ref' => '#/components/schemas/ThemeSummary'
                    ],
                    'landingRoute' => [
                        'type' => 'string'
                    ],
                    'selectedIndustry' => [
                        'type' => 'string',
                        'enum' => [
                            'Legal',
                            'Medical',
                            'Mortgage'
                        ],
                        'nullable' => true
                    ],
                    'selectedIndustryConfigVersion' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'capabilities' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string'
                        ]
                    ]
                ],
                'required' => [
                    'isAuthenticated',
                    'user',
                    'tenant',
                    'roles',
                    'permissions',
                    'onboardingState',
                    'onboardingStep',
                    'theme',
                    'landingRoute',
                    'selectedIndustry',
                    'selectedIndustryConfigVersion',
                    'capabilities'
                ]
            ],
            'UserSummary' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string'
                    ],
                    'email' => [
                        'type' => 'string'
                    ],
                    'displayName' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'id',
                    'email',
                    'displayName'
                ]
            ],
            'TenantSummary' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string'
                    ],
                    'name' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'id',
                    'name'
                ]
            ],
            'ThemeSummary' => [
                'type' => 'object',
                'properties' => [
                    'primary' => [
                        'type' => 'string'
                    ],
                    'secondary' => [
                        'type' => 'string'
                    ],
                    'tertiary' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'primary',
                    'secondary',
                    'tertiary'
                ]
            ],
            'ThemeEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        '$ref' => '#/components/schemas/ThemeSummary'
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
            'OnboardingStateEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        '$ref' => '#/components/schemas/OnboardingStateResponse'
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
            'OnboardingStateResponse' => [
                'type' => 'object',
                'properties' => [
                    'state' => [
                        'type' => 'string',
                        'enum' => [
                            'not_applicable',
                            'required',
                            'in_progress',
                            'completed'
                        ]
                    ],
                    'currentStep' => [
                        'type' => 'string',
                        'enum' => [
                            'profile_confirmation',
                            'industry_selection',
                            'completion'
                        ],
                        'nullable' => true
                    ],
                    'isBypassed' => [
                        'type' => 'boolean'
                    ],
                    'availableIndustries' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                            'enum' => [
                                'Legal',
                                'Medical',
                                'Mortgage'
                            ]
                        ]
                    ],
                    'selectedIndustry' => [
                        'type' => 'string',
                        'enum' => [
                            'Legal',
                            'Medical',
                            'Mortgage'
                        ],
                        'nullable' => true
                    ],
                    'selectedIndustryConfigVersion' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'profile' => [
                        '$ref' => '#/components/schemas/ProfileSnapshot'
                    ],
                    'canComplete' => [
                        'type' => 'boolean'
                    ]
                ],
                'required' => [
                    'state',
                    'currentStep',
                    'isBypassed',
                    'availableIndustries',
                    'selectedIndustry',
                    'selectedIndustryConfigVersion',
                    'profile',
                    'canComplete'
                ]
            ],
            'ProfileSnapshot' => [
                'type' => 'object',
                'properties' => [
                    'firstName' => [
                        'type' => 'string'
                    ],
                    'lastName' => [
                        'type' => 'string'
                    ],
                    'phone' => [
                        'type' => 'string'
                    ],
                    'birthday' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'addressLine1' => [
                        'type' => 'string'
                    ],
                    'addressLine2' => [
                        'type' => 'string'
                    ],
                    'city' => [
                        'type' => 'string'
                    ],
                    'stateCode' => [
                        'type' => 'string'
                    ],
                    'postalCode' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'firstName',
                    'lastName',
                    'phone',
                    'birthday',
                    'addressLine1',
                    'addressLine2',
                    'city',
                    'stateCode',
                    'postalCode'
                ]
            ],
            'ProfileResponse' => [
                'type' => 'object',
                'properties' => [
                    'userId' => [
                        'type' => 'string'
                    ],
                    'email' => [
                        'type' => 'string'
                    ],
                    'displayName' => [
                        'type' => 'string'
                    ],
                    'firstName' => [
                        'type' => 'string'
                    ],
                    'lastName' => [
                        'type' => 'string'
                    ],
                    'phone' => [
                        'type' => 'string'
                    ],
                    'birthday' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'addressLine1' => [
                        'type' => 'string'
                    ],
                    'addressLine2' => [
                        'type' => 'string'
                    ],
                    'city' => [
                        'type' => 'string'
                    ],
                    'stateCode' => [
                        'type' => 'string'
                    ],
                    'postalCode' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'userId',
                    'email',
                    'displayName',
                    'firstName',
                    'lastName',
                    'phone',
                    'birthday',
                    'addressLine1',
                    'addressLine2',
                    'city',
                    'stateCode',
                    'postalCode'
                ]
            ],
            'ProfileEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        '$ref' => '#/components/schemas/ProfileResponse'
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
            'ProfileConfirmationRequest' => [
                'type' => 'object',
                'properties' => [
                    'firstName' => [
                        'type' => 'string'
                    ],
                    'lastName' => [
                        'type' => 'string'
                    ],
                    'phone' => [
                        'type' => 'string'
                    ],
                    'birthday' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'addressLine1' => [
                        'type' => 'string'
                    ],
                    'addressLine2' => [
                        'type' => 'string'
                    ],
                    'city' => [
                        'type' => 'string'
                    ],
                    'stateCode' => [
                        'type' => 'string'
                    ],
                    'postalCode' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'firstName',
                    'lastName',
                    'phone',
                    'addressLine1',
                    'city',
                    'stateCode',
                    'postalCode'
                ]
            ],
            'UpdateProfileRequest' => [
                'type' => 'object',
                'properties' => [
                    'displayName' => [
                        'type' => 'string'
                    ],
                    'firstName' => [
                        'type' => 'string'
                    ],
                    'lastName' => [
                        'type' => 'string'
                    ],
                    'phone' => [
                        'type' => 'string'
                    ],
                    'birthday' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'addressLine1' => [
                        'type' => 'string'
                    ],
                    'addressLine2' => [
                        'type' => 'string'
                    ],
                    'city' => [
                        'type' => 'string'
                    ],
                    'stateCode' => [
                        'type' => 'string'
                    ],
                    'postalCode' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'displayName',
                    'firstName',
                    'lastName',
                    'phone',
                    'addressLine1',
                    'city',
                    'stateCode',
                    'postalCode'
                ]
            ],
            'IndustrySelectionRequest' => [
                'type' => 'object',
                'properties' => [
                    'industry' => [
                        'type' => 'string',
                        'enum' => [
                            'Legal',
                            'Medical',
                            'Mortgage'
                        ]
                    ]
                ],
                'required' => [
                    'industry'
                ]
            ],
            'AccountSummary' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string'
                    ],
                    'email' => [
                        'type' => 'string'
                    ],
                    'displayName' => [
                        'type' => 'string'
                    ],
                    'roles' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string'
                        ]
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => [
                            'active',
                            'deactivated'
                        ]
                    ],
                    'onboardingState' => [
                        'type' => 'string',
                        'enum' => [
                            'not_applicable',
                            'required',
                            'in_progress',
                            'completed'
                        ]
                    ],
                    'selectedIndustry' => [
                        'type' => 'string',
                        'enum' => [
                            'Legal',
                            'Medical',
                            'Mortgage'
                        ],
                        'nullable' => true
                    ],
                    'selectedIndustryConfigVersion' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'firstName' => [
                        'type' => 'string'
                    ],
                    'lastName' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'id',
                    'email',
                    'displayName',
                    'roles',
                    'status',
                    'onboardingState',
                    'selectedIndustry',
                    'selectedIndustryConfigVersion',
                    'firstName',
                    'lastName'
                ]
            ],
            'AccountListEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/components/schemas/AccountSummary'
                        ]
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
            'AccountEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        '$ref' => '#/components/schemas/AccountSummary'
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
            'CreateAccountRequest' => [
                'type' => 'object',
                'properties' => [
                    'email' => [
                        'type' => 'string'
                    ],
                    'displayName' => [
                        'type' => 'string'
                    ],
                    'role' => [
                        'type' => 'string',
                        'enum' => [
                            'admin',
                            'user'
                        ]
                    ],
                    'password' => [
                        'type' => 'string'
                    ],
                    'firstName' => [
                        'type' => 'string'
                    ],
                    'lastName' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'email',
                    'displayName',
                    'role',
                    'password'
                ]
            ],
            'UpdateAccountRequest' => [
                'type' => 'object',
                'properties' => [
                    'displayName' => [
                        'type' => 'string'
                    ],
                    'role' => [
                        'type' => 'string',
                        'enum' => [
                            'admin',
                            'user'
                        ]
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => [
                            'active',
                            'deactivated'
                        ]
                    ],
                    'firstName' => [
                        'type' => 'string'
                    ],
                    'lastName' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'displayName',
                    'role',
                    'status'
                ]
            ],
            'IndustryConfigurationSummary' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string'
                    ],
                    'industry' => [
                        'type' => 'string',
                        'enum' => [
                            'Legal',
                            'Medical',
                            'Mortgage'
                        ]
                    ],
                    'version' => [
                        'type' => 'string'
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => [
                            'draft',
                            'published'
                        ]
                    ],
                    'isActive' => [
                        'type' => 'boolean'
                    ],
                    'capabilities' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string'
                        ]
                    ],
                    'notes' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'publishedAt' => [
                        'type' => 'string',
                        'nullable' => true
                    ],
                    'activatedAt' => [
                        'type' => 'string',
                        'nullable' => true
                    ]
                ],
                'required' => [
                    'id',
                    'industry',
                    'version',
                    'status',
                    'isActive',
                    'capabilities',
                    'notes',
                    'publishedAt',
                    'activatedAt'
                ]
            ],
            'IndustryConfigurationListEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/components/schemas/IndustryConfigurationSummary'
                        ]
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
            'IndustryConfigurationEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        '$ref' => '#/components/schemas/IndustryConfigurationSummary'
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
            'CreateIndustryConfigurationRequest' => [
                'type' => 'object',
                'properties' => [
                    'industry' => [
                        'type' => 'string',
                        'enum' => [
                            'Legal',
                            'Medical',
                            'Mortgage'
                        ]
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => [
                            'draft',
                            'published'
                        ]
                    ],
                    'activate' => [
                        'type' => 'boolean'
                    ],
                    'capabilities' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string'
                        ]
                    ],
                    'notes' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'industry',
                    'status',
                    'capabilities'
                ]
            ],
            'DashboardHero' => [
                'type' => 'object',
                'properties' => [
                    'greeting' => [
                        'type' => 'string'
                    ],
                    'userDisplayName' => [
                        'type' => 'string'
                    ],
                    'tenantName' => [
                        'type' => 'string'
                    ],
                    'selectedIndustry' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'selectedIndustryConfigVersion' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'subtitle' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'greeting',
                    'userDisplayName',
                    'tenantName',
                    'selectedIndustry',
                    'selectedIndustryConfigVersion',
                    'subtitle'
                ]
            ],
            'DashboardKpiDelta' => [
                'type' => 'object',
                'properties' => [
                    'direction' => [
                        'type' => 'string',
                        'enum' => [
                            'up',
                            'down',
                            'flat'
                        ]
                    ],
                    'value' => [
                        'type' => 'integer'
                    ],
                    'label' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'direction',
                    'value',
                    'label'
                ]
            ],
            'DashboardKpiCard' => [
                'type' => 'object',
                'properties' => [
                    'key' => [
                        'type' => 'string',
                        'enum' => [
                            'clients_total',
                            'clients_new_7d',
                            'notes_7d',
                            'documents_7d'
                        ]
                    ],
                    'label' => [
                        'type' => 'string'
                    ],
                    'value' => [
                        'type' => 'integer'
                    ],
                    'description' => [
                        'type' => 'string'
                    ],
                    'href' => [
                        'type' => 'string'
                    ],
                    'delta' => [
                        '$ref' => '#/components/schemas/DashboardKpiDelta'
                    ]
                ],
                'required' => [
                    'key',
                    'label',
                    'value',
                    'description',
                    'href',
                    'delta'
                ]
            ],
            'DashboardActivitySummary' => [
                'type' => 'object',
                'properties' => [
                    'visibleClientCount' => [
                        'type' => 'integer'
                    ],
                    'recentNoteCount' => [
                        'type' => 'integer'
                    ],
                    'recentDocumentCount' => [
                        'type' => 'integer'
                    ]
                ],
                'required' => [
                    'visibleClientCount',
                    'recentNoteCount',
                    'recentDocumentCount'
                ]
            ],
            'DashboardSummaryResponse' => [
                'type' => 'object',
                'properties' => [
                    'hero' => [
                        '$ref' => '#/components/schemas/DashboardHero'
                    ],
                    'kpis' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/components/schemas/DashboardKpiCard'
                        ]
                    ],
                    'activitySummary' => [
                        '$ref' => '#/components/schemas/DashboardActivitySummary'
                    ],
                    'calendarPanelEnabled' => [
                        'type' => 'boolean'
                    ]
                ],
                'required' => [
                    'hero',
                    'kpis',
                    'activitySummary',
                    'calendarPanelEnabled'
                ]
            ],
            'DashboardSummaryEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        '$ref' => '#/components/schemas/DashboardSummaryResponse'
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
            'DashboardRange' => [
                'type' => 'object',
                'properties' => [
                    'window' => [
                        'type' => 'string',
                        'enum' => [
                            '7d',
                            '30d',
                            '90d'
                        ]
                    ],
                    'startDate' => [
                        'type' => 'string',
                        'format' => 'date'
                    ],
                    'endDate' => [
                        'type' => 'string',
                        'format' => 'date'
                    ],
                    'granularity' => [
                        'type' => 'string',
                        'enum' => [
                            'day'
                        ]
                    ]
                ],
                'required' => [
                    'window',
                    'startDate',
                    'endDate',
                    'granularity'
                ]
            ],
            'ProductionPoint' => [
                'type' => 'object',
                'properties' => [
                    'bucketDate' => [
                        'type' => 'string',
                        'format' => 'date'
                    ],
                    'value' => [
                        'type' => 'integer'
                    ]
                ],
                'required' => [
                    'bucketDate',
                    'value'
                ]
            ],
            'ProductionSeries' => [
                'type' => 'object',
                'properties' => [
                    'key' => [
                        'type' => 'string',
                        'enum' => [
                            'clientsCreated',
                            'notesCreated',
                            'documentsUploaded'
                        ]
                    ],
                    'label' => [
                        'type' => 'string'
                    ],
                    'points' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/components/schemas/ProductionPoint'
                        ]
                    ]
                ],
                'required' => [
                    'key',
                    'label',
                    'points'
                ]
            ],
            'ProductionTotals' => [
                'type' => 'object',
                'properties' => [
                    'clientsCreated' => [
                        'type' => 'integer'
                    ],
                    'notesCreated' => [
                        'type' => 'integer'
                    ],
                    'documentsUploaded' => [
                        'type' => 'integer'
                    ]
                ],
                'required' => [
                    'clientsCreated',
                    'notesCreated',
                    'documentsUploaded'
                ]
            ],
            'DashboardProductionResponse' => [
                'type' => 'object',
                'properties' => [
                    'range' => [
                        '$ref' => '#/components/schemas/DashboardRange'
                    ],
                    'series' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/components/schemas/ProductionSeries'
                        ]
                    ],
                    'totals' => [
                        '$ref' => '#/components/schemas/ProductionTotals'
                    ]
                ],
                'required' => [
                    'range',
                    'series',
                    'totals'
                ]
            ],
            'DashboardProductionEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        '$ref' => '#/components/schemas/DashboardProductionResponse'
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
            'ClientAddressSummary' => [
                'type' => 'object',
                'properties' => [
                    'addressLine1' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'addressLine2' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'city' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'stateCode' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'postalCode' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ]
                ],
                'required' => [
                    'addressLine1',
                    'addressLine2',
                    'city',
                    'stateCode',
                    'postalCode'
                ]
            ],
            'ClientDetail' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string'
                    ],
                    'displayName' => [
                        'type' => 'string'
                    ],
                    'firstName' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'lastName' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'companyName' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => [
                            'lead',
                            'active',
                            'inactive'
                        ]
                    ],
                    'primaryEmail' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'primaryPhone' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'preferredContactChannel' => [
                        'type' => [
                            'string',
                            'null'
                        ],
                        'enum' => [
                            'email',
                            'sms',
                            'phone',
                            null
                        ]
                    ],
                    'dateOfBirth' => [
                        'type' => [
                            'string',
                            'null'
                        ],
                        'format' => 'date'
                    ],
                    'ownerUserId' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'ownerDisplayName' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'address' => [
                        'oneOf' => [
                            [
                                '$ref' => '#/components/schemas/ClientAddressSummary'
                            ],
                            [
                                'type' => 'null'
                            ]
                        ]
                    ],
                    'createdAt' => [
                        'type' => [
                            'string',
                            'null'
                        ],
                        'format' => 'date-time'
                    ],
                    'updatedAt' => [
                        'type' => [
                            'string',
                            'null'
                        ],
                        'format' => 'date-time'
                    ]
                ],
                'required' => [
                    'id',
                    'displayName',
                    'firstName',
                    'lastName',
                    'companyName',
                    'status',
                    'primaryEmail',
                    'primaryPhone',
                    'preferredContactChannel',
                    'dateOfBirth',
                    'ownerUserId',
                    'ownerDisplayName',
                    'address',
                    'createdAt',
                    'updatedAt'
                ]
            ],
            'ClientEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        '$ref' => '#/components/schemas/ClientDetail'
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
            'ClientListItem' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string'
                    ],
                    'displayName' => [
                        'type' => 'string'
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => [
                            'lead',
                            'active',
                            'inactive'
                        ]
                    ],
                    'primaryEmail' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'primaryPhone' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'city' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'stateCode' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'ownerDisplayName' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'notesCount' => [
                        'type' => 'integer'
                    ],
                    'documentsCount' => [
                        'type' => 'integer'
                    ],
                    'updatedAt' => [
                        'type' => [
                            'string',
                            'null'
                        ],
                        'format' => 'date-time'
                    ],
                    'createdAt' => [
                        'type' => [
                            'string',
                            'null'
                        ],
                        'format' => 'date-time'
                    ]
                ],
                'required' => [
                    'id',
                    'displayName',
                    'status',
                    'primaryEmail',
                    'primaryPhone',
                    'city',
                    'stateCode',
                    'ownerDisplayName',
                    'notesCount',
                    'documentsCount',
                    'updatedAt',
                    'createdAt'
                ]
            ],
            'ClientListPagination' => [
                'type' => 'object',
                'properties' => [
                    'page' => [
                        'type' => 'integer'
                    ],
                    'perPage' => [
                        'type' => 'integer'
                    ],
                    'total' => [
                        'type' => 'integer'
                    ],
                    'totalPages' => [
                        'type' => 'integer'
                    ]
                ],
                'required' => [
                    'page',
                    'perPage',
                    'total',
                    'totalPages'
                ]
            ],
            'ClientListAppliedFilters' => [
                'type' => 'object',
                'properties' => [
                    'search' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'status' => [
                        'type' => [
                            'string',
                            'null'
                        ],
                        'enum' => [
                            'lead',
                            'active',
                            'inactive',
                            null
                        ]
                    ],
                    'sort' => [
                        'type' => 'string',
                        'enum' => [
                            'display_name',
                            'created_at',
                            'updated_at',
                            'last_activity_at'
                        ]
                    ],
                    'direction' => [
                        'type' => 'string',
                        'enum' => [
                            'asc',
                            'desc'
                        ]
                    ]
                ],
                'required' => [
                    'search',
                    'status',
                    'sort',
                    'direction'
                ]
            ],
            'ClientListResponse' => [
                'type' => 'object',
                'properties' => [
                    'items' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/components/schemas/ClientListItem'
                        ]
                    ],
                    'pagination' => [
                        '$ref' => '#/components/schemas/ClientListPagination'
                    ],
                    'appliedFilters' => [
                        '$ref' => '#/components/schemas/ClientListAppliedFilters'
                    ]
                ],
                'required' => [
                    'items',
                    'pagination',
                    'appliedFilters'
                ]
            ],
            'ClientListEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        '$ref' => '#/components/schemas/ClientListResponse'
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
            'ClientNoteSummary' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string'
                    ],
                    'sourceType' => [
                        'type' => 'string',
                        'enum' => [
                            'user',
                            'system'
                        ]
                    ],
                    'body' => [
                        'type' => 'string'
                    ],
                    'isEditable' => [
                        'type' => 'boolean'
                    ],
                    'authorDisplayName' => [
                        'type' => 'string'
                    ],
                    'createdAt' => [
                        'type' => [
                            'string',
                            'null'
                        ],
                        'format' => 'date-time'
                    ]
                ],
                'required' => [
                    'id',
                    'sourceType',
                    'body',
                    'isEditable',
                    'authorDisplayName',
                    'createdAt'
                ]
            ],
            'ClientDocumentSummary' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string'
                    ],
                    'originalFilename' => [
                        'type' => 'string'
                    ],
                    'mimeType' => [
                        'type' => 'string'
                    ],
                    'sizeBytes' => [
                        'type' => 'integer'
                    ],
                    'provenance' => [
                        'type' => 'string',
                        'enum' => [
                            'manual_upload'
                        ]
                    ],
                    'attachmentCategory' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'uploadedByDisplayName' => [
                        'type' => 'string'
                    ],
                    'uploadedAt' => [
                        'type' => [
                            'string',
                            'null'
                        ],
                        'format' => 'date-time'
                    ],
                    'storageReference' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'id',
                    'originalFilename',
                    'mimeType',
                    'sizeBytes',
                    'provenance',
                    'attachmentCategory',
                    'uploadedByDisplayName',
                    'uploadedAt',
                    'storageReference'
                ]
            ],
            'ClientAuditSummary' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string'
                    ],
                    'action' => [
                        'type' => 'string'
                    ],
                    'actorDisplayName' => [
                        'type' => 'string'
                    ],
                    'subjectType' => [
                        'type' => 'string'
                    ],
                    'createdAt' => [
                        'type' => [
                            'string',
                            'null'
                        ],
                        'format' => 'date-time'
                    ]
                ],
                'required' => [
                    'id',
                    'action',
                    'actorDisplayName',
                    'subjectType',
                    'createdAt'
                ]
            ],
            'ClientWorkspaceTab' => [
                'type' => 'object',
                'properties' => [
                    'key' => [
                        'type' => 'string'
                    ],
                    'label' => [
                        'type' => 'string'
                    ],
                    'href' => [
                        'type' => 'string'
                    ],
                    'available' => [
                        'type' => 'boolean'
                    ]
                ],
                'required' => [
                    'key',
                    'label',
                    'href',
                    'available'
                ]
            ],
            'ClientWorkspaceSummary' => [
                'type' => 'object',
                'properties' => [
                    'notesCount' => [
                        'type' => 'integer'
                    ],
                    'documentsCount' => [
                        'type' => 'integer'
                    ],
                    'eventsCount' => [
                        'type' => 'integer'
                    ],
                    'applicationsCount' => [
                        'type' => 'integer'
                    ],
                    'lastActivityAt' => [
                        'type' => [
                            'string',
                            'null'
                        ],
                        'format' => 'date-time'
                    ]
                ],
                'required' => [
                    'notesCount',
                    'documentsCount',
                    'eventsCount',
                    'applicationsCount',
                    'lastActivityAt'
                ]
            ],
            'ClientWorkspaceResponse' => [
                'type' => 'object',
                'properties' => [
                    'client' => [
                        '$ref' => '#/components/schemas/ClientDetail'
                    ],
                    'summary' => [
                        '$ref' => '#/components/schemas/ClientWorkspaceSummary'
                    ],
                    'recentNotes' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/components/schemas/ClientNoteSummary'
                        ]
                    ],
                    'recentDocuments' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/components/schemas/ClientDocumentSummary'
                        ]
                    ],
                    'recentAudit' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/components/schemas/ClientAuditSummary'
                        ]
                    ],
                    'tabs' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/components/schemas/ClientWorkspaceTab'
                        ]
                    ]
                ],
                'required' => [
                    'client',
                    'summary',
                    'recentNotes',
                    'recentDocuments',
                    'recentAudit',
                    'tabs'
                ]
            ],
            'ClientWorkspaceEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        '$ref' => '#/components/schemas/ClientWorkspaceResponse'
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
            'CreateOrUpdateClientRequest' => [
                'type' => 'object',
                'properties' => [
                    'displayName' => [
                        'type' => 'string'
                    ],
                    'firstName' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'lastName' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'companyName' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'primaryEmail' => [
                        'type' => [
                            'string',
                            'null'
                        ],
                        'format' => 'email'
                    ],
                    'primaryPhone' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'preferredContactChannel' => [
                        'type' => [
                            'string',
                            'null'
                        ],
                        'enum' => [
                            'email',
                            'sms',
                            'phone',
                            null
                        ]
                    ],
                    'dateOfBirth' => [
                        'type' => [
                            'string',
                            'null'
                        ],
                        'format' => 'date'
                    ],
                    'status' => [
                        'type' => [
                            'string',
                            'null'
                        ],
                        'enum' => [
                            'lead',
                            'active',
                            'inactive',
                            null
                        ]
                    ],
                    'ownerUserId' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'addressLine1' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'addressLine2' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'city' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'stateCode' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ],
                    'postalCode' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ]
                ],
                'required' => [
                    'displayName'
                ]
            ],
            'CreateClientNoteRequest' => [
                'type' => 'object',
                'properties' => [
                    'body' => [
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'body'
                ]
            ],
            'CreateClientDocumentRequest' => [
                'type' => 'object',
                'properties' => [
                    'file' => [
                        'type' => 'string',
                        'format' => 'binary'
                    ],
                    'attachmentCategory' => [
                        'type' => [
                            'string',
                            'null'
                        ]
                    ]
                ],
                'required' => [
                    'file'
                ]
            ],
            'ClientNoteEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        '$ref' => '#/components/schemas/ClientNoteSummary'
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
            'ClientDocumentEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        '$ref' => '#/components/schemas/ClientDocumentSummary'
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
