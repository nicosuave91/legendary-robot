<?php

declare(strict_types=1);

namespace App\Modules\TenantGovernance\Support;

final class DefaultIndustryCapabilities
{
    /**
     * @return array<string, array<int, string>>
     */
    public static function all(): array
    {
        return [
            'Legal' => [
                'clients.intake',
                'documents.legal_packets',
                'communications.email',
                'audit.case_timeline',
            ],
            'Medical' => [
                'clients.intake',
                'documents.medical_records',
                'communications.sms',
                'audit.compliance_review',
            ],
            'Mortgage' => [
                'clients.intake',
                'documents.loan_packages',
                'communications.sms',
                'workflow.application_review',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function forIndustry(string $industry): array
    {
        return self::all()[$industry] ?? [];
    }
}
