<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Services;

use App\Modules\IdentityAccess\Models\User;

final class PermissionSnapshotService
{
    /**
     * @return array<int, string>
     */
    public function forUser(User $user): array
    {
        return $user->roles
            ->flatMap(fn ($role) => $role->permissions->pluck('name'))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function roleNames(User $user): array
    {
        return $user->roles
            ->pluck('name')
            ->sort()
            ->values()
            ->all();
    }
}
