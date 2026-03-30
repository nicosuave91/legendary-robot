<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Policies;

use App\Modules\IdentityAccess\Models\User;

final class UserPolicy
{
    public function viewSelf(User $actor, User $subject): bool
    {
        return $actor->id === $subject->id && $actor->tenant_id === $subject->tenant_id;
    }
}
