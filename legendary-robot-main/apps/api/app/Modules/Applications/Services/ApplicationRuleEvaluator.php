<?php

declare(strict_types=1);

namespace App\Modules\Applications\Services;

use App\Modules\Applications\Models\Application;
use App\Modules\IdentityAccess\Models\User;

interface ApplicationRuleEvaluator
{
    public function evaluate(Application $application, string $triggerEvent, ?User $actor = null, array $context = []): array;
}
