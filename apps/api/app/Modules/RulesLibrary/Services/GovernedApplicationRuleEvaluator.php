<?php

declare(strict_types=1);

namespace App\Modules\RulesLibrary\Services;

use App\Modules\Applications\Models\Application;
use App\Modules\Applications\Services\ApplicationRuleEvaluator;
use App\Modules\IdentityAccess\Models\User;

final class GovernedApplicationRuleEvaluator implements ApplicationRuleEvaluator
{
    public function __construct(
        private readonly RuleExecutionReadyQueryService $queryService,
        private readonly RuleExecutionService $executionService,
    ) {
    }

    public function evaluate(Application $application, string $triggerEvent, ?User $actor = null, array $context = []): array
    {
        $versions = $this->queryService->publishedVersionsFor((string) $application->tenant_id, 'applications', $triggerEvent);

        return $this->executionService->evaluateApplicationRules(
            correlationId: (string) ($context['correlationId'] ?? ''),
            application: $application,
            versions: $versions,
            triggerEvent: $triggerEvent,
            actor: $actor,
            context: $context,
        );
    }
}