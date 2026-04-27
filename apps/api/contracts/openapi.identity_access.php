<?php

declare(strict_types=1);

return [
    'components' => [
        'schemas' => [
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
                        'enum' => ['not_applicable', 'required', 'in_progress', 'completed', 'exempt'],
                    ],
                    'selectedIndustry' => [
                        'type' => ['string', 'null'],
                        'enum' => ['Legal', 'Medical', 'Mortgage', null],
                    ],
                    'selectedIndustryConfigVersion' => ['type' => ['string', 'null']],
                    'firstName' => ['type' => 'string'],
                    'lastName' => ['type' => 'string'],
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
                    'lastName',
                ],
            ],
        ],
    ],
];
