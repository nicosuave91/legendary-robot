<?php

declare(strict_types=1);

return [
    'openapi' => '3.1.0',
    'info' => [
        'title' => 'Snowball CRM API',
        'version' => '3.0.0',
        'description' => 'Sprint 3 tenant governance, settings, and versioned industry configuration contracts.',
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
                            'schema' => ['$ref' => '#/components/schemas/SignInRequest'],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Authentication response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/AuthContextEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
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
                                'schema' => ['$ref' => '#/components/schemas/MessageResponse'],
                            ],
                        ],
                    ],
                ],
            ],
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
                                'schema' => ['$ref' => '#/components/schemas/AuthContextEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
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
                                'schema' => ['$ref' => '#/components/schemas/OnboardingStateEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/v1/onboarding/profile-confirmation' => [
            'patch' => [
                'operationId' => 'patchOnboardingProfileConfirmation',
                'summary' => 'Confirm the current user profile during onboarding',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/ProfileConfirmationRequest'],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Updated onboarding state response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/OnboardingStateEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/v1/onboarding/industry-selection' => [
            'patch' => [
                'operationId' => 'patchOnboardingIndustrySelection',
                'summary' => 'Persist the selected industry for onboarding',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/IndustrySelectionRequest'],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Updated onboarding state response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/OnboardingStateEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
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
                                'schema' => ['$ref' => '#/components/schemas/OnboardingStateEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
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
                                'schema' => ['$ref' => '#/components/schemas/ProfileEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
            'patch' => [
                'operationId' => 'patchSettingsProfile',
                'summary' => 'Update current profile settings',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateProfileRequest'],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Updated profile response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/ProfileEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
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
                                'schema' => ['$ref' => '#/components/schemas/AccountListEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
            'post' => [
                'operationId' => 'postSettingsAccounts',
                'summary' => 'Create a tenant account that defaults to onboarding required',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/CreateAccountRequest'],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Created account response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/AccountEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
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
                        'schema' => ['type' => 'string'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateAccountRequest'],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Updated account response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/AccountEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
            'delete' => [
                'operationId' => 'deleteSettingsAccount',
                'summary' => 'Decommission a tenant account',
                'parameters' => [
                    [
                        'name' => 'userId',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Decommissioned account response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/MessageResponse'],
                            ],
                        ],
                    ],
                ],
            ],
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
                                'schema' => ['$ref' => '#/components/schemas/ThemeEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
            'patch' => [
                'operationId' => 'patchSettingsTheme',
                'summary' => 'Update current tenant theme settings',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/ThemeSummary'],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Updated theme response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/ThemeEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
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
                                'schema' => ['$ref' => '#/components/schemas/IndustryConfigurationListEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
            'post' => [
                'operationId' => 'postSettingsIndustryConfigurations',
                'summary' => 'Create a tenant industry configuration version',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/CreateIndustryConfigurationRequest'],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Created industry configuration response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/IndustryConfigurationEnvelope'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'components' => [
        'schemas' => [
            'MessageResponse' => [
                'type' => 'object',
                'properties' => [
                    'message' => ['type' => 'string'],
                ],
                'required' => ['message'],
            ],
            'ResponseMeta' => [
                'type' => 'object',
                'properties' => [
                    'apiVersion' => ['type' => 'string', 'enum' => ['v1']],
                    'correlationId' => ['type' => 'string'],
                ],
                'required' => ['apiVersion', 'correlationId'],
            ],
            'SignInRequest' => [
                'type' => 'object',
                'properties' => [
                    'email' => ['type' => 'string'],
                    'password' => ['type' => 'string'],
                ],
                'required' => ['email', 'password'],
            ],
            'AuthContextEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['$ref' => '#/components/schemas/AuthContextResponse'],
                    'meta' => ['$ref' => '#/components/schemas/ResponseMeta'],
                ],
                'required' => ['data', 'meta'],
            ],
            'AuthContextResponse' => [
                'type' => 'object',
                'properties' => [
                    'isAuthenticated' => ['type' => 'boolean'],
                    'user' => ['$ref' => '#/components/schemas/UserSummary'],
                    'tenant' => ['$ref' => '#/components/schemas/TenantSummary'],
                    'roles' => ['type' => 'array', 'items' => ['type' => 'string']],
                    'permissions' => ['type' => 'array', 'items' => ['type' => 'string']],
                    'onboardingState' => [
                        'type' => 'string',
                        'enum' => ['not_applicable', 'required', 'in_progress', 'completed'],
                    ],
                    'onboardingStep' => [
                        'type' => 'string',
                        'enum' => ['profile_confirmation', 'industry_selection', 'completion'],
                        'nullable' => true,
                    ],
                    'theme' => ['$ref' => '#/components/schemas/ThemeSummary'],
                    'landingRoute' => ['type' => 'string'],
                    'selectedIndustry' => [
                        'type' => 'string',
                        'enum' => ['Legal', 'Medical', 'Mortgage'],
                        'nullable' => true,
                    ],
                    'selectedIndustryConfigVersion' => ['type' => 'string', 'nullable' => true],
                    'capabilities' => ['type' => 'array', 'items' => ['type' => 'string']],
                ],
                'required' => [
                    'isAuthenticated', 'user', 'tenant', 'roles', 'permissions', 'onboardingState',
                    'onboardingStep', 'theme', 'landingRoute', 'selectedIndustry',
                    'selectedIndustryConfigVersion', 'capabilities',
                ],
            ],
            'UserSummary' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'email' => ['type' => 'string'],
                    'displayName' => ['type' => 'string'],
                ],
                'required' => ['id', 'email', 'displayName'],
            ],
            'TenantSummary' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'name' => ['type' => 'string'],
                ],
                'required' => ['id', 'name'],
            ],
            'ThemeSummary' => [
                'type' => 'object',
                'properties' => [
                    'primary' => ['type' => 'string'],
                    'secondary' => ['type' => 'string'],
                    'tertiary' => ['type' => 'string'],
                ],
                'required' => ['primary', 'secondary', 'tertiary'],
            ],
            'ThemeEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['$ref' => '#/components/schemas/ThemeSummary'],
                    'meta' => ['$ref' => '#/components/schemas/ResponseMeta'],
                ],
                'required' => ['data', 'meta'],
            ],
            'OnboardingStateEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['$ref' => '#/components/schemas/OnboardingStateResponse'],
                    'meta' => ['$ref' => '#/components/schemas/ResponseMeta'],
                ],
                'required' => ['data', 'meta'],
            ],
            'OnboardingStateResponse' => [
                'type' => 'object',
                'properties' => [
                    'state' => [
                        'type' => 'string',
                        'enum' => ['not_applicable', 'required', 'in_progress', 'completed'],
                    ],
                    'currentStep' => [
                        'type' => 'string',
                        'enum' => ['profile_confirmation', 'industry_selection', 'completion'],
                        'nullable' => true,
                    ],
                    'isBypassed' => ['type' => 'boolean'],
                    'availableIndustries' => [
                        'type' => 'array',
                        'items' => ['type' => 'string', 'enum' => ['Legal', 'Medical', 'Mortgage']],
                    ],
                    'selectedIndustry' => [
                        'type' => 'string',
                        'enum' => ['Legal', 'Medical', 'Mortgage'],
                        'nullable' => true,
                    ],
                    'selectedIndustryConfigVersion' => ['type' => 'string', 'nullable' => true],
                    'profile' => ['$ref' => '#/components/schemas/ProfileSnapshot'],
                    'canComplete' => ['type' => 'boolean'],
                ],
                'required' => [
                    'state', 'currentStep', 'isBypassed', 'availableIndustries',
                    'selectedIndustry', 'selectedIndustryConfigVersion', 'profile', 'canComplete',
                ],
            ],
            'ProfileSnapshot' => [
                'type' => 'object',
                'properties' => [
                    'firstName' => ['type' => 'string'],
                    'lastName' => ['type' => 'string'],
                    'phone' => ['type' => 'string'],
                    'birthday' => ['type' => 'string', 'nullable' => true],
                    'addressLine1' => ['type' => 'string'],
                    'addressLine2' => ['type' => 'string'],
                    'city' => ['type' => 'string'],
                    'stateCode' => ['type' => 'string'],
                    'postalCode' => ['type' => 'string'],
                ],
                'required' => ['firstName', 'lastName', 'phone', 'birthday', 'addressLine1', 'addressLine2', 'city', 'stateCode', 'postalCode'],
            ],
            'ProfileResponse' => [
                'type' => 'object',
                'properties' => [
                    'userId' => ['type' => 'string'],
                    'email' => ['type' => 'string'],
                    'displayName' => ['type' => 'string'],
                    'firstName' => ['type' => 'string'],
                    'lastName' => ['type' => 'string'],
                    'phone' => ['type' => 'string'],
                    'birthday' => ['type' => 'string', 'nullable' => true],
                    'addressLine1' => ['type' => 'string'],
                    'addressLine2' => ['type' => 'string'],
                    'city' => ['type' => 'string'],
                    'stateCode' => ['type' => 'string'],
                    'postalCode' => ['type' => 'string'],
                ],
                'required' => [
                    'userId', 'email', 'displayName', 'firstName', 'lastName', 'phone', 'birthday',
                    'addressLine1', 'addressLine2', 'city', 'stateCode', 'postalCode',
                ],
            ],
            'ProfileEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['$ref' => '#/components/schemas/ProfileResponse'],
                    'meta' => ['$ref' => '#/components/schemas/ResponseMeta'],
                ],
                'required' => ['data', 'meta'],
            ],
            'ProfileConfirmationRequest' => [
                'type' => 'object',
                'properties' => [
                    'firstName' => ['type' => 'string'],
                    'lastName' => ['type' => 'string'],
                    'phone' => ['type' => 'string'],
                    'birthday' => ['type' => 'string', 'nullable' => true],
                    'addressLine1' => ['type' => 'string'],
                    'addressLine2' => ['type' => 'string'],
                    'city' => ['type' => 'string'],
                    'stateCode' => ['type' => 'string'],
                    'postalCode' => ['type' => 'string'],
                ],
                'required' => ['firstName', 'lastName', 'phone', 'addressLine1', 'city', 'stateCode', 'postalCode'],
            ],
            'UpdateProfileRequest' => [
                'type' => 'object',
                'properties' => [
                    'displayName' => ['type' => 'string'],
                    'firstName' => ['type' => 'string'],
                    'lastName' => ['type' => 'string'],
                    'phone' => ['type' => 'string'],
                    'birthday' => ['type' => 'string', 'nullable' => true],
                    'addressLine1' => ['type' => 'string'],
                    'addressLine2' => ['type' => 'string'],
                    'city' => ['type' => 'string'],
                    'stateCode' => ['type' => 'string'],
                    'postalCode' => ['type' => 'string'],
                ],
                'required' => ['displayName', 'firstName', 'lastName', 'phone', 'addressLine1', 'city', 'stateCode', 'postalCode'],
            ],
            'IndustrySelectionRequest' => [
                'type' => 'object',
                'properties' => [
                    'industry' => [
                        'type' => 'string',
                        'enum' => ['Legal', 'Medical', 'Mortgage'],
                    ],
                ],
                'required' => ['industry'],
            ],
            'AccountSummary' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'email' => ['type' => 'string'],
                    'displayName' => ['type' => 'string'],
                    'roles' => ['type' => 'array', 'items' => ['type' => 'string']],
                    'status' => ['type' => 'string', 'enum' => ['active', 'deactivated']],
                    'onboardingState' => [
                        'type' => 'string',
                        'enum' => ['not_applicable', 'required', 'in_progress', 'completed'],
                    ],
                    'selectedIndustry' => [
                        'type' => 'string',
                        'enum' => ['Legal', 'Medical', 'Mortgage'],
                        'nullable' => true,
                    ],
                    'selectedIndustryConfigVersion' => ['type' => 'string', 'nullable' => true],
                    'firstName' => ['type' => 'string'],
                    'lastName' => ['type' => 'string'],
                ],
                'required' => [
                    'id', 'email', 'displayName', 'roles', 'status', 'onboardingState',
                    'selectedIndustry', 'selectedIndustryConfigVersion', 'firstName', 'lastName',
                ],
            ],
            'AccountListEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/AccountSummary']],
                    'meta' => ['$ref' => '#/components/schemas/ResponseMeta'],
                ],
                'required' => ['data', 'meta'],
            ],
            'AccountEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['$ref' => '#/components/schemas/AccountSummary'],
                    'meta' => ['$ref' => '#/components/schemas/ResponseMeta'],
                ],
                'required' => ['data', 'meta'],
            ],
            'CreateAccountRequest' => [
                'type' => 'object',
                'properties' => [
                    'email' => ['type' => 'string'],
                    'displayName' => ['type' => 'string'],
                    'role' => ['type' => 'string', 'enum' => ['admin', 'user']],
                    'password' => ['type' => 'string'],
                    'firstName' => ['type' => 'string'],
                    'lastName' => ['type' => 'string'],
                ],
                'required' => ['email', 'displayName', 'role', 'password'],
            ],
            'UpdateAccountRequest' => [
                'type' => 'object',
                'properties' => [
                    'displayName' => ['type' => 'string'],
                    'role' => ['type' => 'string', 'enum' => ['admin', 'user']],
                    'status' => ['type' => 'string', 'enum' => ['active', 'deactivated']],
                    'firstName' => ['type' => 'string'],
                    'lastName' => ['type' => 'string'],
                ],
                'required' => ['displayName', 'role', 'status'],
            ],
            'IndustryConfigurationSummary' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'industry' => ['type' => 'string', 'enum' => ['Legal', 'Medical', 'Mortgage']],
                    'version' => ['type' => 'string'],
                    'status' => ['type' => 'string', 'enum' => ['draft', 'published']],
                    'isActive' => ['type' => 'boolean'],
                    'capabilities' => ['type' => 'array', 'items' => ['type' => 'string']],
                    'notes' => ['type' => 'string', 'nullable' => true],
                    'publishedAt' => ['type' => 'string', 'nullable' => true],
                    'activatedAt' => ['type' => 'string', 'nullable' => true],
                ],
                'required' => ['id', 'industry', 'version', 'status', 'isActive', 'capabilities', 'notes', 'publishedAt', 'activatedAt'],
            ],
            'IndustryConfigurationListEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/IndustryConfigurationSummary']],
                    'meta' => ['$ref' => '#/components/schemas/ResponseMeta'],
                ],
                'required' => ['data', 'meta'],
            ],
            'IndustryConfigurationEnvelope' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['$ref' => '#/components/schemas/IndustryConfigurationSummary'],
                    'meta' => ['$ref' => '#/components/schemas/ResponseMeta'],
                ],
                'required' => ['data', 'meta'],
            ],
            'CreateIndustryConfigurationRequest' => [
                'type' => 'object',
                'properties' => [
                    'industry' => ['type' => 'string', 'enum' => ['Legal', 'Medical', 'Mortgage']],
                    'status' => ['type' => 'string', 'enum' => ['draft', 'published']],
                    'activate' => ['type' => 'boolean'],
                    'capabilities' => ['type' => 'array', 'items' => ['type' => 'string']],
                    'notes' => ['type' => 'string'],
                ],
                'required' => ['industry', 'status', 'capabilities'],
            ],
        ],
    ],
];
