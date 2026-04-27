<?php

declare(strict_types=1);

return [
    'paths' => [
        '/api/v1/clients' => [
            'get' => [
                'parameters' => [
                    [
                        'name' => 'search',
                        'in' => 'query',
                        'required' => false,
                        'schema' => ['type' => 'string'],
                    ],
                    [
                        'name' => 'status',
                        'in' => 'query',
                        'required' => false,
                        'schema' => [
                            'type' => 'string',
                            'enum' => ['lead', 'qualified', 'applied', 'active', 'inactive'],
                        ],
                    ],
                    [
                        'name' => 'sort',
                        'in' => 'query',
                        'required' => false,
                        'schema' => [
                            'type' => 'string',
                            'enum' => ['display_name', 'created_at', 'updated_at', 'last_activity_at'],
                        ],
                    ],
                    [
                        'name' => 'direction',
                        'in' => 'query',
                        'required' => false,
                        'schema' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                    ],
                    [
                        'name' => 'page',
                        'in' => 'query',
                        'required' => false,
                        'schema' => ['type' => 'integer'],
                    ],
                    [
                        'name' => 'perPage',
                        'in' => 'query',
                        'required' => false,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
            ],
        ],
    ],
    'components' => [
        'schemas' => [
            'ClientListAppliedFilters' => [
                'type' => 'object',
                'properties' => [
                    'search' => ['type' => ['string', 'null']],
                    'status' => [
                        'type' => ['string', 'null'],
                        'enum' => ['lead', 'qualified', 'applied', 'active', 'inactive', null],
                    ],
                    'sort' => [
                        'type' => 'string',
                        'enum' => ['display_name', 'created_at', 'updated_at', 'last_activity_at'],
                    ],
                    'direction' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                ],
                'required' => ['search', 'status', 'sort', 'direction'],
            ],
            'CreateOrUpdateClientRequest' => [
                'type' => 'object',
                'properties' => [
                    'displayName' => ['type' => 'string'],
                    'firstName' => ['type' => ['string', 'null']],
                    'lastName' => ['type' => ['string', 'null']],
                    'companyName' => ['type' => ['string', 'null']],
                    'primaryEmail' => ['type' => ['string', 'null']],
                    'primaryPhone' => ['type' => ['string', 'null']],
                    'preferredContactChannel' => [
                        'type' => ['string', 'null'],
                        'enum' => ['email', 'sms', 'phone', null],
                    ],
                    'dateOfBirth' => ['type' => ['string', 'null']],
                    'status' => [
                        'type' => ['string', 'null'],
                        'enum' => ['lead', 'qualified', 'applied', 'active', 'inactive', null],
                    ],
                    'ownerUserId' => ['type' => ['string', 'null']],
                    'addressLine1' => ['type' => ['string', 'null']],
                    'addressLine2' => ['type' => ['string', 'null']],
                    'city' => ['type' => ['string', 'null']],
                    'stateCode' => ['type' => ['string', 'null']],
                    'postalCode' => ['type' => ['string', 'null']],
                ],
                'required' => ['displayName'],
            ],
        ],
    ],
];
