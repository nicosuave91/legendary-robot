<?php

declare(strict_types=1);

namespace App\Modules\Applications\Services;

use App\Modules\Applications\Models\Application;
use App\Modules\IdentityAccess\Models\User;

final class DefaultApplicationRuleEvaluator implements ApplicationRuleEvaluator
{
    public function evaluate(Application $application, string $triggerEvent, ?User $actor = null, array $context = []): array
    {
        $results = [];

        if ($application->amount_requested === null) {
            $results[] = [
                'ruleKey' => 'application.amount.missing',
                'ruleVersion' => 'sprint7-v1',
                'triggerEvent' => $triggerEvent,
                'outcome' => 'warning',
                'title' => 'Requested amount missing',
                'noteBody' => 'This application does not yet include an amount requested. Review it before downstream decisions.',
                'isBlocking' => false,
                'evidence' => [
                    'applicationId' => (string) $application->id,
                    'status' => (string) $application->status,
                ],
            ];
        }

        if ($application->amount_requested !== null && (float) $application->amount_requested >= 250000) {
            $results[] = [
                'ruleKey' => 'application.high_value.review',
                'ruleVersion' => 'sprint7-v1',
                'triggerEvent' => $triggerEvent,
                'outcome' => 'info',
                'title' => 'High-value application review note',
                'noteBody' => 'This application exceeds the high-value review threshold and should receive additional human review.',
                'isBlocking' => false,
                'evidence' => [
                    'threshold' => 250000,
                    'amountRequested' => (float) $application->amount_requested,
                ],
            ];
        }

        if (($context['targetStatus'] ?? null) === 'submitted' && $application->submitted_at === null) {
            $results[] = [
                'ruleKey' => 'application.submission.date.required',
                'ruleVersion' => 'sprint7-v1',
                'triggerEvent' => $triggerEvent,
                'outcome' => 'blocking',
                'title' => 'Submission date required',
                'noteBody' => 'A submitted application must include a submitted timestamp before the status can advance to Submitted.',
                'isBlocking' => true,
                'evidence' => [
                    'targetStatus' => 'submitted',
                    'submittedAt' => null,
                ],
            ];
        }

        if (($context['targetStatus'] ?? null) === 'approved' && empty($application->external_reference)) {
            $results[] = [
                'ruleKey' => 'application.external_reference.missing',
                'ruleVersion' => 'sprint7-v1',
                'triggerEvent' => $triggerEvent,
                'outcome' => 'warning',
                'title' => 'External reference missing',
                'noteBody' => 'The application was approved without an external reference. Review this state for downstream reconciliation.',
                'isBlocking' => false,
                'evidence' => [
                    'targetStatus' => 'approved',
                ],
            ];
        }

        return $results;
    }
}
