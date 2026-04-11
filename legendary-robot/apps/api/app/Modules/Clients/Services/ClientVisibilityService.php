<?php

declare(strict_types=1);

namespace App\Modules\Clients\Services;

use Illuminate\Database\Eloquent\Builder;
use App\Modules\Clients\Models\Client;
use App\Modules\IdentityAccess\Models\User;

final class ClientVisibilityService
{
    public function queryForActor(User $actor): Builder
    {
        $query = Client::query()->withoutGlobalScopes()->where('clients.tenant_id', $actor->tenant_id);

        if ($actor->hasPermission('clients.read.all')) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($actor): void {
            $builder->where('clients.owner_user_id', $actor->id)
                ->orWhere('clients.created_by', $actor->id);
        });
    }

    public function canView(User $actor, Client $client): bool
    {
        if ((string) $actor->tenant_id !== (string) $client->tenant_id) {
            return false;
        }

        if ($actor->hasPermission('clients.read.all')) {
            return true;
        }

        return (string) $client->owner_user_id === (string) $actor->id || (string) $client->created_by === (string) $actor->id;
    }
}
